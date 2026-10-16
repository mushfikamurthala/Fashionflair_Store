<?php

// Database connection
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "fashionflair_store"
);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


// Get search value
$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}


// Search products
if ($search != "") {

    $search_safe = mysqli_real_escape_string(
        $conn,
        $search
    );

    $sql = "
        SELECT 
            p.*,
            c.category_name
        FROM products p
        LEFT JOIN category c
            ON p.CategoryID = c.category_id
        WHERE
            p.ProductName LIKE '%$search_safe%'
            OR p.Brand LIKE '%$search_safe%'
            OR p.Color LIKE '%$search_safe%'
            OR p.Size LIKE '%$search_safe%'
            OR c.category_name LIKE '%$search_safe%'
        ORDER BY p.product_id DESC
    ";

} else {

    $sql = "
        SELECT 
            p.*,
            c.category_name
        FROM products p
        LEFT JOIN category c
            ON p.CategoryID = c.category_id
        ORDER BY p.product_id DESC
    ";
}


$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Search Products - Fashion Flair Boutique</title>


<style>

/* Page */
* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    min-height: 100vh;

    background-image:
        linear-gradient(
            rgba(0,0,0,0.60),
            rgba(0,0,0,0.60)
        ),
        url("images/search-bg.jpg");

    background-size: cover;

    background-position: center;

    background-attachment: fixed;
}


/* Navigation bar */
.navbar {

    background: rgba(0,0,0,0.90);

    padding: 18px 30px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    flex-wrap: wrap;
}


.logo {

    color: white;

    font-size: 23px;

    font-weight: bold;
}


.navbar-links {

    display: flex;

    gap: 20px;

    flex-wrap: wrap;
}


.navbar a {

    color: white;

    text-decoration: none;

    font-size: 15px;

}


.navbar a:hover {

    color: #ffb6c1;

}


/* Main container */
.container {

    width: 92%;

    max-width: 1200px;

    margin: 40px auto;

    background: rgba(255,255,255,0.96);

    padding: 30px;

    border-radius: 15px;

    box-shadow:
        0 8px 30px rgba(0,0,0,0.45);
}


/* Heading */
h1 {

    text-align: center;

    color: #7a2850;

    margin-top: 0;

    margin-bottom: 25px;

}


/* Search box */
.search-box {

    display: flex;

    gap: 10px;

    margin-bottom: 30px;
}


.search-box input {

    flex: 1;

    padding: 14px;

    border: 1px solid #ccc;

    border-radius: 8px;

    font-size: 16px;

}


.search-box button {

    padding: 14px 25px;

    background: #c2185b;

    color: white;

    border: none;

    border-radius: 8px;

    font-size: 16px;

    cursor: pointer;

}


.search-box button:hover {

    background: #8e1745;

}


/* Search information */
.search-info {

    text-align: center;

    margin-bottom: 25px;

    color: #555;

    font-size: 17px;

}


/* Product grid */
.product-grid {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(220px, 1fr)
        );

    gap: 25px;

}


/* Product card */
.product-card {

    background: white;

    border-radius: 12px;

    overflow: hidden;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.18);

    transition: 0.3s;

}


.product-card:hover {

    transform: translateY(-5px);

    box-shadow:
        0 8px 25px rgba(0,0,0,0.30);

}


/* Product image */
.product-card img {

    width: 100%;

    height: 220px;

    object-fit: cover;

    display: block;

}


/* Product details */
.product-info {

    padding: 18px;

}


.product-name {

    font-size: 20px;

    font-weight: bold;

    color: #7a2850;

    margin-bottom: 8px;

}


.product-info p {

    margin: 7px 0;

    color: #555;

}


.price {

    color: #c2185b;

    font-size: 20px;

    font-weight: bold;

    margin-top: 12px !important;

}


.stock {

    font-weight: bold;

}


/* No products */
.no-products {

    text-align: center;

    padding: 40px;

    font-size: 20px;

    color: #777;

}


/* Buttons */
.button-area {

    display: flex;

    gap: 8px;

    margin-top: 15px;

}


.cart-button {

    flex: 1;

    padding: 10px;

    background: #c2185b;

    color: white;

    text-decoration: none;

    text-align: center;

    border-radius: 6px;

    font-weight: bold;

}


