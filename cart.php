<?php

session_start();

require_once "config/db_connect.php";
require_once "includes/auth_check.php";


/* =========================================
   CREATE CART SESSION
========================================= */

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}


/* =========================================
   ADD PRODUCT TO CART
   Supports:
   cart.php?add=1
   cart.php?product_id=1
========================================= */

if (isset($_GET['add'])) {

    $product_id = intval($_GET['add']);

    if ($product_id > 0) {

        /* Check product exists */

        $sql = "SELECT product_id, item_name, price, stock_quantity
                FROM products
                WHERE product_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $product = $result->fetch_assoc();

            if ($product['stock_quantity'] > 0) {

                if (isset($_SESSION['cart'][$product_id])) {

                    /* Increase quantity */

                    if (
                        $_SESSION['cart'][$product_id]
                        <
                        $product['stock_quantity']
                    ) {

                        $_SESSION['cart'][$product_id]++;

                    }

                } else {

                    /* First item */

                    $_SESSION['cart'][$product_id] = 1;

                }

            }
        }
    }

    header("Location: cart.php");
    exit();
}


/* =========================================
   UPDATE CART
========================================= */

if (isset($_POST['update_cart'])) {

    if (isset($_POST['quantity'])) {

        foreach ($_POST['quantity'] as $product_id => $quantity) {

            $product_id = intval($product_id);
            $quantity = intval($quantity);

            if ($quantity <= 0) {

                unset($_SESSION['cart'][$product_id]);

            } else {

                /* Check stock */

                $sql = "SELECT stock_quantity
                        FROM products
                        WHERE product_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $product_id);
                $stmt->execute();

                $result = $stmt->get_result();

                if ($result->num_rows > 0) {

                    $product = $result->fetch_assoc();

                    $stock = intval($product['stock_quantity']);

                    if ($quantity > $stock) {
                        $quantity = $stock;
                    }

                    $_SESSION['cart'][$product_id] = $quantity;

                } else {

                    unset($_SESSION['cart'][$product_id]);
                }
            }
        }
    }

    header("Location: cart.php");
    exit();
}


/* =========================================
   REMOVE PRODUCT
========================================= */

if (isset($_GET['remove'])) {

    $product_id = intval($_GET['remove']);

    if (isset($_SESSION['cart'][$product_id])) {

        unset($_SESSION['cart'][$product_id]);

    }

    header("Location: cart.php");
    exit();
}


/* =========================================
   CLEAR CART
========================================= */

if (isset($_GET['clear'])) {

    $_SESSION['cart'] = [];

    header("Location: cart.php");
    exit();
}


/* =========================================
   GET CART PRODUCTS
========================================= */

$cart_products = [];

$subtotal = 0;

$total_items = 0;


if (!empty($_SESSION['cart'])) {

    foreach ($_SESSION['cart'] as $product_id => $quantity) {

        $product_id = intval($product_id);
        $quantity = intval($quantity);

        if ($quantity <= 0) {
            continue;
        }


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
                WHERE product_id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "i",
            $product_id
        );

        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows > 0) {

            $product = $result->fetch_assoc();


            /* Check stock */

            if ($quantity > $product['stock_quantity']) {

                $quantity = $product['stock_quantity'];

                $_SESSION['cart'][$product_id] = $quantity;
            }


            $item_total =
                $product['price'] * $quantity;


            $subtotal += $item_total;

            $total_items += $quantity;


            $product['quantity'] = $quantity;

            $product['item_total'] = $item_total;


            $cart_products[] = $product;
        }
    }
}


/* =========================================
   TOTAL
========================================= */

$total = $subtotal;

?>

<?php require_once "includes/header.php"; ?>


<style>

/* =========================================
   CART PAGE
========================================= */

.cart-page {

    min-height: 650px;

    padding: 50px 20px 70px;

    background:
        linear-gradient(
            rgba(255,255,255,0.94),
            rgba(255,246,250,0.96)
        );

    font-family: Arial, sans-serif;
}


/* =========================================
   CONTAINER
========================================= */

.cart-container {

    width: 92%;

    max-width: 1150px;

    margin: auto;
}


/* =========================================
   HEADING
========================================= */

.cart-heading {

    text-align: center;

    margin-bottom: 35px;
}

.cart-heading span {

    display: block;

    font-size: 13px;

    font-weight: bold;

    letter-spacing: 4px;

    color: #b44e79;

    margin-bottom: 8px;
}

