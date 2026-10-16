<?php

session_start();

require_once "config/db_connect.php";
require_once "includes/admin_check.php";


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}


/*
|--------------------------------------------------------------------------
| TOTAL SALES / TRANSACTIONS
|--------------------------------------------------------------------------
*/

$total_sales_sql = "
    SELECT
        COUNT(*) AS total_transactions,
        COALESCE(SUM(TotalAmount), 0) AS total_revenue,
        COALESCE(AVG(TotalAmount), 0) AS average_sale
    FROM sales
";

$total_sales_result = $conn->query($total_sales_sql);

$total_sales_data = $total_sales_result->fetch_assoc();

$total_transactions =
    $total_sales_data['total_transactions'];

$total_revenue =
    $total_sales_data['total_revenue'];

$average_sale =
    $total_sales_data['average_sale'];


/*
|--------------------------------------------------------------------------
| TODAY'S SALES
|--------------------------------------------------------------------------
*/

$today_sql = "
    SELECT
        COUNT(*) AS today_transactions,
        COALESCE(SUM(TotalAmount), 0) AS today_revenue
    FROM sales
    WHERE DATE(SaleDate) = CURDATE()
";

$today_result = $conn->query($today_sql);

$today_data = $today_result->fetch_assoc();

$today_transactions =
    $today_data['today_transactions'];

$today_revenue =
    $today_data['today_revenue'];


/*
|--------------------------------------------------------------------------
| SALES LIST
|--------------------------------------------------------------------------
*/

