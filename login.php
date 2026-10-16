<?php

session_start();

require_once "config/db_connect.php";

$message = "";
$message_type = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {

        $message = "Please enter your email and password.";
        $message_type = "error";

    } else {

        $sql = "SELECT UserID, FullName, Email, Password, Phone, Role, Status
                FROM users
                WHERE Email = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if ($user['Status'] !== 'Active') {

                $message = "Your account is inactive.";
                $message_type = "error";

            } elseif (password_verify($password, $user['Password'])) {

                $_SESSION['UserID'] = $user['UserID'];
                $_SESSION['FullName'] = $user['FullName'];
                $_SESSION['Email'] = $user['Email'];
                $_SESSION['Phone'] = $user['Phone'];
                $_SESSION['Role'] = $user['Role'];

                if ($user['Role'] === 'Admin') {

                    header("Location: dashboard.php");
                    exit;

                } else {

                    header("Location: products.php");
                    exit;
                }

            } else {

                $message = "Invalid email or password.";
                $message_type = "error";
            }

        } else {

            $message = "Invalid email or password.";
            $message_type = "error";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login | FashionFlair</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="login-page">

    <!-- LEFT SIDE -->

    <div class="login-image-section">

        <div class="login-overlay">

            <div class="login-image-content">

                <span>FASHIONFLAIR</span>

                <h1>
                    Style Builds<br>
                    Confidence
                </h1>

                <p>
                    Login to continue your
                    fashion journey with
                    FashionFlair.
                </p>

            </div>

        </div>

    </div>


    <!-- RIGHT SIDE -->

    <div class="login-form-section">

        <div class="login-card">

            <div class="login-logo">
                FASHIONFLAIR
            </div>

            <div class="login-line">
                <span>♡</span>
            </div>

            <h2>
                Welcome Back
            </h2>

            <p class="login-subtitle">
                Login to your FashionFlair account
            </p>


            <?php if (!empty($message)): ?>

                <div class="<?php echo $message_type; ?>-message">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <form method="POST" action="login.php">

                <!-- EMAIL -->

                <div class="login-input-group">

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


                <!-- PASSWORD -->

                <div class="login-input-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                </div>


                <!-- OPTIONS -->

                <div class="login-options">

                    <label>

                        <input
                            type="checkbox"
                            name="remember"
                        >

                        Remember me

                    </label>

                    <a href="#">
                        Forgot Password?
                    </a>

                </div>


                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    name="login"
                    class="login-submit-btn">

                    🔐 LOGIN

                </button>

            </form>


            <div class="login-divider">

                <span>OR</span>

            </div>


            <p class="create-account-text">

                Don't have an account?

                <a href="register.php">
                    Create Account
                </a>

            </p>


            <a href="index.php"
               class="login-home-btn">

                ← Back to Home

            </a>

        </div>

    </div>

</div>

</body>

</html>