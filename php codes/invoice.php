<?php
session_start();
include 'includes/header.php';

if (!isset($_GET['order_id'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Order not found!</div></div>";
    include 'includes/footer.php';
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Get order details
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id")->fetch_assoc();

if (!$order) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Order not found!</div></div>";
    include 'includes/footer.php';
    exit();
}

// Get invoice from invoices table
$invoice = $conn->query("SELECT * FROM invoices WHERE order_id = $order_id")->fetch_assoc();

if (!$invoice) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Invoice not found!</div></div>";
    include 'includes/footer.php';
    exit();
}

$invoice_data = json_decode($invoice['invoice_data'], true);
$invoice_number = $invoice_data['invoice_number'] ?? 'INV-' . $order_id;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $invoice_number; ?> - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .invoice-container { max-width: 800px; margin: 100px auto 50px; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(37, 99, 235, 0.15); overflow: hidden; }
        .invoice-header { background: linear-gradient(135deg, #4169e1, #27408b); padding: 30px; color: #fff; }
        .invoice-body { padding: 30px; }
        .invoice-footer { background: #f8fafc; padding: 20px 30px; border-top: 2px dashed #e2e8f0; }
        .invoice-title { font-size: 2rem; font-weight: 700; }
        .invoice-info { background: linear-gradient(135deg, #f8fafc, #e0f2fe); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .invoice-table th { background: linear-gradient(135deg, #4169e1, #27408b); color: #fff; }
        .invoice-total { background: linear-gradient(135deg, #dbeafe, #e0f2fe); border-radius: 12px; padding: 20px; text-align: right; }
        .btn-print { background: linear-gradient(135deg, #4169e1, #27408b); color: #fff; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 600; cursor: pointer; }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(65, 105, 225, 0.3); }
        @media print { body { background: #fff; } .invoice-container { box-shadow: none; margin: 0; } .btn-print, .navbar, footer { display: none; } .invoice-container { max-width: 100%; } }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="row">
                <div class="col-md-6">
                    <h1 class="invoice-title">🧾 INVOICE</h1>
                    <p class="mb-0">Vinayak Footwear</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0"><strong>Invoice #:</strong> <?php echo $invoice_number; ?></p>
                    <p class="mb-0"><strong>Date:</strong> <?php echo date('d M Y, h:i A', strtotime($invoice['created_at'])); ?></p>
                    <p class="mb-0"><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                </div>
            </div>
        </div>
        
        <div class="invoice-body">
            <div class="invoice-info">
                <h5 class="mb-3">📦 Bill To:</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($invoice_data['customer']['name']); ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($invoice_data['customer']['email']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($invoice_data['customer']['phone']); ?></p>
                        <p class="mb-1"><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($invoice_data['customer']['address'])); ?></p>
                    </div>
                </div>
            </div>
            
            <table class="table table-striped invoice-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Image</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoice_data['items'] as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><img src="<?php echo $item['image']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;"></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                        <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6>Order Status: <span class="badge bg-<?php echo ($order['status'] == 'delivered') ? 'success' : (($order['status'] == 'cancelled') ? 'danger' : 'primary'); ?>"><?php echo ucfirst($order['status']); ?></span></h6>
                        <p class="mb-0"><strong>Payment Method:</strong> <?php echo $order['payment_method'] ?? 'Cash on Delivery'; ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="invoice-total">
                        <p class="mb-1">Subtotal: ₹<?php echo number_format($invoice_data['totals']['subtotal'], 2); ?></p>
                        <p class="mb-1">Discount: -₹<?php echo number_format($invoice_data['totals']['discount'], 2); ?></p>
                        <p class="mb-1">Delivery: <?php echo ($invoice_data['totals']['delivery'] == 0) ? '<span class="text-success">Free</span>' : '₹' . number_format($invoice_data['totals']['delivery'], 2); ?></p>
                        <h4 class="mb-0">Total: ₹<?php echo number_format($invoice_data['totals']['total'], 2); ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="invoice-footer text-center">
            <button onclick="window.print()" class="btn-print me-2"><i class="fas fa-print me-2"></i>Print Invoice</button>
            <a href="index.php" class="btn btn-outline-primary"><i class="fas fa-home me-2"></i>Back to Home</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>