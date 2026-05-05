// Add to cart functionality with size and quantity
function addToCart(productId) {
    const size = document.getElementById('size-' + productId).value;
    const qty = document.getElementById('qty-' + productId).value;
    if (qty < 1 || qty > 10) {
        alert('Quantity must be between 1 and 10!');
        return;
    }
    fetch('cart.php?action=add&id=' + productId + '&size=' + size + '&qty=' + qty)
        .then(response => response.text())
        .then(data => alert('Added to cart! 👟'));
}

// Update cart quantity
function updateQuantity(productId, quantity) {
    if (quantity < 1 || quantity > 10) {
        alert('Quantity must be between 1 and 10!');
        return;
    }
    fetch('cart.php?action=update&id=' + productId + '&qty=' + quantity)
        .then(response => response.text())
        .then(data => location.reload());
}