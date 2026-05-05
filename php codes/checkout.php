<?php
include 'includes/header.php';
require 'includes/functions.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) { echo "<div class='container mt-5'><div class='alert alert-warning'>Your cart is empty.</div></div>"; include 'includes/footer.php'; exit(); }

function sanitizeInput($input) { return htmlspecialchars(trim($input)); }

$subtotal = 0;
foreach ($_SESSION['cart'] as $id => $details) { $product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc(); $subtotal += $product['price'] * $details['qty']; }

$discount = 0; $discountAmount = 0; $message = '';
if (isset($_POST['apply_coupon'])) {
    $coupon = strtoupper(sanitizeInput($_POST['coupon_code']));
    if ($coupon === "FOOT10") { $discount = 0.10; $message = "Coupon applied! 10% discount added."; }
    else { $message = "Invalid coupon code."; }
}
$discountAmount = $subtotal * $discount;
$deliveryCharge = ($subtotal < 1000) ? 50 : 0;
$total = $subtotal - $discountAmount + $deliveryCharge;

if (isset($_POST['cod_place_order'])) {
    $fullname = sanitizeInput($_POST['fullname']); $phone = sanitizeInput($_POST['phone']); $address = sanitizeInput($_POST['address']);
    $otp = rand(100000, 999999);
    $_SESSION['cod_data'] = ['fullname' => $fullname, 'phone' => $phone, 'address' => $address, 'total' => $total];
    $_SESSION['cod_otp'] = $otp;
    $user = $conn->query("SELECT email FROM users WHERE id={$_SESSION['user_id']}")->fetch_assoc();
    sendOTP($user['email'], $otp);
    echo "<div class='alert alert-info text-center'>OTP sent. Enter OTP to confirm order.</div>";
}

if (isset($_POST['verify_cod_otp'])) {
    if ($_POST['entered_otp'] == $_SESSION['cod_otp']) {
        $data = $_SESSION['cod_data'];
        $user = $conn->query("SELECT * FROM users WHERE id={$_SESSION['user_id']}")->fetch_assoc();
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, discount, status, address, phone, otp, otp_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $status = 'placed'; $otp_verified = 1;
        $stmt->bind_param("iddisssi", $_SESSION['user_id'], $data['total'], $discountAmount, $status, $data['address'], $data['phone'], $_SESSION['cod_otp'], $otp_verified);
        
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id; $stmt->close();
            $items_data = [];
            foreach ($_SESSION['cart'] as $id => $details) {
                $product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
                $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $id, {$details['qty']}, {$product['price']})");
                $conn->query("UPDATE products SET stock = stock - {$details['qty']} WHERE id=$id");
                $items_data[] = ['name' => $product['name'], 'image' => $product['image'], 'quantity' => $details['qty'], 'price' => $product['price']];
            }
            $invoice_number = 'INV-' . date('Y') . '-' . str_pad($order_id, 5, '0', STR_PAD_LEFT);
            $invoice_data = json_encode(['invoice_number' => $invoice_number, 'date' => date('Y-m-d H:i:s'), 'customer' => ['name' => $user['username'], 'email' => $user['email'], 'phone' => $data['phone'], 'address' => $data['address']], 'items' => $items_data, 'totals' => ['subtotal' => $subtotal, 'discount' => $discountAmount, 'delivery' => $deliveryCharge, 'total' => $data['total']]]);
            $escaped_data = mysqli_real_escape_string($conn, $invoice_data);
            $conn->query("INSERT INTO invoices (order_id, invoice_data) VALUES ($order_id, '$escaped_data')");
            unset($_SESSION['cart'], $_SESSION['cod_otp'], $_SESSION['cod_data']);
            echo "<div class='alert alert-success text-center'>Order Confirmed! 🎉<br>Order ID: $order_id<br><a href='invoice.php?order_id=$order_id' class='btn btn-primary mt-2'><i class='fas fa-file-invoice me-2'></i>View Invoice</a></div>";
        } else { echo "<div class='alert alert-danger text-center'>Order failed!</div>"; }
    } else { echo "<div class='alert alert-danger text-center'>Invalid OTP!</div>"; }
}

