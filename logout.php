<?php

session_start();

/* Store user name before destroying session */
$logged_out_user = $_SESSION['FullName'] ?? 'FashionFlair User';

/* Clear session */
$_SESSION = array();

/* Destroy session */
session_destroy();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Logout | FashionFlair</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body class="logout-page">

    <!-- Background Overlay -->
    <div class="logout-overlay">

        <!-- Logout Card -->
        <div class="logout-card">

            <!-- Brand -->
            <div class="logout-brand">
                <span>FASHION</span><strong>FLAIR</strong>
            </div>

            <!-- Decorative Line -->
            <div class="logout-line"></div>

            <!-- Icon -->
            <div class="logout-icon-wrapper">

                <div class="logout-icon">
                    ✓
                </div>

            </div>

            <!-- Main Heading -->
            <h1>You're Successfully Logged Out</h1>

            <p class="logout-welcome">
                Thank you for visiting <strong>FashionFlair</strong>.
            </p>

            <p class="logout-description">
                Your account has been safely logged out.
                We hope to see you again soon.
            </p>

            <!-- Quote -->
            <div class="logout-quote">
                <span>“</span>
                Style is the way to say who you are
                without having to speak.
                <span>”</span>
            </div>

            <!-- Buttons -->
            <div class="logout-buttons">

                <a href="login.php" class="logout-login-btn">
                    <span>🔐</span>
                    LOGIN AGAIN
                </a>

                <a href="index.php" class="logout-home-btn">
                    <span>🏠</span>
                    GO TO HOME
                </a>

            </div>

            <!-- Footer text -->
            <div class="logout-footer">

                <span>FASHIONFLAIR</span>

                <p>
                    Discover your style. Express yourself.
                </p>

            </div>

        </div>

    </div>

</body>

</html>