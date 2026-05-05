<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$user = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user->bind_param("i", $user_id);
$user->execute();
$user_result = $user->get_result();
$user_data = $user_result->fetch_assoc();

// Get user orders
$orders = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders->bind_param("i", $user_id);
$orders->execute();
$orders_result = $orders->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h2>Welcome back, <?php echo htmlspecialchars($user_data['username']); ?>! 👟</h2>
            <p>Manage your profile and track your orders</p>
        </div>
        
        <div class="row">
            <!-- Profile Card -->
            <div class="col-lg-4 mb-4">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h4><i class="fas fa-user-circle me-2"></i>My Profile</h4>
                    </div>
                    <div class="dashboard-card-body">
                        <div class="profile-section">
                            <h5><i class="fas fa-envelope me-2"></i>Contact Info</h5>
                            <div class="profile-item">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <strong>Email</strong>
                                    <p><?php echo htmlspecialchars($user_data['email']); ?></p>
                                </div>
                            </div>
                            <div class="profile-item">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <strong>Phone</strong>
                                    <p><?php echo htmlspecialchars($user_data['phone'] ?? 'Not set'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="profile-section mt-3">
                            <h5><i class="fas fa-home me-2"></i>Location</h5>
                            <div class="profile-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <strong>Pincode</strong>
                                    <p><?php echo htmlspecialchars($user_data['pincode'] ?? 'Not set'); ?></p>
                                </div>
                            </div>
                            <div class="profile-item">
                                <i class="fas fa-address-card"></i>
                                <div>
                                    <strong>Address</strong>
                                    <p><?php echo nl2br(htmlspecialchars($user_data['address'] ?? 'Not set')); ?></p>
                                </div>
                            </div>
                            <div class="profile-item">
                                <i class="fas fa-calendar-alt"></i>
                                <div>
                                    <strong>Member Since</strong>
                                    <p><?php echo date('d M Y', strtotime($user_data['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dashboard-actions">
                            <a href="edit_profile.php" class="btn-dashboard btn-dashboard-edit">
                                <i class="fas fa-edit"></i>Edit Profile
                            </a>
                            <a href="logout.php" class="btn-dashboard btn-dashboard-logout">
                                <i class="fas fa-sign-out-alt"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Orders Card -->
            <div class="col-lg-8 mb-4">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h4><i class="fas fa-shopping-bag me-2"></i>My Orders</h4>
                    </div>
                    <div class="dashboard-card-body" style="padding:0;">
                        <?php if ($orders_result->num_rows == 0): ?>
                            <div class="empty-orders">
                                <i class="fas fa-shopping-basket"></i>
                                <h5>No orders yet</h5>
                                <p>Start shopping to see your orders here</p>
                                <a href="products.php" class="btn-dashboard btn-dashboard-edit">
                                    <i class="fas fa-store"></i>Start Shopping
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="orders-table">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><span class="order-id">#<?php echo $order['id']; ?></span></td>
                                            <td><span class="order-total">₹<?php echo number_format($order['total'], 2); ?></span></td>
                                            <td>
                                                <span class="order-badge order-badge-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><span class="order-date"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></span></td>
                                            <td>
                                                <div class="order-actions">
                                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn-order-view">View</a>
                                                    <a href="invoice.php?order_id=<?php echo $order['id']; ?>" class="btn-order-invoice" target="_blank">Invoice</a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>