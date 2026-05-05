<?php include 'includes/header.php'; ?>
<div class="container mt-5">
    <div class="jumbotron text-center p-5 mb-5">
        <h1>Welcome to Vinayak Footwear 👟</h1>
        <p>Your one-stop shop for quality Shoes and Accessories.</p>
        <a href="products.php" class="btn btn-primary btn-lg">Shop Now</a>
    </div>
    <h2 class="text-center mb-5">Featured Products</h2>
    <div class="row">
        <?php
        $result = $conn->query("SELECT * FROM products LIMIT 6");
        while ($row = $result->fetch_assoc()) {
            echo "<div class='col-md-4 mb-4'><div class='card h-100'><img src='{$row['image']}' class='card-img-top' alt='{$row['name']}' style='height: 200px; object-fit: cover;'><div class='card-body d-flex flex-column'><h5 class='card-title'>{$row['name']}</h5><p class='card-text'>₹{$row['price']}</p><a href='product_details.php?id={$row['id']}' class='btn btn-primary mt-auto'>View Details</a></div></div></div>";
        }
        ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>