<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lunar\Models\Product;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Channel;

class StoreController extends Controller
{
    public function index()
    {
        $products = Product::with(['variants.prices'])->get();
        $cart = $this->getOrCreateCart();

        if (!$cart->lines->isEmpty()) {
            $cart->calculate();
        }

        return view('index', compact('products', 'cart'));
    }

    public function addToCart(Request $request)
    {
        $productId = $request->input('product_id');
        $product = Product::with(['variants.prices'])->find($productId);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $variant = $product->variants->first();

        if (!$variant) {
            return response()->json(['error' => 'Product variant not found'], 404);
        }

        $cart = $this->getOrCreateCart();

        $cart->add($variant, 1);

        return response()->json([
            'success' => true,
            'cart' => $this->formatCartForJson($cart),
            'total' => $cart->total ? number_format($cart->total->decimal, 2) : '0.00'
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $productId = $request->input('product_id');
        $cart = $this->getOrCreateCart();

        $cartLine = $cart->lines->first(function($line) use ($productId) {
            return $line->purchasable->product_id == $productId;
        });

        if ($cartLine) {
            $cartLine->delete();
            $cart->refresh();
            $cart->calculate();
        }

        return response()->json([
            'success' => true,
            'cart' => $this->formatCartForJson($cart),
            'total' => $cart->total ? number_format($cart->total->decimal, 2) : '0.00'
        ]);
    }

    private function getOrCreateCart()
    {
        $cartId = session()->get('cart_id');

        if ($cartId) {
            $cart = Cart::with(['lines.purchasable.product'])->find($cartId);
            if ($cart) {
                return $cart;
            }
        }

        $cart = Cart::create([
            'currency_id' => Currency::getDefault()->id,
            'channel_id' => Channel::getDefault()->id,
        ]);

        session()->put('cart_id', $cart->id);

        return $cart;
    }

    public function getShippingOptions(Request $request)
    {
        $cart = $this->getOrCreateCart();

        if ($cart->lines->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        // Get address data from request
        $addressData = $request->input('address');

        if (!$addressData) {
            return response()->json(['error' => 'Address is required'], 400);
        }

        // Set shipping address to calculate shipping options
        $cart->setShippingAddress([
            'first_name' => $addressData['first_name'],
            'last_name' => $addressData['last_name'],
            'line_one' => $addressData['line_one'],
            'city' => $addressData['city'],
            'state' => $addressData['state'],
            'postcode' => $addressData['postcode'],
            'country_id' => \Lunar\Models\Country::first()->id ?? 1,
        ]);

        $cart->calculate();

        // Get shipping options
        $shippingOptions = \Lunar\Facades\ShippingManifest::getOptions($cart);

        $options = $shippingOptions->map(function($option) {
            return [
                'identifier' => $option->getIdentifier(),
                'name' => $option->getName(),
                'description' => $option->getDescription(),
                'price' => $option->price->decimal,
                'formatted_price' => number_format($option->price->decimal, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'options' => $options
        ]);
    }

    public function checkout(Request $request)
    {
        $cart = $this->getOrCreateCart();

        if ($cart->lines->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        // Get address data from request
        $addressData = $request->input('address');
        $shippingIdentifier = $request->input('shipping_option');

        if (!$addressData) {
            return response()->json(['error' => 'Address is required'], 400);
        }

        if (!$shippingIdentifier) {
            return response()->json(['error' => 'Shipping option is required'], 400);
        }

        // Add billing address to cart
        $cart->setBillingAddress([
            'first_name' => $addressData['first_name'],
            'last_name' => $addressData['last_name'],
            'line_one' => $addressData['line_one'],
            'city' => $addressData['city'],
            'state' => $addressData['state'],
            'postcode' => $addressData['postcode'],
            'country_id' => \Lunar\Models\Country::first()->id ?? 1,
        ]);

        // Set shipping address (same as billing)
        $cart->setShippingAddress([
            'first_name' => $addressData['first_name'],
            'last_name' => $addressData['last_name'],
            'line_one' => $addressData['line_one'],
            'city' => $addressData['city'],
            'state' => $addressData['state'],
            'postcode' => $addressData['postcode'],
            'country_id' => \Lunar\Models\Country::first()->id ?? 1,
        ]);

        $cart->calculate();

        // Get and set the selected shipping option
        $shippingOptions = \Lunar\Facades\ShippingManifest::getOptions($cart);
        $selectedOption = $shippingOptions->first(function($option) use ($shippingIdentifier) {
            return $option->getIdentifier() === $shippingIdentifier;
        });

        if (!$selectedOption) {
            return response()->json(['error' => 'Invalid shipping option'], 400);
        }

        $cart->setShippingOption($selectedOption);
        $cart->calculate();

        try {
            $order = $cart->createOrder();

            session()->forget('cart_id');

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id,
                'order_reference' => $order->reference
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    private function formatCartForJson($cart)
    {
        $formatted = [];

        foreach ($cart->lines as $line) {
            $product = $line->purchasable->product;
            $formatted[$product->id] = [
                'id' => $product->id,
                'name' => $product->translateAttribute('name'),
                'price' => $line->unitPrice ? $line->unitPrice->decimal : 0,
                'quantity' => $line->quantity
            ];
        }

        return $formatted;
    }
}