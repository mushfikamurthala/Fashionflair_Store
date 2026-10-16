<?php

session_start();

require_once "config/db_connect.php";
require_once "includes/admin_check.php";


/* CHECK PRODUCT ID */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = (int) $_GET['id'];


/* GET PRODUCT DETAILS */

$stmt = $conn->prepare("
    SELECT
        ProductID,
        ProductName,
        CategoryID,
        Brand,
        Size,
        Color,
        Price,
        StockQuantity,
        Description,
        Image
    FROM products
    WHERE ProductID = ?
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


/* GET CATEGORIES */

$category_sql = "
    SELECT CategoryID, CategoryName
    FROM categories
    ORDER BY CategoryName ASC
";

$category_result = $conn->query($category_sql);


/* UPDATE PRODUCT */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $product_name = trim($_POST['product_name']);
    $category_id = (int) $_POST['category_id'];
    $brand = trim($_POST['brand']);
    $size = trim($_POST['size']);
    $color = trim($_POST['color']);
    $price = (float) $_POST['price'];
    $stock_quantity = (int) $_POST['stock_quantity'];
    $description = trim($_POST['description']);

    $errors = [];


    /* VALIDATION */

    if ($product_name === '') {
        $errors[] = "Product name is required.";
    }

    if ($category_id <= 0) {
        $errors[] = "Please select a category.";
    }

    if ($brand === '') {
        $errors[] = "Brand is required.";
    }

    if ($size === '') {
        $errors[] = "Size is required.";
    }

    if ($color === '') {
        $errors[] = "Color is required.";
    }

    if ($price <= 0) {
        $errors[] = "Price must be greater than 0.";
    }

    if ($stock_quantity < 0) {
        $errors[] = "Stock quantity cannot be negative.";
    }


    /* IMAGE UPLOAD */

    $new_image_name = $product['Image'];

    if (
        isset($_FILES['product_image']) &&
        $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['product_image']['error'] === UPLOAD_ERR_OK) {

            $image_name = $_FILES['product_image']['name'];
            $image_tmp = $_FILES['product_image']['tmp_name'];
            $image_size = $_FILES['product_image']['size'];

            $image_extension = strtolower(
                pathinfo($image_name, PATHINFO_EXTENSION)
            );

            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];


            /* CHECK EXTENSION */

            if (!in_array($image_extension, $allowed_extensions)) {

                $errors[] =
                    "Only JPG, JPEG, PNG and WEBP images are allowed.";

            }


            /* CHECK SIZE */

            if ($image_size > 5 * 1024 * 1024) {

                $errors[] =
                    "Image size must be less than 5MB.";

            }


            /* GENERATE UNIQUE IMAGE NAME */

            if (empty($errors)) {

                $new_image_name =
                    time() . "_" .
                    preg_replace(
                        "/[^a-zA-Z0-9._-]/",
                        "_",
                        $image_name
                    );

                $upload_directory = __DIR__ . "/images/";

                $upload_path =
                    $upload_directory . $new_image_name;


                if (!move_uploaded_file($image_tmp, $upload_path)) {

                    $errors[] =
                        "Failed to upload the new image.";

                    $new_image_name = $product['Image'];
                }

            }

        } else {

            $errors[] = "Error uploading image.";
        }
    }


    /* UPDATE DATABASE */

    if (empty($errors)) {

        $update_stmt = $conn->prepare("
            UPDATE products
            SET
                ProductName = ?,
                CategoryID = ?,
                Brand = ?,
                Size = ?,
                Color = ?,
                Price = ?,
                StockQuantity = ?,
                Description = ?,
                Image = ?
            WHERE ProductID = ?
        ");

        $update_stmt->bind_param(
            "sisssdissi",
            $product_name,
            $category_id,
            $brand,
            $size,
            $color,
            $price,
            $stock_quantity,
            $description,
            $new_image_name,
            $product_id
        );


        if ($update_stmt->execute()) {

            /*
             * Delete old image only when
             * a new image was successfully uploaded.
             */

            if (
                $new_image_name !== $product['Image'] &&
                !empty($product['Image'])
            ) {

                $old_image_path =
                    __DIR__ . "/images/" . $product['Image'];

                if (
                    file_exists($old_image_path) &&
                    is_file($old_image_path)
                ) {

                    unlink($old_image_path);
                }
            }


            $update_stmt->close();

            header(
                "Location: products.php?updated=1"
            );

            exit;

        } else {

            $error_message =
                "Product could not be updated. Please try again.";
        }

        $update_stmt->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Product | FashionFlair</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body class="edit-product-page">


<div class="edit-product-overlay">

    <div class="edit-product-card">


        <!-- BRAND -->

        <div class="edit-product-brand">

            <span>FASHION</span>
            <strong>FLAIR</strong>

        </div>


        <!-- DECORATIVE LINE -->

        <div class="edit-product-line"></div>


        <!-- HEADING -->

        <div class="edit-product-heading">

            <div class="edit-product-icon">
                ✏️
            </div>

            <div>

                <h1>
                    Edit Product
                </h1>

                <p>
                    Update your FashionFlair product information
                </p>

            </div>

        </div>


        <!-- ERROR MESSAGE -->

        <?php if (!empty($errors)): ?>

            <div class="edit-error">

                <strong>
                    ⚠ Please fix the following:
                </strong>

                <ul>

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?php echo htmlspecialchars($error); ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <?php if (isset($error_message)): ?>

            <div class="edit-error">

                ❌
                <?php
                echo htmlspecialchars($error_message);
                ?>

            </div>

        <?php endif; ?>


        <!-- FORM -->

        <form
            method="POST"
            enctype="multipart/form-data"
            class="edit-product-form"
        >


            <!-- PRODUCT NAME -->

            <div class="edit-form-group">

                <label>
                    Product Name
                </label>

                <input
                    type="text"
                    name="product_name"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['product_name']
                        ?? $product['ProductName']
                    );
                    ?>"
                    placeholder="Enter product name"
                    required
                >

            </div>


            <!-- CATEGORY -->

            <div class="edit-form-group">

                <label>
                    Category
                </label>

                <select
                    name="category_id"
                    required
                >

                    <option value="">
                        Select Category
                    </option>

                    <?php while (
                        $category = $category_result->fetch_assoc()
                    ): ?>

                        <option
                            value="<?php
                            echo $category['CategoryID'];
                            ?>"
                            <?php

                            $selected_category =
                                $_POST['category_id']
                                ?? $product['CategoryID'];

                            if (
                                $selected_category ==
                                $category['CategoryID']
                            ) {
                                echo "selected";
                            }

                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $category['CategoryName']
                            );
                            ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- BRAND -->

            <div class="edit-form-group">

                <label>
                    Brand
                </label>

                <input
                    type="text"
                    name="brand"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['brand']
                        ?? $product['Brand']
                    );
                    ?>"
                    placeholder="Enter brand name"
                    required
                >

            </div>


            <!-- SIZE -->

            <div class="edit-form-group">

                <label>
                    Size
                </label>

                <input
                    type="text"
                    name="size"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['size']
                        ?? $product['Size']
                    );
                    ?>"
                    placeholder="Example: S, M, L, XL, 40"
                    required
                >

            </div>


            <!-- COLOR -->

            <div class="edit-form-group">

                <label>
                    Color
                </label>

                <input
                    type="text"
                    name="color"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['color']
                        ?? $product['Color']
                    );
                    ?>"
                    placeholder="Example: Black, White, Blue"
                    required
                >

            </div>


            <!-- PRICE -->

            <div class="edit-form-group">

                <label>
                    Price (Rs.)
                </label>

                <input
                    type="number"
                    name="price"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['price']
                        ?? $product['Price']
                    );
                    ?>"
                    min="0.01"
                    step="0.01"
                    placeholder="Enter product price"
                    required
                >

            </div>


            <!-- STOCK -->

            <div class="edit-form-group">

                <label>
                    Stock Quantity
                </label>

                <input
                    type="number"
                    name="stock_quantity"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['stock_quantity']
                        ?? $product['StockQuantity']
                    );
                    ?>"
                    min="0"
                    placeholder="Enter stock quantity"
                    required
                >

            </div>


            <!-- DESCRIPTION -->

            <div class="edit-form-group full-width">

                <label>
                    Product Description
                </label>

                <textarea
                    name="description"
                    rows="5"
                    placeholder="Enter product description"
                ><?php
                echo htmlspecialchars(
                    $_POST['description']
                    ?? $product['Description']
                );
                ?></textarea>

            </div>


            <!-- CURRENT IMAGE -->

            <div class="current-image-section">

                <label>
                    Current Product Image
                </label>

                <div class="current-image-box">

                    <?php if (!empty($product['Image'])): ?>

                        <img
                            src="images/<?php
                            echo htmlspecialchars(
                                $product['Image']
                            );
                            ?>"
                            alt="Current Product Image"
                        >

                    <?php else: ?>

                        <div class="no-current-image">
                            🛍️
                            <span>
                                No Image
                            </span>
                        </div>

                    <?php endif; ?>

                </div>

                <small>
                    Upload a new image below if you want to
                    replace the current image.
                </small>

            </div>


            <!-- NEW IMAGE -->

            <div class="edit-form-group full-width">

                <label>
                    New Product Image
                </label>

                <input
                    type="file"
                    name="product_image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small>
                    Allowed: JPG, JPEG, PNG, WEBP |
                    Maximum size: 5MB
                </small>

            </div>


            <!-- BUTTONS -->

            <div class="edit-product-buttons">

                <button
                    type="submit"
                    class="update-product-btn"
                >
                    💾 UPDATE PRODUCT
                </button>


                <a
                    href="products.php"
                    class="cancel-edit-btn"
                >
                    ↩ CANCEL
                </a>

            </div>


        </form>


        <!-- FOOTER -->

        <div class="edit-product-footer">

            <span>
                FASHIONFLAIR ADMIN PANEL
            </span>

            <p>
                Keep your fashion collection updated.
            </p>

        </div>


    </div>

</div>


</body>

</html>