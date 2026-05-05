<?php
include 'includes/header.php';

$result = $conn->query("SELECT * FROM orders 
WHERE user_id={$_SESSION['user_id']} 
ORDER BY id DESC");
?>

<div class="container mt-5">
<h3>Your Orders</h3>

<?php while($row=$result->fetch_assoc()): ?>
<div class="card p-3 mb-3">
Order ID: <?php echo $row['id']; ?><br>
Total: ₹<?php echo $row['total']; ?><br>
Status: <strong><?php echo ucfirst($row['status']); ?></strong><br>
Date: <?php echo $row['created_at']; ?>
</div>
<?php endwhile; ?>

</div>

<?php include 'includes/footer.php'; ?>