.cart-heading h1 {

    font-size: 34px;

    color: #222;

    margin-bottom: 8px;
}

.cart-heading p {

    color: #777;

    font-size: 14px;
}


/* =========================================
   CART LAYOUT
========================================= */

.cart-layout {

    display: grid;

    grid-template-columns: 1fr 330px;

    gap: 28px;

    align-items: start;
}


/* =========================================
   CART CARD
========================================= */

.cart-card {

    background: white;

    border-radius: 18px;

    border: 1px solid #f0dce4;

    box-shadow:
        0 8px 30px rgba(100,40,65,0.08);

    overflow: hidden;
}


/* =========================================
   CART HEADER
========================================= */

.cart-card-header {

    padding: 20px 25px;

    border-bottom: 1px solid #eee;

    display: flex;

    justify-content: space-between;

    align-items: center;
}

.cart-card-header h2 {

    font-size: 20px;

    color: #333;
}

.item-count {

    background: #f8e1ea;

    color: #a8446e;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;
}


/* =========================================
   CART ITEM
========================================= */

.cart-item {

    display: grid;

    grid-template-columns: 95px 1fr auto;

    gap: 18px;

    align-items: center;

    padding: 22px 25px;

    border-bottom: 1px solid #eee;
}


/* =========================================
   PRODUCT IMAGE
========================================= */

.cart-image {

    width: 95px;

    height: 110px;

    border-radius: 12px;

    overflow: hidden;

    background: #f5f1f3;

    display: flex;

    align-items: center;

    justify-content: center;
}

.cart-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;
}

.no-image {

    font-size: 30px;

    color: #bbb;
}


/* =========================================
   PRODUCT DETAILS
========================================= */

.cart-details h3 {

    font-size: 17px;

    color: #333;

    margin-bottom: 5px;
}

.cart-details .brand {

    font-size: 12px;

    color: #999;

    margin-bottom: 8px;
}

.product-meta {

    display: flex;

    gap: 8px;

    flex-wrap: wrap;

    margin-bottom: 8px;
}

.product-meta span {

    background: #f8f3f5;

    color: #777;

    padding: 4px 8px;

    border-radius: 5px;

    font-size: 10px;
}

.product-price {

    color: #b44e79;

    font-weight: bold;

    font-size: 15px;
}


/* =========================================
   QUANTITY
========================================= */

.quantity-box {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-top: 10px;
}

.quantity-box label {

    font-size: 12px;

    color: #777;
}

.quantity-box input {

    width: 60px;

    padding: 7px;

    text-align: center;

    border: 1px solid #ddd;

    border-radius: 7px;

    outline: none;
}


/* =========================================
   ITEM RIGHT
========================================= */

.cart-item-right {

    text-align: right;
}

.item-total {

    color: #333;

    font-size: 16px;

    font-weight: bold;

    margin-bottom: 12px;
}

.remove-btn {

    color: #c44d5c;

    text-decoration: none;

    font-size: 12px;

    font-weight: bold;
}

.remove-btn:hover {

    color: #e00020;
}


/* =========================================
   UPDATE BUTTON
========================================= */

.cart-update {

    padding: 18px 25px;

    display: flex;

    justify-content: space-between;

    align-items: center;
}

.update-btn {

    background: #222;

    color: white;

    border: none;

    padding: 11px 18px;

    border-radius: 8px;

    font-size: 12px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;
}

.update-btn:hover {

    background: #b44e79;

    transform: translateY(-2px);
}

.clear-btn {

    color: #c44d5c;

    text-decoration: none;

    font-size: 12px;

    font-weight: bold;
}


/* =========================================
   SUMMARY
========================================= */

.summary-card {

    background: white;

    border-radius: 18px;

    border: 1px solid #f0dce4;

    padding: 28px;

    box-shadow:
        0 8px 30px rgba(100,40,65,0.08);

    position: sticky;

    top: 20px;
}

.summary-card h2 {

    font-size: 21px;

    margin-bottom: 22px;

    color: #333;
}

.summary-row {

    display: flex;

    justify-content: space-between;

    margin-bottom: 15px;

    font-size: 14px;

    color: #666;
}

.summary-row strong {

    color: #333;
}

.summary-divider {

    border: none;

    border-top: 1px solid #eee;

    margin: 20px 0;
}

