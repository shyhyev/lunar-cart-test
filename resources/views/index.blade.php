<x-layouts.app>
    <div class="container">
        <div class="main-content">
            <div class="products-section">
                <div class="products">
                    @foreach ($products as $product)
                        <div class="product-card">
                            <div class="product-info">
                                <h2>{{ $product->translateAttribute('name') }}</h2>
                                <p>{{ $product->translateAttribute('description') }}</p>
                                @if ($product->variants->first()?->prices->first())
                                    <p class="price">Price:
                                        ${{ number_format($product->variants->first()->prices->first()->price->decimal, 2) }}
                                    </p>
                                @endif
                            </div>
                            <button
                                class="add-to-cart-btn"
                                data-product-id="{{ $product->id }}"
                            >Add to Cart</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="cart-sidebar">
                <h2>Shopping Cart</h2>
                <div
                    class="cart-items"
                    id="cart-items"
                >
                    @if ($cart->lines->isEmpty())
                        <p class="empty-cart">Your cart is empty</p>
                    @else
                        @foreach ($cart->lines as $line)
                            <div class="cart-item">
                                <div class="cart-item-details">
                                    <h4>{{ $line->purchasable->product->translateAttribute('name') }}</h4>

                                    <p>${{ $line->unitPrice ? number_format($line->unitPrice->decimal, 2) : '0.00' }} x
                                        {{ $line->quantity }}
                                    </p>
                                </div>
                                <button
                                    class="remove-btn"
                                    data-product-id="{{ $line->purchasable->product_id }}"
                                >×</button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="cart-total">
                    <strong>Total: $<span
                            id="cart-total">{{ $cart->lines->isEmpty() || !$cart->total ? '0.00' : number_format($cart->total->decimal, 2) }}</span></strong>
                </div>

                <div class="address-form" id="address-form" style="display: none;">
                    <h3>Billing Address</h3>
                    <input type="text" id="first_name" placeholder="First Name" required>
                    <input type="text" id="last_name" placeholder="Last Name" required>
                    <input type="text" id="line_one" placeholder="Address Line 1" required>
                    <input type="text" id="city" placeholder="City" required>
                    <input type="text" id="state" placeholder="State" required>
                    <input type="text" id="postcode" placeholder="Postal Code" required>

                    <div id="shipping-options-container" style="display: none; margin-top: 20px;">
                        <h3>Shipping Options</h3>
                        <div id="shipping-options"></div>
                    </div>
                </div>

                <button class="checkout-btn" id="checkout-btn">Checkout</button>
            </div>
        </div>
    </div>
</x-layouts.app>
