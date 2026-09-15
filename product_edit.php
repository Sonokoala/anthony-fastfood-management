<?php
require_once 'header.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['email']) || !in_array($_SESSION['roleID'], [1, 2])) {
    header("Location: login.php");
    exit;
}

$message = '';
$product = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (!empty($id) && !empty($name) && !empty($price)) {
        $sql = "UPDATE products SET name = ?, price = ?, description = ? WHERE id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("sdsi", $name, $price, $description, $id);
        
        if ($stmt->execute()) {
            $message = "Product updated successfully!";
        } else {
            $message = "Error updating product: " . $connection->error;
        }
        $stmt->close();
    }
}

// Get products for selection
$sql = "SELECT * FROM products ORDER BY name";
$result = $connection->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);

// Get specific product if ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - Anthony's Fast Food</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Edit Product</h2>
        
        <?php if ($message): ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4 mb-4">
                <h4>Select Product</h4>
                <div class="list-group">
                    <?php foreach ($products as $p): ?>
                        <a href="?id=<?php echo $p['id']; ?>" 
                           class="list-group-item list-group-item-action <?php echo (isset($_GET['id']) && $_GET['id'] == $p['id']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($p['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="col-md-8">
                <?php if ($product): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name*</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Price*</label>
                            <input type="number" class="form-control" id="price" name="price" 
                                   value="<?php echo $product['price']; ?>" step="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Product</button>
                        <a href="home.php" class="btn btn-secondary">Back to Home</a>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        Please select a product from the list to edit.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php require_once 'footer.php'; ?> 