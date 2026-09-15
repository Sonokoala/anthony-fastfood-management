<?php
require_once 'header.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['email']) || !in_array($_SESSION['roleID'], [1, 2])) {
    header("Location: login.php");
    exit;
}

$message = '';

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $id = $_POST['id'];
    
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Product deleted successfully!";
    } else {
        $message = "Error deleting product: " . $connection->error;
    }
    $stmt->close();
}

// Get all products
$sql = "SELECT * FROM products ORDER BY name";
$result = $connection->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-4">
    <h2>Delete Product</h2>
    
    <?php if ($message): ?>
    <div class="alert alert-info">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($product['description']); ?></td>
                    <td>
                        <form method="POST" action="" style="display: inline;"
                              onsubmit="return confirm('Are you sure you want to delete this product?');">
                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="home.php" class="btn btn-secondary">Back to Home</a>
</div>

<?php require_once 'footer.php'; ?> 