.total-row {

    display: flex;

    justify-content: space-between;

    font-size: 20px;

    font-weight: bold;

    color: #222;

    margin-bottom: 22px;
}

.total-price {

    color: #b44e79;
}


/* =========================================
   CHECKOUT BUTTON
========================================= */

.checkout-btn {

    display: block;

    width: 100%;

    text-align: center;

    padding: 14px;

    background: linear-gradient(
        135deg,
        #b44e79,
        #dd82a5
    );

    color: white;

    border-radius: 10px;

    text-decoration: none;

    font-size: 14px;

    font-weight: bold;

    transition: 0.3s;
}

.checkout-btn:hover {

    transform: translateY(-2px);

    box-shadow:
        0 8px 20px rgba(180,78,121,0.25);
}


/* =========================================
   CONTINUE SHOPPING
========================================= */

.continue-btn {

    display: block;

    width: 100%;

    text-align: center;

    margin-top: 12px;

    padding: 12px;

    background: white;

    color: #b44e79;

    border: 1px solid #dfb8c8;

    border-radius: 10px;

    text-decoration: none;

    font-size: 13px;

    font-weight: bold;

    transition: 0.3s;
}

.continue-btn:hover {

    background: #b44e79;

    color: white;
}


/* =========================================
   EMPTY CART
========================================= */

.empty-cart {

    background: white;

    border-radius: 18px;

    padding: 70px 30px;

    text-align: center;

    border: 1px solid #f0dce4;

    box-shadow:
        0 8px 30px rgba(100,40,65,0.08);
}

.empty-cart-icon {

    font-size: 70px;

    margin-bottom: 15px;
}

.empty-cart h2 {

    font-size: 25px;

    color: #333;

    margin-bottom: 10px;
}

.empty-cart p {

    color: #888;

    font-size: 14px;

    margin-bottom: 25px;
}

.shop-now-btn {

    display: inline-block;

    background: #b44e79;

    color: white;

    padding: 13px 25px;

    border-radius: 9px;

    text-decoration: none;

    font-weight: bold;

    font-size: 13px;
}

.shop-now-btn:hover {

    background: #963d65;
}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 850px) {

    .cart-layout {

        grid-template-columns: 1fr;
    }

    .summary-card {

        position: static;
    }
}


@media (max-width: 600px) {

    .cart-page {

        padding: 35px 10px 50px;
    }

    .cart-container {

        width: 96%;
    }

    .cart-heading h1 {

        font-size: 28px;
    }

    .cart-item {

        grid-template-columns: 75px 1fr;

        padding: 18px;
    }

    .cart-image {

        width: 75px;

        height: 90px;
    }

    .cart-item-right {

        grid-column: 2;

        text-align: left;
    }

    .cart-update {

        flex-direction: column;

        align-items: stretch;

        gap: 12px;
    }

}

</style>


<!-- =========================================
     CART PAGE
========================================= -->

