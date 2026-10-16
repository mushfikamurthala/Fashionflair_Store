<?php

session_start();

require_once "config/db_connect.php";

$message = "";
$message_type = "";

if (isset($_POST['register'])) {

    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $phone    = trim($_POST['phone']);

    // Basic validation
    if (empty($fullname) || empty($email) || empty($password) || empty($phone)) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must contain at least 6 characters.";
        $message_type = "error";

    } else {

        // Check whether email already exists
        $check_sql = "SELECT UserID FROM users WHERE Email = ?";

        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            $message = "This email is already registered.";
            $message_type = "error";

        } else {

            // Secure password hashing
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert customer account
            $sql = "INSERT INTO users
                    (FullName, Email, Password, Phone, Role, Status)
                    VALUES (?, ?, ?, ?, 'Customer', 'Active')";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "ssss",
                $fullname,
                $email,
                $hashed_password,
                $phone
            );

            if ($stmt->execute()) {

                $message = "Registration successful! You can now login.";
                $message_type = "success";

            } else {

                $message = "Registration failed. Please try again.";
                $message_type = "error";
            }

            $stmt->close();
        }

        $check_stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Register | FashionFlair</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>

<div class="auth-container">

    <div class="auth-card">

        <div class="auth-logo">
            FashionFlair
        </div>

        <h1>Create Account</h1>

        <p class="auth-subtitle">
            Join FashionFlair and start shopping
        </p>

        <?php if (!empty($message)): ?>

            <div class="<?php echo $message_type; ?>-message">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form method="POST"
              action="register.php">

            <div class="form-group">

                <label for="fullname">
                    Full Name
                </label>

                <input
                    type="text"
                    id="fullname"
                    name="fullname"
                    placeholder="Enter your full name"
                    required
                >

            </div>


            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <div class="form-group">

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="text"
                    id="phone"
                    name="phone"
                    placeholder="Enter your phone number"
                    required
                >

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Create a password"
                    required
                >

            </div>


            <button
                type="submit"
                name="register"
                class="auth-btn">

                CREATE ACCOUNT

            </button>

        </form>


        <p class="auth-footer">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </p>


        <a href="index.php"
           class="back-home">

            ← Back to Home

        </a>

    </div>

</div>

</body>

</html>