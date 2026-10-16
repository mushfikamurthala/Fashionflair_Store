<?php

session_start();

require_once "config/db_connect.php";
require_once "includes/auth_check.php";

$user_id = $_SESSION['UserID'];

$message = "";
$error = "";


/* =========================================
   UPDATE PROFILE
========================================= */

if (isset($_POST['update_profile'])) {

    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);

    if (empty($full_name) || empty($phone)) {

        $error = "Please fill in all required fields.";

    } else {

        $sql = "UPDATE users
                SET FullName = ?, Phone = ?
                WHERE UserID = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssi",
            $full_name,
            $phone,
            $user_id
        );

        if ($stmt->execute()) {

            $_SESSION['FullName'] = $full_name;
            $_SESSION['Phone'] = $phone;

            $message = "Profile updated successfully.";

        } else {

            $error = "Unable to update profile.";
        }
    }
}


/* =========================================
   CHANGE PASSWORD
========================================= */

if (isset($_POST['change_password'])) {

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (
        empty($current_password) ||
        empty($new_password) ||
        empty($confirm_password)
    ) {

        $error = "Please fill in all password fields.";

    } elseif ($new_password !== $confirm_password) {

        $error = "New passwords do not match.";

    } elseif (strlen($new_password) < 8) {

        $error = "New password must contain at least 8 characters.";

    } else {

        /* Get current password */

        $sql = "SELECT Password
                FROM users
                WHERE UserID = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("i", $user_id);

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();


        if (
            $user &&
            password_verify(
                $current_password,
                $user['Password']
            )
        ) {

            $hashed_password = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );


            $update_sql = "UPDATE users
                           SET Password = ?
                           WHERE UserID = ?";

            $update_stmt = $conn->prepare($update_sql);

            $update_stmt->bind_param(
                "si",
                $hashed_password,
                $user_id
            );


            if ($update_stmt->execute()) {

                $message = "Password changed successfully.";

            } else {

                $error = "Unable to change password.";
            }

        } else {

            $error = "Current password is incorrect.";
        }
    }
}


/* =========================================
   GET USER DETAILS
========================================= */

$sql = "SELECT
            UserID,
            FullName,
            Email,
            Phone,
            Role,
            Status,
            CreatedAt
        FROM users
        WHERE UserID = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

?>

<?php require_once "includes/header.php"; ?>


<style>

/* =========================================
   PROFILE PAGE
========================================= */

.profile-page {
    min-height: 650px;

    padding: 55px 20px 70px;

    background:
        linear-gradient(
            rgba(255,255,255,0.90),
            rgba(255,245,249,0.94)
        );

    font-family: Arial, sans-serif;
}


/* =========================================
   CONTAINER
========================================= */

.profile-page .container {
    width: 92%;
    max-width: 1150px;
    margin: auto;
}


/* =========================================
   HEADING
========================================= */

.profile-heading {
    text-align: center;
    margin-bottom: 30px;
}

.profile-heading span {
    display: block;

    font-size: 13px;

    font-weight: bold;

    letter-spacing: 4px;

    color: #b24f79;

    margin-bottom: 8px;
}

.profile-heading h1 {
    font-size: 34px;

    color: #222;

    margin: 0 0 8px;
}

.profile-heading p {
    color: #777;

    font-size: 15px;
}


/* =========================================
   SUCCESS / ERROR
========================================= */

.profile-success,
.profile-error {
    width: 100%;

    max-width: 800px;

    margin: 0 auto 22px;

    padding: 14px 18px;

    border-radius: 10px;

    font-size: 14px;

    text-align: center;
}

.profile-success {
    background: #e9f8ef;

    color: #217a45;

    border: 1px solid #bfe7ce;
}

.profile-error {
    background: #fff0f0;

    color: #b52e3c;

    border: 1px solid #f1c0c5;
}


/* =========================================
   TWO CARDS
========================================= */

.profile-grid {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 28px;

    align-items: stretch;
}


/* =========================================
   PROFILE CARD
========================================= */

.profile-card {
    background: #ffffff;

    padding: 32px;

    border-radius: 18px;

    border: 1px solid #f0dce4;

    box-shadow:
        0 8px 30px rgba(100, 40, 65, 0.09);

    transition: 0.3s;
}

.profile-card:hover {
    transform: translateY(-4px);

    box-shadow:
        0 14px 35px rgba(100, 40, 65, 0.14);
}


/* =========================================
   ICON
========================================= */

.profile-icon {
    width: 62px;
    height: 62px;

    border-radius: 50%;

    background: linear-gradient(
        135deg,
        #b84e7c,
        #e69bb7
    );

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 28px;

    margin-bottom: 18px;

    box-shadow:
        0 7px 18px rgba(184,78,124,0.22);
}


