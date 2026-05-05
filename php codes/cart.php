<?php
session_start();
include 'includes/header.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ========== HANDLE ALL ACTIONS FIRST ==========

// Add to cart (GET request from product_details.php)
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
    $size = isset($_GET['size']) ? $_GET['size'] : 'N/A';
    
    if ($id > 0 && $qty >= 1 && $qty <= 10) {
        $product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
        
        if ($product) {
            $_SESSION['cart'][$id] = [
                'qty' => $qty,
                'size' => $size,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image']
            ];
            
            header('Location: cart.php?added=1');
            exit();
        }
    }
}

// Remove from cart
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    unset($_SESSION['cart'][$id]);
}

// Update quantity
if (isset($_GET['action']) && $_GET['action'] == 'update' && isset($_GET['id']) && isset($_GET['qty'])) {
    $id = intval($_GET['id']);
    $qty = intval($_GET['qty']);
    
    if ($qty >= 1 && $qty <= 10 && isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty'] = $qty;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">🛒 Your Shopping Cart</h2>
        
        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                ✅ Product added to cart successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                <p class="text-muted">Your cart is empty!</p>
                <a href="products.php" class="btn btn-primary">Start Shopping 👟</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Size</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($_SESSION['cart'] as $id => $item):
                            $subtotal = $item['price'] * $item['qty'];
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo $item['image']; ?>" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.src='images/no-image.png'">
                            </td>
                            <td><strong><?php echo $item['name']; ?></strong></td>
                            <td><span class="badge bg-secondary"><?php echo isset($item['size']) ? $item['size'] : 'N/A'; ?></span></td>
                            <td>₹<?php echo number_format($item['price']); ?></td>
                            <td>
                                <input type="number" value="<?php echo $item['qty']; ?>" 
                                       min="1" max="10" 
                                       onchange="updateQuantity(<?php echo $id; ?>, this.value)"
                                       class="form-control" style="width: 80px;">
                            </td>
                            <td><strong>₹<?php echo number_format($subtotal); ?></strong></td>
                            <td>
                                <a href="?action=remove&id=<?php echo $id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item?')">
                                    🗑️ Remove
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <a href="products.php" class="btn btn-secondary">← Continue Shopping</a>
                </div>
                <div class="col-md-6 text-end">
                    <div class="card">
                        <div class="card-body">
                            <h4>Total: <strong class="text-primary">₹<?php echo number_format($total); ?></strong></h4>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="checkout.php" class="btn btn-success btn-lg mt-2">Proceed to Checkout</a>
                            <?php else: ?>
                                <p class="text-muted mt-2">Please <a href="login.php">login</a> to checkout</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
    function updateQuantity(id, qty) {
        if (qty < 1 || qty > 10) {
            alert('Quantity must be between 1 and 10!');
            location.reload();
            return;
        }
        window.location.href = '?action=update&id=' + id + '&qty=' + qty;
    }
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>