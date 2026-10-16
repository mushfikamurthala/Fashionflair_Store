<?php

session_start();

require_once "config/db_connect.php";
require_once "includes/admin_check.php";


/* =====================================================
   CHECK PRODUCT ID
===================================================== */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: products.php");
    exit;
}

$product_id = (int) $_GET['id'];


/* =====================================================
   GET PRODUCT DETAILS
===================================================== */

$stmt = $conn->prepare("
    SELECT
        p.ProductID,
        p.ProductName,
        p.Brand,
        p.Price,
        p.StockQuantity,
        p.Image,
        c.CategoryName
    FROM products p
    INNER JOIN categories c
        ON p.CategoryID = c.CategoryID
    WHERE p.ProductID = ?
");

$stmt->bind_param("i", $product_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    header("Location: products.php");
    exit;
}

$product = $result->fetch_assoc();

$stmt->close();


/* =====================================================
   DELETE PRODUCT
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST['confirm_delete'])) {

        $delete_stmt = $conn->prepare("
            DELETE FROM products
            WHERE ProductID = ?
        ");

        $delete_stmt->bind_param("i", $product_id);

        if ($delete_stmt->execute()) {

            /*
             * Delete product image from folder
             * only if an image exists.
             */

            if (!empty($product['Image'])) {

                $image_path = __DIR__ . "/images/" . $product['Image'];

                if (file_exists($image_path)) {

                    unlink($image_path);
                }
            }

            $delete_stmt->close();

            header("Location: products.php?deleted=1");
            exit;

        } else {

            $error_message = "Unable to delete this product. It may be connected to an existing order.";
        }

        $delete_stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Delete Product | FashionFlair</title>

    <link rel="stylesheet" href="css/style.css">

</head>


<body class="delete-product-page">


    <!-- Background Overlay -->

    <div class="delete-overlay">


        <!-- Delete Card -->

        <div class="delete-card">


            <!-- Brand -->

            <div class="delete-brand">

                <span>FASHION</span>
                <strong>FLAIR</strong>

            </div>


            <div class="delete-line"></div>


            <!-- Warning Icon -->

            <div class="delete-icon-wrapper">

                <div class="delete-icon">
                    ⚠
                </div>

            </div>


            <!-- Heading -->

            <h1>Delete Product?</h1>


            <p class="delete-warning">

                Are you sure you want to delete this product?

            </p>


            <p class="delete-note">

                This action cannot be undone.

            </p>


            <!-- Product Information -->

            <div class="delete-product-info">


                <!-- Product Image -->

                <div class="delete-product-image">

                    <?php if (!empty($product['Image'])): ?>

                        <img
                            src="images/<?php echo htmlspecialchars($product['Image']); ?>"
                            alt="<?php echo htmlspecialchars($product['ProductName']); ?>"
                        >

                    <?php else: ?>

                        <div class="no-delete-image">
                            🛍️
                        </div>

                    <?php endif; ?>

                </div>


                <!-- Product Details -->

                <div class="delete-product-details">

                    <h2>
                        <?php echo htmlspecialchars($product['ProductName']); ?>
                    </h2>

                    <p>
                        <strong>Brand:</strong>
                        <?php echo htmlspecialchars($product['Brand']); ?>
                    </p>

                    <p>
                        <strong>Category:</strong>
                        <?php echo htmlspecialchars($product['CategoryName']); ?>
                    </p>

                    <p>
                        <strong>Price:</strong>
                        Rs. <?php echo number_format($product['Price'], 2); ?>
                    </p>

                    <p>
                        <strong>Stock:</strong>
                        <?php echo $product['StockQuantity']; ?>
                    </p>

                </div>

            </div>


            <!-- Error -->

            <?php if (isset($error_message)): ?>

                <div class="delete-error">

                    ❌
                    <?php echo htmlspecialchars($error_message); ?>

                </div>

            <?php endif; ?>


            <!-- Buttons -->

            <form method="POST" class="delete-form">


                <button
                    type="submit"
                    name="confirm_delete"
                    class="confirm-delete-btn"
                >

                    🗑️ DELETE PRODUCT

                </button>


                <a
                    href="products.php"
                    class="cancel-delete-btn"
                >

                    ↩ CANCEL

                </a>


            </form>


            <!-- Footer -->

            <div class="delete-footer">

                <span>FASHIONFLAIR ADMIN PANEL</span>

                <p>
                    Manage your fashion products safely.
                </p>

            </div>


        </div>

    </div>


</body>

</html>