<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['UserID'])) {

    die("
        <h2 style='color:red;'>UserID Session Missing</h2>
        <p>Please login first.</p>
        <a href='login.php'>Go to Login</a>
    ");
}

if (!isset($_SESSION['Role'])) {

    die("
        <h2 style='color:red;'>Role Session Missing</h2>
        <p>Login page is not storing the user's Role.</p>
        <a href='login.php'>Go to Login</a>
    ");
}

if ($_SESSION['Role'] !== 'Admin') {

    die("
        <h2 style='color:red;'>Admin Access Required</h2>
        <p>Your current role is: <strong>" .
        htmlspecialchars($_SESSION['Role']) .
        "</strong></p>
        <p>Change your user Role to Admin in the database.</p>
    ");
}