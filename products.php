<?php
session_start();

/* ==============================
   ADD TO CART
============================== */

if (isset($_GET['add'])) {

    $product = $_GET['add'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product])) {
        $_SESSION['cart'][$product]++;
    } else {
        $_SESSION['cart'][$product] = 1;
    }

    header("Location: products.php?success=1");
    exit();
}


/* ==============================
   PRODUCT DATA
============================== */

$products = [

    [
        "name" => "Elegant Blouse",
        "category" => "Formal Wear",
        "price" => "6500",
        "image" => "blouse.png"
    ],

    [
        "name" => "Cotton T-Shirt",
        "category" => "Casual Wear",
        "price" => "4500",
        "image" => "cotton t-shirt.png"
    ],

    [
        "name" => "Linen T-Shirt",
        "category" => "Casual Wear",
        "price" => "5000",
        "image" => "linen t-shirt.png"
    ],

    [
        "name" => "Classic Belt",
        "category" => "Accessories",
        "price" => "3000",
        "image" => "belt.png"
    ],

    [
        "name" => "Denim Jeans",
        "category" => "Casual Wear",
        "price" => "7500",
        "image" => "denim.png"
    ],

    [
        "name" => "Elegant Handbag",
        "category" => "Accessories",
        "price" => "9500",
        "image" => "handbag.png"
    ],

    [
        "name" => "Fashion Shoes",
        "category" => "Footwear",
        "price" => "12000",
        "image" => "shoe.png"
    ],

    [
        "name" => "Stylish Sunglasses",
        "category" => "Accessories",
        "price" => "4000",
        "image" => "sunglass.png"
    ],

    [
        "name" => "Summer Dress",
        "category" => "Casual Wear",
        "price" => "8500",
        "image" => "summer dress.png"
    ],

    [
        "name" => "Formal Trousers",
        "category" => "Formal Wear",
        "price" => "7000",
        "image" => "trousers.png"
    ]

];


/* ==============================
   CART COUNT
============================== */

$cartCount = 0;