<section class="cart-page">

    <div class="cart-container">


        <!-- HEADING -->

        <div class="cart-heading">

            <span>FASHIONFLAIR</span>

            <h1>🛒 My Shopping Cart</h1>

            <p>
                Review your selected fashion items before checkout.
            </p>

        </div>


        <?php if (empty($cart_products)): ?>


            <!-- =================================
                 EMPTY CART
            ================================= -->

            <div class="empty-cart">

                <div class="empty-cart-icon">
                    🛒
                </div>

                <h2>
                    Your Cart is Empty
                </h2>

                <p>
                    You haven't added any products to your cart yet.
                </p>

                <a
                    href="products.php"
                    class="shop-now-btn"
                >
                    🛍️ SHOP NOW
                </a>

            </div>


        <?php else: ?>


            <!-- =================================
                 CART + SUMMARY
            ================================= -->

            <div class="cart-layout">


                <!-- =================================
                     CART ITEMS
                ================================= -->

                <div class="cart-card">


                    <div class="cart-card-header">

                        <h2>
                            Shopping Cart
                        </h2>

                        <span class="item-count">

                            <?php
                            echo $total_items;
                            ?>

                            Items

                        </span>

                    </div>


                    <!-- UPDATE FORM -->

                    <form
                        method="POST"
                        action="cart.php"
                    >


                        <?php foreach ($cart_products as $product): ?>


                            <div class="cart-item">


                                <!-- IMAGE -->

                                <div class="cart-image">

                                    <?php

                                    $image = $product['image'] ?? '';

                                    if (
                                        !empty($image) &&
                                        file_exists("images/" . $image)
                                    ):

                                    ?>

                                        <img
                                            src="images/<?php echo htmlspecialchars($image); ?>"
                                            alt="<?php echo htmlspecialchars($product['item_name']); ?>"
                                        >

                                    <?php else: ?>

                                        <div class="no-image">
                                            👗
                                        </div>

                                    <?php endif; ?>

                                </div>


                                <!-- DETAILS -->

                                <div class="cart-details">

                                    <h3>
                                        <?php
                                        echo htmlspecialchars(
                                            $product['item_name']
                                        );
                                        ?>
                                    </h3>

                                    <div class="brand">

                                        Brand:
                                        <?php
                                        echo htmlspecialchars(
                                            $product['brand']
                                        );
                                        ?>

                                    </div>


                                    <div class="product-meta">

                                        <?php if (!empty($product['size'])): ?>

                                            <span>
                                                Size:
                                                <?php
                                                echo htmlspecialchars(
                                                    $product['size']
                                                );
                                                ?>
                                            </span>

                                        <?php endif; ?>


                                        <?php if (!empty($product['colour'])): ?>

                                            <span>
                                                Colour:
                                                <?php
                                                echo htmlspecialchars(
                                                    $product['colour']
                                                );
                                                ?>
                                            </span>

                                        <?php endif; ?>

                                    </div>


                                    <div class="product-price">

                                        Rs.
                                        <?php
                                        echo number_format(
                                            $product['price'],
                                            2
                                        );
                                        ?>

                                    </div>


                                    <!-- QUANTITY -->

                                    <div class="quantity-box">

                                        <label>
                                            Quantity:
                                        </label>

                                        <input
                                            type="number"
                                            name="quantity[<?php echo $product['product_id']; ?>]"
                                            value="<?php echo $product['quantity']; ?>"
                                            min="1"
                                            max="<?php echo $product['stock_quantity']; ?>"
                                        >

                                    </div>

                                </div>


                                <!-- RIGHT -->

                                <div class="cart-item-right">

                                    <div class="item-total">

                                        Rs.
                                        <?php
                                        echo number_format(
                                            $product['item_total'],
                                            2
                                        );
                                        ?>

                                    </div>


                                    <a
                                        href="cart.php?remove=<?php echo $product['product_id']; ?>"
                                        class="remove-btn"
                                        onclick="return confirm('Remove this item from cart?');"
                                    >
                                        🗑️ REMOVE
                                    </a>

                                </div>


                            </div>


                        <?php endforeach; ?>


                        <!-- UPDATE / CLEAR -->

                        <div class="cart-update">

                            <button
                                type="submit"
                                name="update_cart"
                                class="update-btn"
                            >
                                🔄 UPDATE CART
                            </button>


                            <a
                                href="cart.php?clear=1"
                                class="clear-btn"
                                onclick="return confirm('Clear all items from cart?');"
                            >
                                🗑️ CLEAR CART
                            </a>

                        </div>


                    </form>


                </div>


                <!-- =================================
                     ORDER SUMMARY
                ================================= -->

                <div class="summary-card">

                    <h2>
                        Order Summary
                    </h2>


                    <div class="summary-row">

                        <span>
                            Items
                        </span>

                        <strong>
                            <?php echo $total_items; ?>
                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>
                            Subtotal
                        </span>

                        <strong>
                            Rs.
                            <?php
                            echo number_format(
                                $subtotal,
                                2
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>
                            Delivery
                        </span>

                        <strong>
                            FREE
                        </strong>

                    </div>


                    <hr class="summary-divider">


                    <div class="total-row">

                        <span>
                            Total
                        </span>

                        <span class="total-price">

                            Rs.
                            <?php
                            echo number_format(
                                $total,
                                2
                            );
                            ?>

                        </span>

                    </div>


                    <!-- CHECKOUT -->

                    <a
                        href="checkout.php"
                        class="checkout-btn"
                    >
                        💳 PROCEED TO CHECKOUT
                    </a>


                    <!-- CONTINUE -->

                    <a
                        href="products.php"
                        class="continue-btn"
                    >
                        🛍️ CONTINUE SHOPPING
                    </a>

                </div>


            </div>


        <?php endif; ?>


    </div>

</section>


<?php require_once "includes/footer.php"; ?>