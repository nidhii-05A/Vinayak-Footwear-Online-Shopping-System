<?php
include 'includes/header.php';

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get order details
$order = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order->bind_param("ii", $order_id, $user_id);
$order->execute();
$order_result = $order->get_result();

if ($order_result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Order not found!</div>";
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items->bind_param("i", $order_id);
$items->execute();
$items_result = $items->get_result();
?>

<div class="container mt-4">
    <h2>Order #<?php echo $order_id; ?></h2>
    <p>Status: <strong><?php echo ucfirst($order['status']); ?></strong></p>
    <p>Date: <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
    
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items_result->fetch_assoc()): ?>
            <tr>
                <td>
                    <img src="<?php echo $item['image']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                    <?php echo $item['name']; ?>
                </td>
                <td><?php echo $item['quantity']; ?></td>
                <td>₹<?php echo $item['price']; ?></td>
                <td>₹<?php echo $item['quantity'] * $item['price']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                <td><strong>₹<?php echo $order['total']; ?></strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php include 'includes/footer.php'; ?>