/* =========================================
   CARD TITLE
========================================= */

.profile-card h2 {
    font-size: 23px;

    color: #222;

    margin-bottom: 7px;
}

.profile-description {
    color: #888;

    font-size: 13px;

    margin-bottom: 25px;

    line-height: 1.5;
}


/* =========================================
   FORM GROUP
========================================= */

.profile-form-group {
    margin-bottom: 18px;
}

.profile-form-group label {
    display: block;

    font-size: 13px;

    font-weight: bold;

    color: #444;

    margin-bottom: 7px;
}

.profile-form-group input {
    width: 100%;

    padding: 12px 14px;

    border: 1px solid #ddd;

    border-radius: 9px;

    font-size: 14px;

    outline: none;

    transition: 0.3s;

    background: #fff;
}

.profile-form-group input:focus {
    border-color: #c45c87;

    box-shadow:
        0 0 0 3px rgba(196,92,135,0.10);
}

.profile-form-group input[readonly] {
    background: #f7f7f7;

    color: #777;

    cursor: not-allowed;
}

.profile-form-group small {
    display: block;

    margin-top: 5px;

    font-size: 11px;

    color: #999;
}


/* =========================================
   UPDATE PROFILE BUTTON
========================================= */

.profile-update-btn,
.password-btn {

    width: 100%;

    border: none;

    padding: 13px 18px;

    border-radius: 9px;

    font-size: 13px;

    font-weight: bold;

    cursor: pointer;

    color: white;

    transition: 0.3s;

    margin-top: 5px;
}

.profile-update-btn {

    background: linear-gradient(
        135deg,
        #b84e7c,
        #d9789d
    );
}

.password-btn {

    background: linear-gradient(
        135deg,
        #343434,
        #111111
    );
}

.profile-update-btn:hover,
.password-btn:hover {

    transform: translateY(-2px);

    box-shadow:
        0 7px 18px rgba(0,0,0,0.18);
}


/* =========================================
   ACCOUNT INFORMATION
========================================= */

.account-info {

    margin-top: 28px;

    padding-top: 22px;

    border-top: 1px solid #eee;
}

.account-info h3 {

    font-size: 17px;

    color: #333;

    margin-bottom: 15px;
}

.account-info p {

    font-size: 13px;

    color: #666;

    margin-bottom: 8px;
}

.account-info strong {

    color: #333;
}


/* =========================================
   PROFILE ACTIONS
========================================= */

.profile-actions {

    width: 100%;

    margin-top: 30px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;
}


/* LEFT SIDE */

.action-left {

    display: flex;

    align-items: center;

    gap: 12px;
}


/* RIGHT SIDE */

.action-right {

    display: flex;

    align-items: center;

    gap: 12px;
}


/* =========================================
   ACTION BUTTONS
========================================= */

.profile-action-btn,
.profile-logout-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 165px;

    padding: 13px 20px;

    border-radius: 10px;

    text-decoration: none;

    font-size: 13px;

    font-weight: bold;

    transition: all 0.3s ease;

    box-shadow:
        0 5px 15px rgba(0,0,0,0.08);
}


/* MY ORDERS + SHOPPING */

.profile-action-btn {

    background: #ffffff;

    color: #ad4774;

    border: 1px solid #e2bdcc;
}

.profile-action-btn:hover {

    background: #b84e7c;

    color: white;

    transform: translateY(-3px);

    box-shadow:
        0 8px 20px rgba(184,78,124,0.25);
}


/* LOGOUT */

.profile-logout-btn {

    background: #222;

    color: white;

    border: 1px solid #222;
}

.profile-logout-btn:hover {

    background: #c84d5b;

    border-color: #c84d5b;

    color: white;

    transform: translateY(-3px);

    box-shadow:
        0 8px 20px rgba(200,77,91,0.25);
}


/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 850px) {

    .profile-grid {

        grid-template-columns: 1fr;
    }

    .profile-card {

        padding: 25px;
    }
}


@media (max-width: 650px) {

    .profile-page {

        padding: 35px 12px 50px;
    }

    .profile-page .container {

        width: 96%;
    }

    .profile-heading h1 {

        font-size: 28px;
    }

    .profile-actions {

        flex-direction: column;

        align-items: stretch;
    }

    .action-left,
    .action-right {

        width: 100%;

        display: flex;

        flex-direction: column;
    }

    .profile-action-btn,
    .profile-logout-btn {

        width: 100%;

        min-width: 0;
    }
}

</style>


<!-- =========================================
     PROFILE PAGE
========================================= -->