if ($search !== "") {

    $sales_stmt = $conn->prepare("
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

            u.FullName AS StaffName

        FROM sales s

        INNER JOIN customers c
            ON s.CustomerID = c.CustomerID

        INNER JOIN users u
            ON s.UserID = u.UserID

        WHERE
            CAST(s.SaleID AS CHAR) LIKE ?
            OR CAST(s.OrderID AS CHAR) LIKE ?
            OR c.CustomerName LIKE ?
            OR c.Email LIKE ?
            OR u.FullName LIKE ?
            OR s.PaymentMethod LIKE ?

        ORDER BY s.SaleID DESC
    ");

    $search_value = "%" . $search . "%";

    $sales_stmt->bind_param(
        "ssssss",
        $search_value,
        $search_value,
        $search_value,
        $search_value,
        $search_value,
        $search_value
    );

    $sales_stmt->execute();

    $sales_result =
        $sales_stmt->get_result();

} else {

    $sales_sql = "
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

            u.FullName AS StaffName

        FROM sales s

        INNER JOIN customers c
            ON s.CustomerID = c.CustomerID

        INNER JOIN users u
            ON s.UserID = u.UserID

        ORDER BY s.SaleID DESC
    ";

    $sales_result =
        $conn->query($sales_sql);
}

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
        Sales & Reports | FashionFlair
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body class="sales-page">


<div class="sales-overlay">

    <div class="sales-container">


        <!-- BRAND -->

        <div class="sales-brand">

            <span>FASHION</span>
            <strong>FLAIR</strong>

        </div>


        <div class="sales-line"></div>


        <!-- HEADER -->

        <div class="sales-header">

            <div>

                <span class="sales-small-title">
                    FASHIONFLAIR ADMIN PANEL
                </span>

                <h1>
                    💰 Sales & Reports
                </h1>

                <p>
                    Monitor your store sales and
                    transaction performance.
                </p>

            </div>


            <div class="sales-header-date">

                📅

                <?php
                echo date("d M Y");
                ?>

            </div>

        </div>


        <!-- STAT CARDS -->

        <div class="sales-stat-grid">


            <!-- TOTAL TRANSACTIONS -->

            <div class="sales-stat-card">

                <div class="sales-stat-icon">
                    🧾
                </div>

                <div>

                    <span>
                        TOTAL TRANSACTIONS
                    </span>

                    <strong>
                        <?php
                        echo number_format(
                            $total_transactions
                        );
                        ?>
                    </strong>

                </div>

            </div>


            <!-- TOTAL REVENUE -->

            <div class="sales-stat-card">

                <div class="sales-stat-icon">
                    💰
                </div>

                <div>

                    <span>
                        TOTAL REVENUE
                    </span>

                    <strong>
                        Rs.
                        <?php
                        echo number_format(
                            $total_revenue,
                            2
                        );
                        ?>
                    </strong>

                </div>

            </div>


            <!-- AVERAGE SALE -->

            <div class="sales-stat-card">

                <div class="sales-stat-icon">
                    📊
                </div>

                <div>

                    <span>
                        AVERAGE SALE
                    </span>

                    <strong>
                        Rs.
                        <?php
                        echo number_format(
                            $average_sale,
                            2
                        );
                        ?>
                    </strong>

                </div>

            </div>


            <!-- TODAY -->

            <div class="sales-stat-card">

                <div class="sales-stat-icon">
                    📅
                </div>

                <div>

                    <span>
                        TODAY'S REVENUE
                    </span>

                    <strong>
                        Rs.
                        <?php
                        echo number_format(
                            $today_revenue,
                            2
                        );
                        ?>
                    </strong>

                    <small>
                        <?php
                        echo $today_transactions;
                        ?>
                        transaction(s)
                    </small>

                </div>

            </div>


        </div>


        <!-- SEARCH -->

        <div class="sales-search-section">

            <form
                method="GET"
                action="sales.php"
                class="sales-search-form"
            >

                <div class="sales-search-input">

                    🔍

                    <input
                        type="text"
                        name="search"
                        value="<?php
                        echo htmlspecialchars($search);
                        ?>"
                        placeholder="
                            Search Sale ID, Order ID,
                            Customer, Email or Payment...
                        "
                    >

                </div>


                <button
                    type="submit"
                    class="sales-search-btn"
                >
                    🔍 SEARCH
                </button>


                <?php if ($search !== ""): ?>

                    <a
                        href="sales.php"
                        class="sales-clear-btn"
                    >
                        ✕ CLEAR
                    </a>

                <?php endif; ?>

            </form>

        </div>


        <!-- SALES TABLE SECTION -->

        <div class="sales-table-section">


            <div class="sales-table-header">

                <div>

                    <h2>
                        🧾 Sales Transactions
                    </h2>

                    <p>
                        Complete list of FashionFlair sales
                    </p>

                </div>


                <div class="sales-count">

                    <?php
                    echo $sales_result
                        ? $sales_result->num_rows
                        : 0;
                    ?>

                    Sale(s)

                </div>

            </div>


            <?php if (
                $sales_result &&
                $sales_result->num_rows > 0
            ): ?>


                <div class="sales-table-wrapper">

                    <table class="sales-table">


                        <thead>

                            <tr>

                                <th>
                                    Sale
                                </th>

                                <th>
                                    Order
                                </th>

                                <th>
                                    Customer
                                </th>

                                <th>
                                    Staff
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Payment
                                </th>

                                <th>
                                    Amount
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while (
                            $sale =
                            $sales_result->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- SALE -->

                                <td>

                                    <div class="sale-id-box">

                                        <span>
                                            SALE
                                        </span>

                                        <strong>
                                            #<?php
                                            echo $sale['SaleID'];
                                            ?>
                                        </strong>

                                    </div>

                                </td>


                                <!-- ORDER -->

                                <td>

                                    <span class="order-id">

                                        #<?php
                                        echo $sale['OrderID'];
                                        ?>

                                    </span>

                                </td>


                                <!-- CUSTOMER -->

                                <td>

                                    <div class="sales-customer">

                                        <div
                                            class="sales-customer-avatar"
                                        >

                                            <?php

                                            $customer_name =
                                                $sale['CustomerName'];

                                            echo strtoupper(
                                                substr(
                                                    $customer_name,
                                                    0,
                                                    1
                                                )
                                            );

                                            ?>

                                        </div>


                                        <div>

                                            <strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $customer_name
                                                );
                                                ?>

                                            </strong>

                                            <small>

                                                <?php
                                                echo htmlspecialchars(
                                                    $sale['Email']
                                                );
                                                ?>

                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <!-- STAFF -->

                                <td>

                                    <span class="staff-name">

                                        👤

                                        <?php
                                        echo htmlspecialchars(
                                            $sale['StaffName']
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- DATE -->

                                <td>

                                    <div class="sale-date">

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

                                </td>


                                <!-- PAYMENT -->

                                <td>

                                    <span class="payment-badge">

                                        💳

                                        <?php
                                        echo htmlspecialchars(
                                            $sale['PaymentMethod']
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- AMOUNT -->

                                <td>

                                    <strong class="sale-amount">

                                        Rs.

                                        <?php
                                        echo number_format(
                                            $sale['TotalAmount'],
                                            2
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <!-- ACTION -->

                                <td>

                                    <a
                                        href="sale_details.php?id=<?php
                                        echo $sale['SaleID'];
                                        ?>"
                                        class="sale-view-btn"
                                    >

                                        👁 VIEW

                                    </a>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <!-- NO SALES -->

                <div class="sales-empty">

                    <div class="sales-empty-icon">
                        💰
                    </div>

                    <h3>
                        No Sales Found
                    </h3>

                    <?php if ($search !== ""): ?>

                        <p>
                            No sales matched your search
                            for
                            <strong>
                                "<?php
                                echo htmlspecialchars(
                                    $search
                                );
                                ?>"
                            </strong>.
                        </p>

                        <a
                            href="sales.php"
                            class="sales-empty-btn"
                        >
                            VIEW ALL SALES
                        </a>

                    <?php else: ?>

                        <p>
                            No sales have been recorded yet.
                        </p>

                    <?php endif; ?>

                </div>


            <?php endif; ?>


        </div>


        <!-- BOTTOM BUTTONS -->

        <div class="sales-bottom-buttons">


            <a
                href="dashboard.php"
                class="sales-dashboard-btn"
            >
                📊 DASHBOARD
            </a>


            <a
                href="products.php"
                class="sales-products-btn"
            >
                🛍️ PRODUCTS
            </a>


            <a
                href="customers.php"
                class="sales-customers-btn"
            >
                👥 CUSTOMERS
            </a>


            <a
                href="index.php"
                class="sales-store-btn"
            >
                🏠 VIEW STORE
            </a>


        </div>


        <!-- FOOTER -->

        <div class="sales-footer">

            <span>
                FASHIONFLAIR ADMIN PANEL
            </span>

            <p>
                Manage your sales. Understand your business.
                Grow your fashion store.
            </p>

        </div>


    </div>

</div>


</body>

</html>


<?php

if (isset($sales_stmt)) {
    $sales_stmt->close();
}

?>