<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FashionFlair | Fashion Store</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>


<header class="main-header">

    <div class="container navbar">


        <!-- LOGO -->

        <div class="logo">

            <a href="index.php">
                FashionFlair
            </a>

        </div>


        <!-- NAVIGATION -->

        <nav>

            <a href="index.php">
                🏠 Home
            </a>

            <a href="products.php">
                🛍️ Shop
            </a>

            

            <a href="index.php#about">
                ℹ️ About
            </a>

            <a href="index.php#contact">
                📞 Contact
            </a>

            <a href="cart.php">
                🛒 Cart
            </a>


            <?php if (isset($_SESSION['UserID'])): ?>


                <?php if (
                    isset($_SESSION['Role']) &&
                    $_SESSION['Role'] === 'Admin'
                ): ?>

                    <a href="dashboard.php">
                        📊 Dashboard
                    </a>

                <?php endif; ?>


                <a href="logout.php">
                    🚪 Logout
                </a>


            <?php else: ?>


                <a href="login.php">
                    🔐 Login
                </a>

                <a href="register.php" class="nav-register">
                    📝 Register
                </a>


            <?php endif; ?>


        </nav>

    </div>

</header>


<main>