if (isset($_SESSION['cart'])) {

    foreach ($_SESSION['cart'] as $quantity) {

        $cartCount += $quantity;

    }

}
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Fashion Flair - Products</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            font-family: Arial, sans-serif;
            background: #faf6f7;
            color: #333;
        }


        /* =========================
           NAVBAR
        ========================= */

        .navbar {

            background: #7b2948;

            padding: 18px 50px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .logo {

            color: white;

            font-size: 28px;

            font-weight: bold;

        }


        .menu {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .menu a {

            color: white;

            text-decoration: none;

            margin-left: 15px;

            padding: 9px 12px;

            border-radius: 6px;

            transition: 0.3s;

        }


        .menu a:hover {

            background: rgba(255,255,255,0.15);

        }


        /* =========================
           PROFILE BUTTON
        ========================= */

        .profile-btn {

            background: transparent;

            color: white !important;

        }


        .profile-btn:hover {

            background: white !important;

            color: #7b2948 !important;

        }


        /* =========================
           CART BUTTON
        ========================= */

        .cart {

            background: white;

            color: #7b2948 !important;

            padding: 9px 15px !important;

            border-radius: 20px !important;

            margin-left: 15px !important;

        }


        .cart:hover {

            background: #f2e5ea !important;

            color: #7b2948 !important;

        }


        /* =========================
           HEADER
        ========================= */

        .header {

            text-align: center;

            padding: 50px 20px 30px;

        }


        .header h1 {

            color: #7b2948;

            font-size: 40px;

            margin-bottom: 10px;

        }


        .header p {

            color: #777;

        }


        /* =========================
           SUCCESS MESSAGE
        ========================= */

        .success {

            width: 90%;

            max-width: 1000px;

            margin: auto;

            background: #e8f7ed;

            color: #28743c;

            padding: 15px;

            text-align: center;

            border-radius: 8px;

            margin-bottom: 25px;

        }


        /* =========================
           PRODUCTS
        ========================= */

        .products {

            width: 90%;

            max-width: 1200px;

            margin: auto;

            display: grid;

            grid-template-columns: repeat(4, 1fr);

            gap: 25px;

            padding-bottom: 60px;

        }


        /* =========================
           CARD
        ========================= */

        .card {

            background: white;

            border-radius: 12px;

            overflow: hidden;

            box-shadow: 0 4px 15px rgba(0,0,0,0.10);

        }


        .card img {

            width: 100%;

            height: 260px;

            object-fit: cover;

            display: block;

        }


        .details {

            padding: 18px;

        }


        .details h2 {

            color: #7b2948;

            font-size: 20px;

            margin-bottom: 8px;

        }


        .category {

            color: #888;

            font-size: 14px;

            margin-bottom: 10px;

        }


        .price {

            color: #7b2948;

            font-size: 20px;

            font-weight: bold;

            margin: 12px 0;

        }


        /* =========================
           ADD TO CART
        ========================= */

        .add-button {

            display: block;

            width: 100%;

            padding: 12px;

            background: #7b2948;

            color: white;

            text-align: center;

            text-decoration: none;

            border-radius: 6px;

            font-weight: bold;

        }


        .add-button:hover {

            background: #5d1e35;

        }


        /* =========================
           FOOTER
        ========================= */

        footer {

            background: #7b2948;

            color: white;

            text-align: center;

            padding: 25px;

        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1000px) {

            .products {

                grid-template-columns: repeat(3, 1fr);

            }

        }


        @media (max-width: 750px) {

            .products {

                grid-template-columns: repeat(2, 1fr);

            }

            .navbar {

                padding: 15px 20px;

            }

            .menu a {

                margin-left: 5px;

                padding: 7px;

            }

        }


        @media (max-width: 500px) {

            .products {

                grid-template-columns: 1fr;

            }

            .menu {

                display: none;

            }

        }

    </style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<div class="navbar">

    <div class="logo">

        Fashion Flair

    </div>


    <div class="menu">

        <a href="index.php">
            Home
        </a>


        <a href="products.php">
            Products
        </a>


        <a href="category.php">
            Category
        </a>


        <!-- PROFILE BUTTON -->

        <a href="profile.php" class="profile-btn">
            Profile
        </a>


        <!-- CART BUTTON -->

        <a href="cart_item.php" class="cart">

            🛒 Cart

            (<?php echo $cartCount; ?>)

        </a>

    </div>

</div>



<!-- =========================
     HEADER
========================= -->

<div class="header">

    <h1>
        Our Products
    </h1>

    <p>
        Discover the latest fashion at Fashion Flair Boutique
    </p>

</div>



<!-- =========================
     SUCCESS MESSAGE
========================= -->

<?php

if (isset($_GET['success'])) {

?>

    <div class="success">

        ✓ Product added to your cart successfully!

    </div>

<?php

}

?>



<!-- =========================
     PRODUCTS
========================= -->

<div class="products">


<?php

foreach ($products as $product) {

?>


    <div class="card">


        <!-- IMAGE -->

        <img
            src="images/<?php echo htmlspecialchars($product['image']); ?>"
            alt="<?php echo htmlspecialchars($product['name']); ?>"
        >


        <!-- DETAILS -->

        <div class="details">


            <h2>

                <?php

                echo htmlspecialchars($product['name']);

                ?>

            </h2>


            <p class="category">

                <?php

                echo htmlspecialchars($product['category']);

                ?>

            </p>


            <p>

                Fashion Flair

            </p>


            <p class="price">

                Rs.

                <?php

                echo number_format(
                    $product['price'],
                    2
                );

                ?>

            </p>


            <!-- ADD TO CART -->

            <a
                href="products.php?add=<?php echo urlencode($product['name']); ?>"
                class="add-button"
            >

                🛒 Add to Cart

            </a>


        </div>


    </div>


<?php

}

?>


</div>



<!-- =========================
     FOOTER
========================= -->

<footer>

    <h3>
        Fashion Flair Boutique
    </h3>

    <br>

    <p>
        Stylish Fashion | Quality Products
    </p>

    <br>

    <p>
        © 2026 Fashion Flair Boutique
    </p>

</footer>



</body>

</html>