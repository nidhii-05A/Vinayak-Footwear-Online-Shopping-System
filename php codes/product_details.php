<?php
include 'includes/header.php';

$product_id = $_GET['id'];
$product = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$product->bind_param("i", $product_id);
$product->execute();
$product_result = $product->get_result();

if ($product_result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Product not found!</div>";
    exit();
}

$product = $product_result->fetch_assoc();
$sizes = explode(',', $product['sizes']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5" style="padding-top: 80px;">
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo $product['image']; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='images/no-image.png'">
            </div>
            <div class="col-md-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
                    </ol>
                </nav>
                
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p class="text-muted">Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
                <h3 class="text-primary">₹<?php echo number_format($product['price'], 2); ?></h3>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge bg-success">In Stock (<?php echo $product['stock']; ?>)</span>
                <?php else: ?>
                    <span class="badge bg-danger">Out of Stock</span>
                <?php endif; ?>
                
                <!-- Form submits to cart.php -->
                <form method="GET" action="cart.php" class="mt-4">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <!-- Size Selection -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Select Size:</strong></label>
                        <div class="btn-group" role="group">
                            <?php foreach ($sizes as $size): ?>
                                <input type="radio" class="btn-check" name="size" id="size_<?php echo trim($size); ?>" value="<?php echo trim($size); ?>" required>
                                <label class="btn btn-outline-primary" for="size_<?php echo trim($size); ?>"><?php echo trim($size); ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Quantity -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Quantity:</strong></label>
                        <input type="number" name="qty" class="form-control" value="1" min="1" max="<?php echo $product['stock']; ?>" style="width: 100px;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" <?php echo ($product['stock'] == 0) ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>