<section class="profile-page">

    <div class="container">


        <!-- PAGE HEADING -->

        <div class="profile-heading">

            <span>FASHIONFLAIR</span>

            <h1>👤 My Profile</h1>

            <p>
                Manage your FashionFlair account
            </p>

        </div>


        <!-- =====================================
             SUCCESS MESSAGE
        ====================================== -->

        <?php if (!empty($message)): ?>

            <div class="profile-success">

                ✅
                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <!-- =====================================
             ERROR MESSAGE
        ====================================== -->

        <?php if (!empty($error)): ?>

            <div class="profile-error">

                ❌
                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <!-- =====================================
             PROFILE CARDS
        ====================================== -->

        <div class="profile-grid">


            <!-- =================================
                 PERSONAL INFORMATION
            ================================= -->

            <div class="profile-card">

                <div class="profile-icon">
                    👤
                </div>

                <h2>
                    Personal Information
                </h2>

                <p class="profile-description">
                    Update your personal account information.
                </p>


                <form method="POST" action="profile.php">


                    <!-- FULL NAME -->

                    <div class="profile-form-group">

                        <label for="full_name">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            value="<?php echo htmlspecialchars($user['FullName']); ?>"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="profile-form-group">

                        <label for="email">
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            value="<?php echo htmlspecialchars($user['Email']); ?>"
                            readonly
                        >

                        <small>
                            Email address cannot be changed.
                        </small>

                    </div>


                    <!-- PHONE -->

                    <div class="profile-form-group">

                        <label for="phone">
                            Phone Number
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="<?php echo htmlspecialchars($user['Phone']); ?>"
                            required
                        >

                    </div>


                    <!-- ACCOUNT TYPE -->

                    <div class="profile-form-group">

                        <label>
                            Account Type
                        </label>

                        <input
                            type="text"
                            value="<?php echo htmlspecialchars($user['Role']); ?>"
                            readonly
                        >

                    </div>


                    <!-- ACCOUNT STATUS -->

                    <div class="profile-form-group">

                        <label>
                            Account Status
                        </label>

                        <input
                            type="text"
                            value="<?php echo htmlspecialchars($user['Status']); ?>"
                            readonly
                        >

                    </div>


                    <!-- UPDATE BUTTON -->

                    <button
                        type="submit"
                        name="update_profile"
                        class="profile-update-btn"
                    >
                        💾 UPDATE PROFILE
                    </button>


                </form>

            </div>



            <!-- =================================
                 CHANGE PASSWORD
            ================================= -->

            <div class="profile-card">

                <div class="profile-icon">
                    🔐
                </div>

                <h2>
                    Change Password
                </h2>

                <p class="profile-description">
                    Keep your account secure by using a strong password.
                </p>


                <form method="POST" action="profile.php">


                    <!-- CURRENT PASSWORD -->

                    <div class="profile-form-group">

                        <label for="current_password">
                            Current Password
                        </label>

                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            required
                        >

                    </div>


                    <!-- NEW PASSWORD -->

                    <div class="profile-form-group">

                        <label for="new_password">
                            New Password
                        </label>

                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            minlength="8"
                            required
                        >

                        <small>
                            Minimum 8 characters.
                        </small>

                    </div>


                    <!-- CONFIRM PASSWORD -->

                    <div class="profile-form-group">

                        <label for="confirm_password">
                            Confirm New Password
                        </label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            minlength="8"
                            required
                        >

                    </div>


                    <!-- CHANGE PASSWORD BUTTON -->

                    <button
                        type="submit"
                        name="change_password"
                        class="password-btn"
                    >
                        🔐 CHANGE PASSWORD
                    </button>


                </form>


                <!-- =================================
                     ACCOUNT INFORMATION
                ================================= -->

                <div class="account-info">

                    <h3>
                        Account Information
                    </h3>

                    <p>
                        <strong>User ID:</strong>
                        <?php echo htmlspecialchars($user['UserID']); ?>
                    </p>

                    <p>
                        <strong>Member Since:</strong>

                        <?php

                        echo date(
                            "d M Y",
                            strtotime($user['CreatedAt'])
                        );

                        ?>

                    </p>

                </div>

            </div>

        </div>


        <!-- =====================================
             PROFILE ACTION BUTTONS
        ====================================== -->

        <div class="profile-actions">


            <!-- LEFT SIDE -->

            <div class="action-left">

                <a
                    href="order_history.php"
                    class="profile-action-btn"
                >
                    📦 MY ORDERS
                </a>

            </div>


            <!-- RIGHT SIDE -->

            <div class="action-right">

                <a
                    href="products.php"
                    class="profile-action-btn"
                >
                    🛍️ CONTINUE SHOPPING
                </a>


                <a
                    href="logout.php"
                    class="profile-logout-btn"
                >
                    🚪 LOGOUT
                </a>

            </div>


        </div>


    </div>

</section>


<?php require_once "includes/footer.php"; ?>