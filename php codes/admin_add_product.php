<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories");

// Add new product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $sizes = implode(',', $_POST['sizes']);
    $image = 'images/' . basename($_FILES['image']['name']);
    
    // Upload image
    if (move_uploaded_file($_FILES['image']['tmp_name'], $image)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, sizes, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiiss", $name, $description, $price, $stock, $category_id, $sizes, $image);
        
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Product added successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error adding product!</div>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Add New Product</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Price (₹)</label>
                                        <input type="number" name="price" class="form-control" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Stock Quantity</label>
                                        <input type="number" name="stock" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php while ($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Available Sizes</label>
                                <div class="btn-group" role="group">
                                    <?php $size_options = ['5', '6', '7', '8', '9', '10', '11']; ?>
                                    <?php foreach ($size_options as $size): ?>
                                        <input type="checkbox" class="btn-check" name="sizes[]" id="size_<?php echo $size; ?>" value="<?php echo $size; ?>">
                                        <label class="btn btn-outline-secondary" for="size_<?php echo $size; ?>"><?php echo $size; ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Product Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*" required onchange="previewImage(this)">
                            </div>
                            <img id="imagePreview" src="" class="img-fluid rounded" style="display: none;">
                        </div>
                    </div>
                    <button type="submit" name="add_product" class="btn btn-success w-100 btn-lg">Add Product</button>
                    <a href="admin_dashboard.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
    
    <script>
    function previewImage(input) {
        var preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>