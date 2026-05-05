<?php
session_start();
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container mt-5" style="padding-top:0;">
        <h2 class="text-center mb-3">👟 Our Products</h2>
        
        <!-- Search Form -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if(isset($_GET['search']) && $_GET['search'] != ''): ?>
                            <a href="products.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php
                        $cats = $conn->query("SELECT * FROM categories");
                        while($c = $cats->fetch_assoc()):
                        ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo $c['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </form>
        
        <!-- Products -->
        <div class="row">
            <?php
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $cat = isset($_GET['category']) ? $_GET['category'] : '';
            
            if ($search) {
                $s = "%$search%";
                $products = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
                $products->bind_param("ss", $s, $s);
                $products->execute();
                $products = $products->get_result();
            } elseif ($cat) {
                $products = $conn->prepare("SELECT * FROM products WHERE category_id = ?");
                $products->bind_param("i", $cat);
                $products->execute();
                $products = $products->get_result();
            } else {
                $products = $conn->query("SELECT * FROM products");
            }
            
            if ($products && $products->num_rows > 0):
                while($p = $products->fetch_assoc()):
                    $sizes = explode(',', $p['sizes']);
            ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo $p['image']; ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?php echo $p['name']; ?>" onerror="this.src='images/no-image.png'">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $p['name']; ?></h5>
                            <p class="card-text small"><?php echo substr($p['description'], 0, 50); ?>...</p>
                            <h4 class="text-primary">₹<?php echo number_format($p['price']); ?></h4>
                            <p class="small text-muted">Stock: <?php echo $p['stock']; ?></p>
                            
                            <!-- Add to Cart Form - SUBMITS TO cart.php -->
                            <form method="GET" action="cart.php" class="d-flex gap-2 mb-2">
                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <select name="size" class="form-select form-select-sm" style="width: 80px;">
                                    <?php foreach($sizes as $size): ?>
                                        <option value="<?php echo trim($size); ?>"><?php echo trim($size); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="qty" value="1" min="1" max="10" class="form-control form-select-sm" style="width: 60px;">
                                <button type="submit" class="btn btn-success btn-sm" <?php echo ($p['stock'] == 0) ? 'disabled' : ''; ?>>
                                    🛒
                                </button>
                            </form>
                            
                            <a href="product_details.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php
                endwhile;
            else:
            ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">No products found!</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>