function addToCart(productId) {
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data.cart, data.total);
        }
    })
    .catch(error => console.error('Error:', error));
}

function removeFromCart(productId) {
    fetch('/cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data.cart, data.total);
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateCartDisplay(cart, total) {
    const cartItemsContainer = document.getElementById('cart-items');
    const cartTotalElement = document.getElementById('cart-total');

    if (Object.keys(cart).length === 0) {
        cartItemsContainer.innerHTML = '<p class="empty-cart">Your cart is empty</p>';
        cartTotalElement.textContent = '0.00';
        return;
    }

    let cartHTML = '';

    Object.values(cart).forEach(item => {
        cartHTML += `
            <div class="cart-item">
                <div class="cart-item-details">
                    <h4>${item.name}</h4>
                    <p>$${parseFloat(item.price).toFixed(2)} x ${item.quantity}</p>
                </div>
                <button class="remove-btn" data-product-id="${item.id}">×</button>
            </div>
        `;
    });

    cartItemsContainer.innerHTML = cartHTML;
    cartTotalElement.textContent = total;

    // Reattach remove button listeners
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            removeFromCart(this.getAttribute('data-product-id'));
        });
    });
}

function fetchShippingOptions() {
    const addressData = {
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        line_one: document.getElementById('line_one').value,
        city: document.getElementById('city').value,
        state: document.getElementById('state').value,
        postcode: document.getElementById('postcode').value
    };

    // Validate address fields before fetching shipping options
    if (!addressData.first_name || !addressData.last_name || !addressData.line_one ||
        !addressData.city || !addressData.state || !addressData.postcode) {
        return;
    }

    fetch('/shipping-options', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ address: addressData })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.options) {
            displayShippingOptions(data.options);
        }
    })
    .catch(error => console.error('Error fetching shipping options:', error));
}

function displayShippingOptions(options) {
    const shippingOptionsContainer = document.getElementById('shipping-options-container');
    const shippingOptionsDiv = document.getElementById('shipping-options');

    if (options.length === 0) {
        shippingOptionsContainer.style.display = 'none';
        return;
    }

    let optionsHTML = '';
    options.forEach((option, index) => {
        optionsHTML += `
            <div class="shipping-option">
                <input type="radio" id="shipping_option_${index}" name="shipping_option" value="${option.identifier}" ${index === 0 ? 'checked' : ''} required>
                <label for="shipping_option_${index}">
                    <strong>${option.name}</strong> - $${option.formatted_price}
                </label>
            </div>
        `;
    });

    shippingOptionsDiv.innerHTML = optionsHTML;
    shippingOptionsContainer.style.display = 'block';

    // Update button text to indicate next step
    const checkoutBtn = document.getElementById('checkout-btn');
    checkoutBtn.textContent = 'Complete Order';
}

function checkout() {
    const addressForm = document.getElementById('address-form');
    const checkoutBtn = document.getElementById('checkout-btn');
    const shippingOptionsContainer = document.getElementById('shipping-options-container');

    // If form is hidden, show it
    if (addressForm.style.display === 'none') {
        addressForm.style.display = 'block';
        checkoutBtn.textContent = 'Continue to Shipping';
        return;
    }

    // Collect address data
    const addressData = {
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        line_one: document.getElementById('line_one').value,
        city: document.getElementById('city').value,
        state: document.getElementById('state').value,
        postcode: document.getElementById('postcode').value
    };

    // Validate address fields
    if (!addressData.first_name || !addressData.last_name || !addressData.line_one ||
        !addressData.city || !addressData.state || !addressData.postcode) {
        alert('Please fill in all address fields');
        return;
    }

    // If shipping options are not shown yet, fetch them
    if (shippingOptionsContainer.style.display === 'none') {
        fetchShippingOptions();
        return;
    }

    // Get selected shipping option
    const selectedShipping = document.querySelector('input[name="shipping_option"]:checked');
    if (!selectedShipping) {
        alert('Please select a shipping option');
        return;
    }

    // Complete checkout
    fetch('/checkout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            address: addressData,
            shipping_option: selectedShipping.value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order created successfully! Order Reference: ' + data.order_reference);
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to create order'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create order');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            addToCart(productId);
        });
    });

    // Remove from cart buttons
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            removeFromCart(productId);
        });
    });

    // Checkout button
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            checkout();
        });
    }
});
