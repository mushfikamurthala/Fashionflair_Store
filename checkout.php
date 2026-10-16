<?php
session_start();
require_once "config/db_connect.php";

/* =========================
   CHECK CART
========================= */

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}


/* =========================
   GET CART PRODUCTS
========================= */

$cart = $_SESSION['cart'];

$products = [];
$subtotal = 0;

foreach ($cart as $product_id => $quantity) {

    $product_id = (int)$product_id;

    $sql = "SELECT 
                product_id,
                item_name,
                brand,
                price,
                stock_quantity,
                size,
                colour,
                image
            FROM products
            WHERE product_id = $product_id
            LIMIT 1";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {

        $product = mysqli_fetch_assoc($result);

        $product['cart_quantity'] = (int)$quantity;

        $product_total =
            $product['price'] * $product['cart_quantity'];

        $product['product_total'] = $product_total;

        $subtotal += $product_total;

        $products[] = $product;
    }
}


/* =========================
   DELIVERY
========================= */

$delivery = 0;

if ($subtotal > 0) {
    $delivery = 0;
}


/* =========================
   GRAND TOTAL
========================= */

$grand_total = $subtotal + $delivery;


/* =========================
   PLACE ORDER
========================= */

$order_success = false;
$order_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['place_order'])) {

    $customer_name =
        trim($_POST['customer_name'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $address =
        trim($_POST['address'] ?? '');

    $city =
        trim($_POST['city'] ?? '');

    $payment_method =
        trim($_POST['payment_method'] ?? '');


    /* VALIDATION */

    if (
        empty($customer_name) ||
        empty($email) ||
        empty($phone) ||
        empty($address) ||
        empty($city) ||
        empty($payment_method)
    ) {

        $order_message =
            "Please fill all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $order_message =
            "Please enter a valid email address.";

    } else {

        /*
         * Order processing can be connected
         * to your orders table later.
         *
         * For now, the order is completed
         * and the cart is cleared.
         */

        $_SESSION['last_order'] = [
            'customer_name' => $customer_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'payment_method' => $payment_method,
            'subtotal' => $subtotal,
            'delivery' => $delivery,
            'grand_total' => $grand_total
        ];

        $_SESSION['cart'] = [];

        $order_success = true;

        $order_message =
            "Your order has been placed successfully!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>FashionFlair | Checkout</title>


<style>

/* =========================
   RESET
========================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* =========================
   BODY
========================= */

body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    color: #333;

}


/* =========================
   CHECKOUT BACKGROUND
========================= */

.checkout-page {

    min-height: 100vh;

    padding-bottom: 60px;

    background:

        linear-gradient(
            rgba(30, 12, 25, 0.72),
            rgba(30, 12, 25, 0.72)
        ),

        url("images/checkout-bg.jpg");

    background-size: cover;

    background-position: center;

    background-attachment: fixed;
}


/* =========================
   NAVBAR
========================= */

.top-bar {

    width: 100%;

    padding: 18px 7%;

    display: flex;

    justify-content: space-between;

    align-items: center;

    background:
        rgba(255,255,255,0.97);

    box-shadow:
        0 4px 20px rgba(0,0,0,0.15);

    position: sticky;

    top: 0;

    z-index: 100;
}


.logo {

    text-decoration: none;

    color: #d63384;

    font-size: 28px;

    font-weight: 800;

    letter-spacing: 1px;
}


.nav-buttons {

    display: flex;

    align-items: center;

    gap: 12px;
}


.nav-btn {

    text-decoration: none;

    color: #333;

    padding: 10px 18px;

    border-radius: 25px;

    font-weight: 600;

    font-size: 14px;

    transition: 0.3s;
}


.nav-btn:hover {

    background: #d63384;

    color: white;

}


.cart-btn {

    text-decoration: none;

    background: #d63384;

    color: white;

    padding: 11px 20px;

    border-radius: 25px;

    font-weight: 700;

    transition: 0.3s;
}


.cart-btn:hover {

    background: #b52a70;

    transform: translateY(-2px);
}


/* =========================
   PAGE TITLE
========================= */

.checkout-header {

    text-align: center;

    color: white;

    padding: 60px 20px 35px;
}


.checkout-header h1 {

    font-size: 45px;

    margin-bottom: 10px;

    text-shadow:
        0 4px 15px rgba(0,0,0,0.5);
}


.checkout-header p {

    font-size: 17px;

    opacity: 0.95;
}


/* =========================
   MAIN CONTAINER
========================= */

.checkout-container {

    width: 90%;

    max-width: 1200px;

    margin: auto;

    display: grid;

    grid-template-columns:
        1.5fr 1fr;

    gap: 30px;

    align-items: start;
}


/* =========================
   CARD
========================= */

.checkout-card {

    background:
        rgba(255,255,255,0.97);

    border-radius: 22px;

    padding: 30px;

    box-shadow:
        0 15px 40px rgba(0,0,0,0.25);
}


.checkout-card h2 {

    color: #d63384;

    margin-bottom: 25px;

    font-size: 25px;
}


/* =========================
   FORM
========================= */

.form-group {

    margin-bottom: 18px;
}


.form-group label {

    display: block;

    margin-bottom: 7px;

    font-size: 14px;

    font-weight: 700;

    color: #444;
}


.form-group input,
.form-group textarea,
.form-group select {

    width: 100%;

    padding: 13px 15px;

    border: 1px solid #ddd;

    border-radius: 10px;

    outline: none;

    font-size: 14px;

    background: #fff;

    transition: 0.3s;
}


.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {

    border-color: #d63384;

    box-shadow:
        0 0 0 3px rgba(214,51,132,0.12);
}


.form-group textarea {

    min-height: 100px;

    resize: vertical;
}


/* =========================
   TWO COLUMN FORM
========================= */

.form-row {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 15px;
}


/* =========================
   PAYMENT
========================= */

.payment-box {

    margin-top: 10px;

    padding: 18px;

    background: #fff6fa;

    border: 1px solid #f3c3d8;

    border-radius: 14px;
}


.payment-option {

    display: flex;

    align-items: center;

    gap: 10px;

    padding: 10px 0;

    font-weight: 600;

    cursor: pointer;
}


.payment-option input {

    width: 18px;

    height: 18px;

    accent-color: #d63384;
}


/* =========================
   ORDER ITEMS
========================= */

.order-item {

    display: flex;

    align-items: center;

    gap: 15px;

    padding: 15px 0;

    border-bottom:
        1px solid #eee;
}


.order-item:last-child {

    border-bottom: none;
}


.order-image {

    width: 75px;

    height: 75px;

    border-radius: 12px;

    object-fit: cover;

    background: #f3f3f3;
}


.order-info {

    flex: 1;
}


.order-info h3 {

    font-size: 16px;

    margin-bottom: 5px;

    color: #222;
}


.order-info p {

    font-size: 13px;

    color: #777;

    line-height: 1.6;
}


.order-price {

    text-align: right;

    font-weight: 800;

    color: #d63384;
}


/* =========================
   SUMMARY
========================= */

.summary {

    margin-top: 20px;

    padding-top: 20px;

    border-top:
        2px solid #eee;
}


.summary-row {

    display: flex;

    justify-content: space-between;

    padding: 9px 0;

    font-size: 15px;
}


.summary-row.total {

    margin-top: 10px;

    padding-top: 18px;

    border-top:
        2px solid #ddd;

    font-size: 21px;

    font-weight: 800;

    color: #d63384;
}


.free-delivery {

    color: #198754;

    font-weight: 700;
}


/* =========================
   PLACE ORDER BUTTON
========================= */

.place-order-btn {

    width: 100%;

    border: none;

    margin-top: 25px;

    padding: 16px;

    border-radius: 13px;

    background:

        linear-gradient(
            135deg,
            #d63384,
            #ff4d94
        );

    color: white;

    font-size: 16px;

    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 10px 25px rgba(214,51,132,0.35);

    transition: 0.3s;
}


.place-order-btn:hover {

    transform: translateY(-3px);

    box-shadow:
        0 15px 30px rgba(214,51,132,0.45);
}


/* =========================
   BACK TO CART
========================= */

.back-cart {

    display: block;

    text-align: center;

    margin-top: 15px;

    text-decoration: none;

    color: #666;

    font-weight: 600;

}


.back-cart:hover {

    color: #d63384;
}


/* =========================
   ERROR MESSAGE
========================= */

.error-message {

    width: 90%;

    max-width: 1200px;

    margin: 0 auto 25px;

    padding: 15px 20px;

    border-radius: 12px;

    background: #f8d7da;

    color: #842029;

    text-align: center;

    font-weight: 700;
}


/* =========================
   SUCCESS PAGE
========================= */

.success-card {

    width: 90%;

    max-width: 600px;

    margin: 50px auto;

    padding: 45px 30px;

    background:
        rgba(255,255,255,0.97);

    border-radius: 25px;

    text-align: center;

    box-shadow:
        0 20px 50px rgba(0,0,0,0.25);
}


.success-icon {

    font-size: 65px;

    margin-bottom: 15px;
}


.success-card h2 {

    color: #198754;

    font-size: 30px;

    margin-bottom: 12px;
}


.success-card p {

    color: #666;

    line-height: 1.7;

    margin-bottom: 25px;
}


.shop-btn {

    display: inline-block;

    text-decoration: none;

    background:
        linear-gradient(
            135deg,
            #d63384,
            #ff4d94
        );

    color: white;

    padding: 13px 25px;

    border-radius: 12px;

    font-weight: 700;

    transition: 0.3s;
}


.shop-btn:hover {

    transform: translateY(-3px);
}


/* =========================
   FOOTER
========================= */

.checkout-footer {

    text-align: center;

    color: white;

    padding: 40px 20px 10px;

    font-size: 14px;
}


/* =========================
   MOBILE
========================= */

@media (max-width: 850px) {

    .checkout-container {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 600px) {

    .top-bar {

        flex-direction: column;

        gap: 15px;

        padding: 15px 20px;
    }

    .nav-buttons {

        flex-wrap: wrap;

        justify-content: center;
    }

    .checkout-header h1 {

        font-size: 34px;
    }

    .form-row {

        grid-template-columns: 1fr;
    }

    .checkout-card {

        padding: 22px;
    }

    .order-item {

        align-items: flex-start;
    }

}

</style>

</head>


<body>


<div class="checkout-page">


<!-- =========================
     NAVIGATION
========================= -->

<div class="top-bar">

    <a href="index.php" class="logo">
        FashionFlair
    </a>


    <div class="nav-buttons">

        <a href="index.php" class="nav-btn">
            🏠 Home
        </a>

        <a href="products.php" class="nav-btn">
            🛍️ Products
        </a>

        <a href="cart.php" class="cart-btn">
            🛒 Cart
        </a>

    </div>

</div>


<!-- =========================
     HEADER
========================= -->

<div class="checkout-header">

    <h1>Checkout</h1>

    <p>
        Complete your details and place your order
    </p>

</div>


<?php if (!empty($order_message) && !$order_success): ?>

<div class="error-message">

    ⚠️
    <?php echo htmlspecialchars($order_message); ?>

</div>

<?php endif; ?>


<?php if ($order_success): ?>


<!-- =========================
     SUCCESS
========================= -->

<div class="success-card">

    <div class="success-icon">
        ✅
    </div>

    <h2>
        Order Placed Successfully!
    </h2>

    <p>

        Thank you for shopping with
        <strong>FashionFlair</strong>.

        <br>

        Your order has been received successfully.

    </p>


    <a href="products.php" class="shop-btn">

        🛍️ CONTINUE SHOPPING

    </a>

</div>


<?php else: ?>


<!-- =========================
     CHECKOUT CONTENT
========================= -->

<div class="checkout-container">


<!-- =========================
     CUSTOMER DETAILS
========================= -->

<div class="checkout-card">

    <h2>
        👤 Customer Details
    </h2>


    <form method="POST"
          action="checkout.php">


        <div class="form-group">

            <label>
                Full Name *
            </label>

            <input
                type="text"
                name="customer_name"
                placeholder="Enter your full name"
                required
            >

        </div>


        <div class="form-row">


            <div class="form-group">

                <label>
                    Email *
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="example@gmail.com"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Phone *
                </label>

                <input
                    type="text"
                    name="phone"
                    placeholder="07XXXXXXXX"
                    required
                >

            </div>


        </div>


        <div class="form-group">

            <label>
                Address *
            </label>

            <textarea
                name="address"
                placeholder="Enter your delivery address"
                required
            ></textarea>

        </div>


        <div class="form-group">

            <label>
                City *
            </label>

            <input
                type="text"
                name="city"
                placeholder="Enter your city"
                required
            >

        </div>


        <!-- PAYMENT -->

        <div class="form-group">

            <label>
                Payment Method *
            </label>


            <div class="payment-box">


                <label class="payment-option">

                    <input
                        type="radio"
                        name="payment_method"
                        value="Cash on Delivery"
                        required
                    >

                    💵 Cash on Delivery

                </label>


                <label class="payment-option">

                    <input
                        type="radio"
                        name="payment_method"
                        value="Card Payment"
                    >

                    💳 Card Payment

                </label>


                <label class="payment-option">

                    <input
                        type="radio"
                        name="payment_method"
                        value="Bank Transfer"
                    >

                    🏦 Bank Transfer

                </label>


            </div>

        </div>


        <button
            type="submit"
            name="place_order"
            class="place-order-btn"
        >

            ✅ PLACE ORDER

        </button>


        <a href="cart.php"
           class="back-cart">

            ← Back to Cart

        </a>


    </form>

</div>


<!-- =========================
     ORDER SUMMARY
========================= -->

<div class="checkout-card">

    <h2>
        🛒 Your Order
    </h2>


    <?php foreach ($products as $product): ?>


    <div class="order-item">


        <?php

        $image = trim($product['image']);

        $imagePath = "images/" . $image;

        if (!empty($image)
            && file_exists($imagePath)) {

        ?>

        <img
            src="<?php echo htmlspecialchars($imagePath); ?>"
            class="order-image"
            alt="<?php echo htmlspecialchars($product['item_name']); ?>"
        >

        <?php

        } else {

        ?>

        <img
            src="images/no-image.png"
            class="order-image"
            alt="No Image"
        >

        <?php

        }

        ?>


        <div class="order-info">

            <h3>

                <?php
                echo htmlspecialchars(
                    $product['item_name']
                );
                ?>

            </h3>


            <p>

                <?php
                echo htmlspecialchars(
                    $product['brand']
                );
                ?>

                <br>

                Qty:
                <?php
                echo $product['cart_quantity'];
                ?>

                <br>

                Rs.
                <?php
                echo number_format(
                    $product['price'],
                    2
                );
                ?>

            </p>

        </div>


        <div class="order-price">

            Rs.
            <?php
            echo number_format(
                $product['product_total'],
                2
            );
            ?>

        </div>


    </div>


    <?php endforeach; ?>


    <!-- SUMMARY -->

    <div class="summary">


        <div class="summary-row">

            <span>
                Subtotal
            </span>

            <span>

                Rs.
                <?php
                echo number_format(
                    $subtotal,
                    2
                );
                ?>

            </span>

        </div>


        <div class="summary-row">

            <span>
                Delivery
            </span>

            <span class="free-delivery">

                FREE

            </span>

        </div>


        <div class="summary-row total">

            <span>
                Total
            </span>

            <span>

                Rs.
                <?php
                echo number_format(
                    $grand_total,
                    2
                );
                ?>

            </span>

        </div>


    </div>


</div>


</div>


<?php endif; ?>


<!-- FOOTER -->

<div class="checkout-footer">

    © <?php echo date("Y"); ?>
    FashionFlair Boutique.
    All Rights Reserved.

</div>


</div>


</body>

</html>