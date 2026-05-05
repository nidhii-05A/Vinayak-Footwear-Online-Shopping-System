<?php
include 'includes/header.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Add new category
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Category added successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error adding category!</div>";
        }
        $stmt->close();
    }
}

// Delete category
if (isset($_POST['delete_category'])) {
    $cat_id = intval($_POST['category_id']);
    
    if ($cat_id > 0) {
        // First, set products category_id to NULL
        $conn->query("UPDATE products SET category_id = NULL WHERE category_id = $cat_id");
        
        // Then delete category
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $cat_id);
        
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Category deleted!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error deleting category!</div>";
        }
        $stmt->close();
    }
}

// Get all categories
$categories = $conn->query("SELECT c.*, COUNT(p.id) as product_count 
                           FROM categories c 
                           LEFT JOIN products p ON c.id = p.category_id 
                           GROUP BY c.id 
                           ORDER BY c.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5" style="padding-top: 0px;">
        <h2 class="mb-4 text-center">📂 Manage Categories</h2>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Operation completed successfully!</div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Add Category Form -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Category</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g., Men, Women, Kids" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description (Optional)</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Brief description of category"></textarea>
                            </div>
                            <button type="submit" name="add_category" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Add Category
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Categories List -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i> All Categories</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($categories && $categories->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Products Count</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($cat = $categories->fetch_assoc()): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?php echo $cat['id']; ?></span></td>
                                            <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $cat['product_count']; ?> products</span>
                                            </td>
                                            <td>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                                    <button type="submit" name="delete_category" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category? Products in this category will not be deleted.')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No categories found!</p>
                                <p class="text-muted">Add your first category using the form.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="row mt-4">
            <div class="col-12">
                <a href="admin_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>