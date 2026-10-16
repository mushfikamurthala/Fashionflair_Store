<?php

session_start();

require_once "config/db_connect.php";
require_once "includes/admin_check.php";


/*
|--------------------------------------------------------------------------
| CHECK SALE ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: sales.php");
    exit;
}

$sale_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| GET SALE INFORMATION
|--------------------------------------------------------------------------
*/

$sale_stmt = $conn->prepare("
    SELECT
        s.SaleID,
        s.OrderID,
        s.CustomerID,
        s.UserID,
        s.SaleDate,
        s.TotalAmount,
        s.PaymentMethod,

        c.CustomerName,
        c.Email,
        c.Phone,
        c.Address,
        c.City,

        u.FullName AS StaffName,
        u.Email AS StaffEmail

    FROM sales s

    INNER JOIN customers c
        ON s.CustomerID = c.CustomerID

    INNER JOIN users u
        ON s.UserID = u.UserID

    WHERE s.SaleID = ?
");

$sale_stmt->bind_param(
    "i",
    $sale_id
);

$sale_stmt->execute();

$sale_result = $sale_stmt->get_result();


/*
|--------------------------------------------------------------------------
| SALE NOT FOUND
|--------------------------------------------------------------------------
*/

if ($sale_result->num_rows === 0) {

    $sale_stmt->close();

    header("Location: sales.php");
    exit;
}


$sale = $sale_result->fetch_assoc();

$sale_stmt->close();


/*
|--------------------------------------------------------------------------
| GET SALE DETAILS
|--------------------------------------------------------------------------
*/

$details_stmt = $conn->prepare("
    SELECT
        sd.SaleDetailID,
        sd.SaleID,
        sd.ProductID,
        sd.Quantity,
        sd.UnitPrice,
        sd.SubTotal,

        p.ProductName,
        p.Brand,
        p.Size,
        p.Color,
        p.Image

    FROM sale_details sd

    INNER JOIN products p
        ON sd.ProductID = p.ProductID

    WHERE sd.SaleID = ?

    ORDER BY sd.SaleDetailID ASC
");

$details_stmt->bind_param(
    "i",
    $sale_id
);

$details_stmt->execute();

$details_result = $details_stmt->get_result();


/*
|--------------------------------------------------------------------------
| CALCULATE TOTAL
|--------------------------------------------------------------------------
*/

$calculated_total = 0;

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Sale #<?php echo $sale['SaleID']; ?> |
        FashionFlair
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body class="sale-details-page">


<div class="sale-details-overlay">

    <div class="sale-details-card">


        <!-- BRAND -->

        <div class="sale-details-brand">

            <span>FASHION</span>
            <strong>FLAIR</strong>

        </div>


        <!-- DECORATIVE LINE -->

        <div class="sale-details-line"></div>


        <!-- HEADER -->

        <div class="sale-details-header">

            <div>

                <span class="sale-small-title">
                    FASHIONFLAIR SALES MANAGEMENT
                </span>

                <h1>
                    Sale #<?php
                    echo $sale['SaleID'];
                    ?>
                </h1>

                <p>
                    Complete information about this sale
                </p>

            </div>


            <div class="sale-status-badge">

                💰 COMPLETED SALE

            </div>

        </div>


        <!-- SALE SUMMARY -->

        <div class="sale-info-grid">


            <!-- SALE DATE -->

            <div class="sale-info-box">

                <div class="sale-info-icon">
                    📅
                </div>

                <div>

                    <span>
                        SALE DATE
                    </span>

                    <strong>
                        <?php
                        echo date(
                            "d M Y",
                            strtotime(
                                $sale['SaleDate']
                            )
                        );
                        ?>
                    </strong>

                    <small>
                        <?php
                        echo date(
                            "h:i A",
                            strtotime(
                                $sale['SaleDate']
                            )
                        );
                        ?>
                    </small>

                </div>

            </div>


            <!-- PAYMENT -->

            <div class="sale-info-box">

                <div class="sale-info-icon">
                    💳
                </div>

                <div>

                    <span>
                        PAYMENT METHOD
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $sale['PaymentMethod']
                        );
                        ?>
                    </strong>

                    <small class="sale-paid">
                        Payment Received
                    </small>

                </div>

            </div>


            <!-- TOTAL -->

            <div class="sale-info-box">

                <div class="sale-info-icon">
                    💰
                </div>

                <div>

                    <span>
                        SALE TOTAL
                    </span>

                    <strong>
                        Rs.
                        <?php
                        echo number_format(
                            $sale['TotalAmount'],
                            2
                        );
                        ?>
                    </strong>

                </div>

            </div>


        </div>


        <!-- CUSTOMER INFORMATION -->

        <div class="sale-section">

            <div class="sale-section-heading">

                <div class="sale-section-icon">
                    👤
                </div>

                <div>

                    <h2>
                        Customer Information
                    </h2>

                    <p>
                        Customer who made this purchase
                    </p>

                </div>

            </div>


            <div class="sale-customer-grid">


                <div class="sale-customer-item">

                    <span>
                        CUSTOMER NAME
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $sale['CustomerName']
                        );
                        ?>
                    </strong>

                </div>


                <div class="sale-customer-item">

                    <span>
                        EMAIL
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $sale['Email']
                        );
                        ?>
                    </strong>

                </div>


                <div class="sale-customer-item">

                    <span>
                        PHONE
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $sale['Phone']
                        );
                        ?>
                    </strong>

                </div>


                <div class="sale-customer-item">

                    <span>
                        CITY
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $sale['City']
                        );
                        ?>
                    </strong>

                </div>


                <div class="sale-customer-item full">

                    <span>
                        ADDRESS
                    </span>

                    <strong>
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $sale['Address']
                            )
                        );
                        ?>
                    </strong>

                </div>


            </div>

        </div>


        <!-- SALE INFORMATION -->

        <div class="sale-section">

            <div class="sale-section-heading">

                <div class="sale-section-icon">
                    🧾
                </div>

                <div>

                    <h2>
                        Sale Information
                    </h2>

                    <p>
                        Transaction and staff information
                    </p>

                </div>

            </div>


            <div class="sale-transaction-grid">


                <div class="sale-transaction-item">

                    <span>
                        SALE ID
                    </span>

                    <strong>
                        #<?php
                        echo $sale['SaleID'];
                        ?>
                    </strong>

                </div>


                <div class="sale-transaction-item">

                    <span>
                        ORDER ID
                    </span>

                    <strong>
                        #<?php
                        echo $sale['OrderID'];
                        ?>
                    </strong>

                </div>


                <div class="sale-transaction-item">

                    <span>
                        CUSTOMER ID
                    </span>

                    <strong>
                        #<?php
                        echo $sale['CustomerID'];
                        ?>
                    </strong>

                </div>


                <div class="sale-transaction-item">

                    <span>
                        PROCESSED BY
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $sale['StaffName']
                        );
                        ?>
                    </strong>

                </div>


            </div>

        </div>


        <!-- PRODUCTS -->

        <div class="sale-section">

            <div class="sale-section-heading">

                <div class="sale-section-icon">
                    🛍️
                </div>

                <div>

                    <h2>
                        Sold Products
                    </h2>

                    <p>
                        Products included in this sale
                    </p>

                </div>

            </div>


            <div class="sale-products-wrapper">


                <?php if (
                    $details_result &&
                    $details_result->num_rows > 0
                ): ?>


                    <?php while (
                        $item =
                        $details_result->fetch_assoc()
                    ):

                        $calculated_total +=
                            $item['SubTotal'];

                    ?>


                        <div class="sale-product-item">


                            <!-- IMAGE -->

                            <div class="sale-product-image">

                                <?php if (
                                    !empty(
                                        $item['Image']
                                    )
                                ): ?>

                                    <img
                                        src="images/<?php
                                        echo htmlspecialchars(
                                            $item['Image']
                                        );
                                        ?>"
                                        alt="<?php
                                        echo htmlspecialchars(
                                            $item['ProductName']
                                        );
                                        ?>"
                                    >

                                <?php else: ?>

                                    <div class="sale-no-image">
                                        🛍️
                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- PRODUCT INFO -->

                            <div class="sale-product-info">

                                <span class="sale-product-brand">

                                    <?php
                                    echo htmlspecialchars(
                                        $item['Brand']
                                    );
                                    ?>

                                </span>

                                <h3>

                                    <?php
                                    echo htmlspecialchars(
                                        $item['ProductName']
                                    );
                                    ?>

                                </h3>


                                <div class="sale-product-meta">

                                    <span>
                                        Size:
                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $item['Size']
                                            );
                                            ?>
                                        </strong>
                                    </span>


                                    <span>
                                        Color:
                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $item['Color']
                                            );
                                            ?>
                                        </strong>
                                    </span>

                                </div>

                            </div>


                            <!-- QUANTITY -->

                            <div class="sale-product-quantity">

                                <span>
                                    QTY
                                </span>

                                <strong>
                                    <?php
                                    echo $item['Quantity'];
                                    ?>
                                </strong>

                            </div>


                            <!-- UNIT PRICE -->

                            <div class="sale-product-price">

                                <span>
                                    UNIT PRICE
                                </span>

                                <strong>
                                    Rs.
                                    <?php
                                    echo number_format(
                                        $item['UnitPrice'],
                                        2
                                    );
                                    ?>
                                </strong>

                            </div>


                            <!-- SUBTOTAL -->

                            <div class="sale-product-subtotal">

                                <span>
                                    SUBTOTAL
                                </span>

                                <strong>
                                    Rs.
                                    <?php
                                    echo number_format(
                                        $item['SubTotal'],
                                        2
                                    );
                                    ?>
                                </strong>

                            </div>


                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="sale-empty">

                        <div class="sale-empty-icon">
                            🧾
                        </div>

                        <h3>
                            No Sale Details Found
                        </h3>

                        <p>
                            There are no products recorded
                            for this sale.
                        </p>

                    </div>


                <?php endif; ?>


            </div>

        </div>


        <!-- TOTAL SUMMARY -->

        <div class="sale-total-box">


            <div class="sale-total-row">

                <span>
                    Products Total
                </span>

                <strong>
                    Rs.
                    <?php
                    echo number_format(
                        $calculated_total,
                        2
                    );
                    ?>
                </strong>

            </div>


            <div class="sale-total-row">

                <span>
                    Payment Method
                </span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $sale['PaymentMethod']
                    );
                    ?>
                </strong>

            </div>


            <div class="sale-total-divider"></div>


            <div class="sale-grand-total">

                <span>
                    GRAND TOTAL
                </span>

                <strong>
                    Rs.
                    <?php
                    echo number_format(
                        $sale['TotalAmount'],
                        2
                    );
                    ?>
                </strong>

            </div>


        </div>


        <!-- BUTTONS -->

        <div class="sale-details-buttons">


            <a
                href="sales.php"
                class="sale-back-btn"
            >
                ← BACK TO SALES
            </a>


            <a
                href="dashboard.php"
                class="sale-dashboard-btn"
            >
                📊 DASHBOARD
            </a>


            <a
                href="products.php"
                class="sale-shop-btn"
            >
                🛍️ PRODUCTS
            </a>


        </div>


        <!-- FOOTER -->

        <div class="sale-details-footer">

            <span>
                FASHIONFLAIR ADMIN PANEL
            </span>

            <p>
                Manage your sales professionally.
            </p>

        </div>


    </div>

</div>


</body>

</html>


<?php

$details_stmt->close();

?>