if (isset($_POST['upi_confirm'])) {
    $user = $conn->query("SELECT * FROM users WHERE id={$_SESSION['user_id']}")->fetch_assoc();
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total, discount, status, otp_verified) VALUES (?, ?, ?, 'paid', 1)");
    $stmt->bind_param("idd", $_SESSION['user_id'], $total, $discountAmount);
    $stmt->execute(); $order_id = $stmt->insert_id; $stmt->close();
    $items_data = [];
    foreach ($_SESSION['cart'] as $id => $details) {
        $product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
        $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $id, {$details['qty']}, {$product['price']})");
        $conn->query("UPDATE products SET stock = stock - {$details['qty']} WHERE id=$id");
        $items_data[] = ['name' => $product['name'], 'image' => $product['image'], 'quantity' => $details['qty'], 'price' => $product['price']];
    }
    $invoice_number = 'INV-' . date('Y') . '-' . str_pad($order_id, 5, '0', STR_PAD_LEFT);
    $invoice_data = json_encode(['invoice_number' => $invoice_number, 'date' => date('Y-m-d H:i:s'), 'customer' => ['name' => $user['username'], 'email' => $user['email'], 'phone' => '', 'address' => ''], 'items' => $items_data, 'totals' => ['subtotal' => $subtotal, 'discount' => $discountAmount, 'delivery' => $deliveryCharge, 'total' => $total]]);
    $escaped_data = mysqli_real_escape_string($conn, $invoice_data);
    $conn->query("INSERT INTO invoices (order_id, invoice_data) VALUES ($order_id, '$escaped_data')");
    unset($_SESSION['cart']);
    echo "<div class='alert alert-success text-center'>Payment Successful! 🎉<br>Order ID: $order_id<br><a href='invoice.php?order_id=$order_id' class='btn btn-primary mt-2'><i class='fas fa-file-invoice me-2'></i>View Invoice</a></div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-card">
            <div class="checkout-header"><h3>🛒 Checkout</h3></div>
            <div class="checkout-body">
                <div class="checkout-section" data-icon="📋">
                    <h5 data-icon="📋">Order Summary</h5>
                    <div class="order-summary">
                        <div class="order-summary-row"><span>Subtotal</span><span>₹<?php echo number_format($subtotal, 2); ?></span></div>
                        <div class="order-summary-row"><span>Discount</span><span>- ₹<?php echo number_format($discountAmount, 2); ?></span></div>
                        <div class="order-summary-row"><span>Delivery</span><span><?php echo $deliveryCharge == 0 ? '<span class="text-success">Free</span>' : '₹' . $deliveryCharge; ?></span></div>
                        <div class="order-summary-row total"><span>Total</span><span>₹<?php echo number_format($total, 2); ?></span></div>
                    </div>
                    <form method="POST" class="coupon-section">
                        <input type="text" name="coupon_code" placeholder="Enter FOOT10 for 10% off" value="<?php echo $_POST['coupon_code'] ?? ''; ?>">
                        <button type="submit" name="apply_coupon">Apply</button>
                    </form>
                    <?php if ($message): ?><p class="coupon-message <?php echo strpos($message, 'applied') !== false ? 'success' : 'error'; ?>"><?php echo $message; ?></p><?php endif; ?>
                </div>
                <div class="checkout-section" data-icon="💵">
                    <h5 data-icon="💵">Cash on Delivery</h5>
                    <form method="POST">
                        <div class="checkout-form-group"><label>Full Name</label><input type="text" name="fullname" placeholder="Enter your full name" required></div>
                        <div class="checkout-form-group"><label>Phone Number</label><input type="text" name="phone" placeholder="Enter phone number" required></div>
                        <div class="checkout-form-group"><label>Delivery Address</label><textarea name="address" placeholder="Enter full delivery address" required></textarea></div>
                        <button type="submit" name="cod_place_order" class="btn-checkout btn-checkout-cod">Place Order (COD)</button>
                    </form>
                </div>
                <?php if (isset($_SESSION['cod_otp'])): ?>
                <div class="otp-section">
                    <h6>🔐 Enter OTP to Confirm Order</h6>
                    <form method="POST">
                        <div class="checkout-form-group"><input type="text" name="entered_otp" placeholder="Enter 6-digit OTP" maxlength="6" required></div>
                        <button type="submit" name="verify_cod_otp" class="btn-checkout">Verify & Confirm Order</button>
                    </form>
                </div>
                <?php endif; ?>
                <div class="checkout-section" data-icon="📱">
                    <h5 data-icon="📱">UPI Payment</h5>
                    <div class="upi-qr-section">
                        <img src="images/gpay_qr.png" width="200" alt="UPI QR Code">
                        <p>📱 Scan QR code with any UPI app<br><small class="text-muted">(Google Pay, PhonePe, Paytm, BHIM)</small></p>
                        <form method="POST"><button type="submit" name="upi_confirm" class="btn-checkout btn-checkout-upi">✓ I Have Paid</button></form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>