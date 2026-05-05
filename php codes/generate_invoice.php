<?php include 'includes/header.php'; if (!isLoggedIn()) header('Location: login.php'); ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="text-center mb-4">Invoice 📄</h2>
                    <?php
                    $order_id = $_GET['order_id'];
                    $order = $conn->query("SELECT * FROM orders WHERE id=$order_id AND user_id={$_SESSION['user_id']}")->fetch_assoc();
                    if ($order) {
                        $user = $conn->query("SELECT * FROM users WHERE id={$_SESSION['user_id']}")->fetch_assoc();
                        $items = $conn->query("SELECT order_items.*, products.name FROM order_items JOIN products ON order_items.product_id = products.id WHERE order_id=$order_id");
                        echo "<div id='invoice'><h4>Order ID: $order_id</h4><p><strong>Customer:</strong> {$user['name']}</p><p><strong>Email:</strong> {$user['email']}</p><p><strong>Date:</strong> {$order['created_at']}</p><table class='table table-striped'><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>";
                        while ($item = $items->fetch_assoc()) {
                            echo "<tr><td>{$item['name']}</td><td>{$item['quantity']}</td><td>₹{$item['price']}</td><td>₹" . ($item['quantity'] * $item['price']) . "</td></tr>";
                        }
                        echo "</tbody></table><h3>Total: ₹{$order['total']}</h3></div><button onclick='window.print()' class='btn btn-primary'>Print Invoice</button>";
                    } else {
                        echo "<p>Invoice not found.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>