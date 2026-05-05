<?php
include 'includes/header.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Handle product update
if (isset($_POST['update_product'])) {
    $product_id = intval($_POST['product_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    $sizes = trim($_POST['sizes']);
    
    if ($product_id > 0 && !empty($name) && $price >= 0) {
        if (!empty($_FILES['image']['name'])) {
            $image = 'images/' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image)) {
                $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, sizes=?, image=? WHERE id=?");
                $stmt->bind_param("ssdiissi", $name, $description, $price, $stock, $category_id, $sizes, $image, $product_id);
            }
        } else {
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, sizes=? WHERE id=?");
            $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $category_id, $sizes, $product_id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Product updated successfully!";
        }
        $stmt->close();
    }
    
    header('Location: admin_products.php');
    exit();
}

// Handle delete product
if (isset($_POST['delete_product'])) {
    $product_id = intval($_POST['product_id']);
    
    if ($product_id > 0) {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Product deleted successfully!";
        }
        $stmt->close();
    }
    
    header('Location: admin_products.php');
    exit();
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

switch($filter) {
    case 'in_stock':
        $products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock > 0 ORDER BY p.id DESC");
        break;
    case 'out_of_stock':
        $products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock = 0 ORDER BY p.id DESC");
        break;
    default:
        $products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5" style="padding-top: 0px;">
        <h2 class="mb-4 text-center">Manage Products</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="mb-3">
            <a href="?filter=all" class="btn <?php echo ($filter == 'all') ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
            <a href="?filter=in_stock" class="btn <?php echo ($filter == 'in_stock') ? 'btn-success' : 'btn-outline-success'; ?>">In Stock</a>
            <a href="?filter=out_of_stock" class="btn <?php echo ($filter == 'out_of_stock') ? 'btn-danger' : 'btn-outline-danger'; ?>">Out of Stock</a>
            <a href="admin_add_product.php" class="btn btn-success float-end">+ Add Product</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Sizes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($products && $products->num_rows > 0): ?>
                                <?php while ($p = $products->fetch_assoc()): ?>
                                <tr>
                                    <td><img src="<?php echo $p['image']; ?>" width="50" height="50" style="object-fit:cover;border-radius:5px" onerror="this.src='images/no-image.png'"></td>
                                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                                    <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                                    <td>₹<?php echo number_format($p['price']); ?></td>
                                    <td><span class="badge <?php echo $p['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>"><?php echo $p['stock']; ?></span></td>
                                    <td><?php echo htmlspecialchars($p['sizes']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $p['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $p['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">No products found!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
    
    <!-- Edit Modals (Outside Table) -->
    <?php 
    $products2 = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
    while ($p = $products2->fetch_assoc()): 
    ?>
    <div class="modal fade" id="editModal<?php echo $p['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Product - <?php echo htmlspecialchars($p['name']); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($p['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($p['description']); ?></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label>Price (₹)</label>
                                            <input type="number" name="price" class="form-control" value="<?php echo $p['price']; ?>" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label>Stock</label>
                                            <input type="number" name="stock" class="form-control" value="<?php echo $p['stock']; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Category</label>
                                    <select name="category_id" class="form-select">
                                        <?php 
                                        $cats = $conn->query("SELECT * FROM categories");
                                        while($c = $cats->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $c['id']; ?>" <?php echo ($p['category_id'] == $c['id']) ? 'selected' : ''; ?>>
                                                <?php echo $c['name']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Sizes (comma separated)</label>
                                    <input type="text" name="sizes" class="form-control" value="<?php echo htmlspecialchars($p['sizes']); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Current Image</label>
                                <img src="<?php echo $p['image']; ?>" class="img-fluid rounded mb-2" onerror="this.src='images/no-image.png'">
                                <label>Change Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_product" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="deleteModal<?php echo $p['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($p['name']); ?></strong>?</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_product" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>