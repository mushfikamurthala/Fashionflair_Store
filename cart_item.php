<?php

session_start();

/* Add product to cart */

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
}


/* Remove product */

if (isset($_GET['remove'])) {

    $product = $_GET['remove'];

    if (isset($_SESSION['cart'][$product])) {

        unset($_SESSION['cart'][$product]);

    }
}


/* Clear cart */

if (isset($_GET['clear'])) {

    $_SESSION['cart'] = [];

}

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Fashion Flair - Shopping Cart</title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {
            font-family: Arial, sans-serif;
            background: #fdf7f8;
            color: #333;
        }


        /* NAVBAR */

        .navbar {
            background: #7b2948;
            padding: 20px 50px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .logo {
            color: white;
            font-size: 28px;
            font-weight: bold;
        }


        .menu a {
            color: white;
            text-decoration: none;
            margin-left: 25px;
        }


        /* PAGE */

        .container {
            width: 90%;
            max-width: 900px;
            margin: 50px auto;
        }


        h1 {
            text-align: center;
            color: #7b2948;
            margin-bottom: 30px;
        }


        /* CART */

        .cart-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }


        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;

            padding: 18px 5px;

            border-bottom: 1px solid #eee;
        }


        .product-name {
            font-size: 18px;
            color: #7b2948;
        }


        .quantity {
            color: #777;
        }


        .remove {
            background: #7b2948;
            color: white;

            text-decoration: none;

            padding: 8px 15px;

            border-radius: 5px;
        }


        .remove:hover {
            background: #5d1e35;
        }


        .empty {
            text-align: center;
            padding: 40px;
            color: #777;
        }


        .buttons {
            margin-top: 25px;

            display: flex;
            justify-content: space-between;
        }


        .button {
            background: #7b2948;
            color: white;

            padding: 12px 20px;

            text-decoration: none;

            border-radius: 6px;
        }


        .button:hover {
            background: #5d1e35;
        }


        footer {
            margin-top: 100px;
            background: #7b2948;
            color: white;
            text-align: center;
            padding: 25px;
        }

    </style>

</head>


<body>


<!-- NAVBAR -->

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
            Categories
        </a>

        <a href="cart_item.php">
            Cart
        </a>

    </div>

</div>



<!-- CART -->

<div class="container">

    <h1>
        Shopping Cart
    </h1>


    <div class="cart-box">


        <?php if (empty($_SESSION['cart'])): ?>


            <div class="empty">

                <h2>
                    Your cart is empty
                </h2>

                <br>

                <a
                    href="products.php"
                    class="button"
                >
                    Continue Shopping
                </a>

            </div>


        <?php else: ?>


            <?php foreach ($_SESSION['cart'] as $product => $quantity): ?>


                <div class="cart-item">


                    <div>

                        <div class="product-name">

                            <?php echo htmlspecialchars($product); ?>

                        </div>


                        <div class="quantity">

                            Quantity:
                            <?php echo $quantity; ?>

                        </div>

                    </div>


                    <a
                        href="cart_item.php?remove=<?php echo urlencode($product); ?>"
                        class="remove"
                    >
                        Remove
                    </a>


                </div>


            <?php endforeach; ?>


            <div class="buttons">


                <a
                    href="products.php"
                    class="button"
                >
                    Continue Shopping
                </a>


                <a
                    href="cart_item.php?clear=1"
                    class="button"
                >
                    Clear Cart
                </a>


            </div>


        <?php endif; ?>


    </div>

</div>



<footer>

    <h3>
        Fashion Flair Boutique
    </h3>

    <br>

    <p>
        © 2026 Fashion Flair Boutique
    </p>

</footer>


</body>

</html>
```