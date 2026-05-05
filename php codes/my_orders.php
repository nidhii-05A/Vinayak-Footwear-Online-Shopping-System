<?php
session_start();
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5 mb-5">
        <h2 class="mb-4">📦 My Orders</h2>
        
        <?php if ($orders->num_rows == 0): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                <p class="text-muted">No orders yet!</p>
                <a href="products.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Order #<?php echo $order['id']; ?></h5>
                                    <span class="badge bg-<?php 
                                        echo match($order['status']) {
                                            'placed' => 'primary',
                                            'confirmed' => 'info',
                                            'shipped' => 'warning',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                                <p class="card-text">
                                    <strong>Date:</strong> <?php echo date('d M Y', strtotime($order['created_at'])); ?><br>
                                    <strong>Total:</strong> ₹<?php echo number_format($order['total'], 2); ?><br>
                                    <strong>Items:</strong> <?php 
                                        $count = $conn->query("SELECT COUNT(*) FROM order_items WHERE order_id = {$order['id']}")->fetch_row()[0];
                                        echo $count;
                                    ?>
                                </p>
                                <a href="invoice.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-file-invoice me-1"></i>View Invoice
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>