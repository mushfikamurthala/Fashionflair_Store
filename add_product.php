<?php

$conn = mysqli_connect("localhost", "root", "");

if (!$conn) {
    die("MySQL connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS fashionflair_store");

mysqli_select_db($conn, "fashionflair_store");

mysqli_query($conn, "
CREATE TABLE IF NOT EXISTS category (
    category_id INT NOT NULL AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    PRIMARY KEY (category_id)
)
");

mysqli_query($conn, "
CREATE TABLE IF NOT EXISTS products (
    product_id INT NOT NULL AUTO_INCREMENT,
    ProductName VARCHAR(100) NOT NULL,
    CategoryID INT NOT NULL,
    Brand VARCHAR(100) NOT NULL,
    Size VARCHAR(50) NOT NULL,
    Color VARCHAR(50) NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    StockQuantity INT NOT NULL,
    Description TEXT,
    Image VARCHAR(255),
    PRIMARY KEY (product_id)
)
");

$check = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM category"
);

$row = mysqli_fetch_assoc($check);

if ($row["total"] == 0) {

    mysqli_query(
        $conn,
        "INSERT INTO category (category_name) VALUES
        ('Casual Wear'),
        ('Formal Wear'),
        ('Sportswear'),
        ('Footwear'),
        ('Accessories')"
    );
}

$message = "";

if (isset($_POST["add_product"])) {

    $product_name = mysqli_real_escape_string(
        $conn,
        $_POST["product_name"]
    );

    $category_id = intval(
        $_POST["category_id"]
    );

    $brand = mysqli_real_escape_string(
        $conn,
        $_POST["brand"]
    );

    $size = mysqli_real_escape_string(
        $conn,
        $_POST["size"]
    );

    $color = mysqli_real_escape_string(
        $conn,
        $_POST["color"]
    );

    $price = floatval(
        $_POST["price"]
    );

    $stock = intval(
        $_POST["stock_quantity"]
    );

    $description = mysqli_real_escape_string(
        $conn,
        $_POST["description"]
    );

    $image_name = "";

    if (
        isset($_FILES["product_image"]) &&
        $_FILES["product_image"]["name"] != ""
    ) {

        if (!is_dir("images")) {
            mkdir("images", 0777, true);
        }

        $image_name = basename(
            $_FILES["product_image"]["name"]
        );

        $image_path = "images/" . $image_name;

        move_uploaded_file(
            $_FILES["product_image"]["tmp_name"],
            $image_path
        );
    }

    $sql = "
    INSERT INTO products
    (
        ProductName,
        CategoryID,
        Brand,
        Size,
        Color,
        Price,
        StockQuantity,
        Description,
        Image
    )
    VALUES
    (
        '$product_name',
        '$category_id',
        '$brand',
        '$size',
        '$color',
        '$price',
        '$stock',
        '$description',
        '$image_name'
    )
    ";

    if (mysqli_query($conn, $sql)) {

        $message = "Product added successfully!";

    } else {

        $message =
            "Error: " . mysqli_error($conn);
    }
}

$categories = mysqli_query(
    $conn,
    "SELECT * FROM category ORDER BY category_id"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Product - Fashion Flair Boutique</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    min-height: 100vh;

    background-image:
        linear-gradient(
            rgba(0,0,0,0.55),
            rgba(0,0,0,0.55)
        ),
        url("images/add-product-bg.jpg");

    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}

.navbar {
    background: rgba(0,0,0,0.9);
    padding: 18px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    color: white;
    font-size: 23px;
    font-weight: bold;
}

.navbar a {
    color: white;
    text-decoration: none;
    margin-left: 18px;
}

.navbar a:hover {
    color: #ffb6c1;
}

.container {
    width: 90%;
    max-width: 750px;
    margin: 45px auto;
    background: rgba(255,255,255,0.97);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.4);
}

h1 {
    text-align: center;
    color: #7a2850;
    margin-bottom: 25px;
}

.message {
    padding: 12px;
    background: #d4edda;
    color: #155724;
    text-align: center;
    border-radius: 7px;
    margin-bottom: 20px;
    font-weight: bold;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
    color: #444;
}

input,
select,
textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 7px;
    font-size: 15px;
}

textarea {
    height: 100px;
    resize: vertical;
}

button {
    width: 100%;
    padding: 14px;
    background: #c2185b;
    color: white;
    border: none;
    border-radius: 7px;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #8e1745;
}

.back {
    display: block;
    text-align: center;
    margin-top: 15px;
    padding: 12px;
    background: #555;
    color: white;
    text-decoration: none;
    border-radius: 7px;
}

@media (max-width: 600px) {

    .navbar {
        flex-direction: column;
        gap: 15px;
    }

    .navbar a {
        margin: 5px;
    }

    .container {
        width: 94%;
        padding: 20px;
    }
}

</style>

</head>

<body>

<div class="navbar">

    <div class="logo">
        👗 Fashion Flair Boutique
    </div>

    <div>

        <a href="index.php">Home</a>

        <a href="products.php">Products</a>

        <a href="category.php">Categories</a>

        <a href="customers.php">Customers</a>

    </div>

</div>


<div class="container">

    <h1>➕ Add New Product</h1>

    <?php

    if ($message != "") {

        echo "<div class='message'>";
        echo htmlspecialchars($message);
        echo "</div>";

    }

    ?>


    <form
        method="POST"
        enctype="multipart/form-data"
    >

        <div class="form-group">

            <label>Product Name</label>

            <input
                type="text"
                name="product_name"
                placeholder="Example: Summer Dress"
                required
            >

        </div>


        <div class="form-group">

            <label>Category</label>

            <select
                name="category_id"
                required
            >

                <option value="">
                    Select Category
                </option>

                <?php

                while ($category = mysqli_fetch_assoc($categories)) {

                    echo "<option value='" .
                         $category["category_id"] .
                         "'>" .
                         htmlspecialchars(
                             $category["category_name"]
                         ) .
                         "</option>";
                }

                ?>

            </select>

        </div>


        <div class="form-group">

            <label>Brand</label>

            <input
                type="text"
                name="brand"
                placeholder="Example: Zara"
                required
            >

        </div>


        <div class="form-group">

            <label>Size</label>

            <input
                type="text"
                name="size"
                placeholder="Example: M / L / XL / 42"
                required
            >

        </div>


        <div class="form-group">

            <label>Color</label>

            <input
                type="text"
                name="color"
                placeholder="Example: Black"
                required
            >

        </div>


        <div class="form-group">

            <label>Price (LKR)</label>

            <input
                type="number"
                name="price"
                placeholder="Example: 8500"
                min="0"
                step="0.01"
                required
            >

        </div>


        <div class="form-group">

            <label>Stock Quantity</label>

            <input
                type="number"
                name="stock_quantity"
                placeholder="Example: 10"
                min="0"
                required
            >

        </div>


        <div class="form-group">

            <label>Description</label>

            <textarea
                name="description"
                placeholder="Enter product description"
            ></textarea>

        </div>


        <div class="form-group">

            <label>Product Image</label>

            <input
                type="file"
                name="product_image"
                accept="image/*"
            >

        </div>


        <button
            type="submit"
            name="add_product"
        >

            ➕ Add Product

        </button>

    </form>


    <a
        href="products.php"
        class="back"
    >

        ← Back to Products

    </a>

</div>

</body>

</html>
```