.cart-button:hover {

    background: #8e1745;

}


.back-button {

    display: block;

    width: 220px;

    margin: 30px auto 0;

    padding: 13px;

    background: #555;

    color: white;

    text-decoration: none;

    text-align: center;

    border-radius: 7px;

}


.back-button:hover {

    background: #333;

}


/* Mobile */
@media (max-width: 600px) {

    .navbar {

        flex-direction: column;

        gap: 15px;

    }

    .search-box {

        flex-direction: column;

    }

    .search-box button {

        width: 100%;

    }

    .container {

        width: 95%;

        padding: 20px;

    }

}

</style>

</head>


<body>


<!-- Navigation -->

<div class="navbar">

    <div class="logo">
        👗 Fashion Flair Boutique
    </div>


    <div class="navbar-links">

        <a href="index.php">
            Home
        </a>

        <a href="products.php">
            Products
        </a>

        <a href="category.php">
            Categories
        </a>

        <a href="customers.php">
            Customers
        </a>

        <a href="add_product.php">
            Add Product
        </a>

        <a href="cart_item.php">
            🛒 Cart
        </a>

    </div>

</div>



<!-- Main content -->

<div class="container">


    <h1>
        🔍 Search Products
    </h1>


    <!-- Search form -->

    <form
        method="GET"
        action="search_products.php"
        class="search-box"
    >

        <input
            type="text"
            name="search"
            placeholder="Search by product name, brand, category, colour or size..."
            value="<?php echo htmlspecialchars($search); ?>"
        >


        <button type="submit">
            🔍 Search
        </button>

    </form>



    <?php if ($search != "") { ?>

        <div class="search-info">

            Search results for:

            <strong>
                <?php echo htmlspecialchars($search); ?>
            </strong>

        </div>

    <?php } else { ?>

        <div class="search-info">

            All Products

        </div>

    <?php } ?>



    <!-- Products -->

    <div class="product-grid">


        <?php

        if ($result && mysqli_num_rows($result) > 0) {


            while ($product = mysqli_fetch_assoc($result)) {


                $image = $product["Image"];


                // If database has no image
                if ($image == "") {

                    $image = "search-bg.jpg";

                }


                // Image path
                $image_path = "images/" . $image;

        ?>


                <div class="product-card">


                    <img
                        src="<?php echo htmlspecialchars($image_path); ?>"
                        alt="<?php echo htmlspecialchars($product["ProductName"]); ?>"
                        onerror="this.src='images/search-bg.jpg';"
                    >


                    <div class="product-info">


                        <div class="product-name">

                            <?php

                            echo htmlspecialchars(
                                $product["ProductName"]
                            );

                            ?>

                        </div>


                        <p>

                            <strong>Brand:</strong>

                            <?php

                            echo htmlspecialchars(
                                $product["Brand"]
                            );

                            ?>

                        </p>


                        <p>

                            <strong>Category:</strong>

                            <?php

                            echo htmlspecialchars(
                                $product["category_name"]
                            );

                            ?>

                        </p>


                        <p>

                            <strong>Size:</strong>

                            <?php

                            echo htmlspecialchars(
                                $product["Size"]
                            );

                            ?>

                        </p>


                        <p>

                            <strong>Colour:</strong>

                            <?php

                            echo htmlspecialchars(
                                $product["Color"]
                            );

                            ?>

                        </p>


                        <p class="price">

                            LKR

                            <?php

                            echo number_format(
                                $product["Price"],
                                2
                            );

                            ?>

                        </p>


                        <p class="stock">

                            <strong>Stock:</strong>

                            <?php

                            echo $product["StockQuantity"];

                            ?>

                        </p>


                        <div class="button-area">


                            <a
                                href="cart_item.php?add=<?php echo $product["product_id"]; ?>"
                                class="cart-button"
                            >

                                🛒 Add to Cart

                            </a>


                        </div>


                    </div>

                </div>


        <?php

            }

        } else {

        ?>

            <div class="no-products">

                ❌ No products found.

                <br><br>

                Try another product name, brand or category.

            </div>

        <?php

        }

        ?>


    </div>



    <a
        href="products.php"
        class="back-button"
    >

        ← Back to Products

    </a>


</div>


</body>

</html>

<?php

mysqli_close($conn);

?>
