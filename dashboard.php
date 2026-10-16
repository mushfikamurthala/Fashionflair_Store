<?php

session_start();

require_once "config/db_connect.php";
require_once "includes/admin_check.php";


/* =========================================
   DASHBOARD STATISTICS
========================================= */


/* Total Products */

$product_sql = "SELECT COUNT(*) AS total_products
                FROM products";

$product_result = $conn->query($product_sql);

$product_data = $product_result->fetch_assoc();

$total_products = $product_data['total_products'];


/* Total Customers */

$customer_sql = "SELECT COUNT(*) AS total_customers
                 FROM customers";

$customer_result = $conn->query($customer_sql);

$customer_data = $customer_result->fetch_assoc();

$total_customers = $customer_data['total_customers'];


/* Total Orders */

$order_sql = "SELECT COUNT(*) AS total_orders
              FROM orders";

$order_result = $conn->query($order_sql);

$order_data = $order_result->fetch_assoc();

$total_orders = $order_data['total_orders'];


/* Total Sales */

$sales_sql = "SELECT COALESCE(SUM(TotalAmount), 0) AS total_sales
              FROM sales";

$sales_result = $conn->query($sales_sql);

$sales_data = $sales_result->fetch_assoc();

$total_sales = $sales_data['total_sales'];


/* Pending Orders */

$pending_sql = "SELECT COUNT(*) AS pending_orders
                FROM orders
                WHERE OrderStatus = 'Pending'";

$pending_result = $conn->query($pending_sql);

$pending_data = $pending_result->fetch_assoc();

$pending_orders = $pending_data['pending_orders'];


/* Low Stock Products */

$low_stock_sql = "SELECT COUNT(*) AS low_stock
                  FROM products
                  WHERE StockQuantity <= 5";

$low_stock_result = $conn->query($low_stock_sql);

$low_stock_data = $low_stock_result->fetch_assoc();

$low_stock = $low_stock_data['low_stock'];


/* =========================================
   RECENT ORDERS
========================================= */

$recent_orders_sql = "SELECT
                        o.OrderID,
                        o.OrderDate,
                        o.TotalAmount,
                        o.OrderStatus,
                        c.CustomerName
                      FROM orders o
                      INNER JOIN customers c
                      ON o.CustomerID = c.CustomerID
                      ORDER BY o.OrderID DESC
                      LIMIT 5";

$recent_orders_result = $conn->query($recent_orders_sql);


/* =========================================
   TOP PRODUCTS
========================================= */

$top_products_sql = "SELECT
                        p.ProductName,
                        SUM(od.Quantity) AS TotalSold
                     FROM order_details od
                     INNER JOIN products p
                     ON od.ProductID = p.ProductID
                     GROUP BY od.ProductID, p.ProductName
                     ORDER BY TotalSold DESC
                     LIMIT 5";

$top_products_result = $conn->query($top_products_sql);

?>

<?php require_once "includes/header.php"; ?>


<section class="dashboard-page">

    <div class="container">


        <!-- =================================
             DASHBOARD HEADER
        ================================= -->

        <div class="dashboard-header">

            <div>

                <span>FASHIONFLAIR ADMIN PANEL</span>

                <h1>📊 Dashboard</h1>

                <p>
                    Welcome back,
                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION['FullName']
                        );
                        ?>
                    </strong>
                </p>

            </div>

            <div class="dashboard-date">

                📅
                <?php echo date("d M Y"); ?>

            </div>

        </div>


        <!-- =================================
             STATISTICS CARDS
        ================================= -->

        <div class="dashboard-stats">


            <!-- Products -->

            <div class="dashboard-stat-card">

                <div class="stat-icon products-icon">
                    🛍️
                </div>

                <div>

                    <h3>
                        <?php echo $total_products; ?>
                    </h3>

                    <p>Total Products</p>

                </div>

            </div>


            <!-- Customers -->

            <div class="dashboard-stat-card">

                <div class="stat-icon customers-icon">
                    👥
                </div>

                <div>

                    <h3>
                        <?php echo $total_customers; ?>
                    </h3>

                    <p>Total Customers</p>

                </div>

            </div>


            <!-- Orders -->

            <div class="dashboard-stat-card">

                <div class="stat-icon orders-icon">
                    📦
                </div>

                <div>

                    <h3>
                        <?php echo $total_orders; ?>
                    </h3>

                    <p>Total Orders</p>

                </div>

            </div>


            <!-- Sales -->

            <div class="dashboard-stat-card">

                <div class="stat-icon sales-icon">
                    💰
                </div>

                <div>

                    <h3>
                        Rs.
                        <?php
                        echo number_format(
                            $total_sales,
                            2
                        );
                        ?>
                    </h3>

                    <p>Total Sales</p>

                </div>

            </div>

        </div>


        <!-- =================================
             QUICK STATUS
        ================================= -->

        <div class="dashboard-status">


            <div class="status-box pending-box">

                <div>
                    ⏳
                </div>

                <div>

                    <strong>
                        <?php echo $pending_orders; ?>
                    </strong>

                    <span>
                        Pending Orders
                    </span>

                </div>

            </div>


            <div class="status-box stock-box">

                <div>
                    ⚠️
                </div>

                <div>

                    <strong>
                        <?php echo $low_stock; ?>
                    </strong>

                    <span>
                        Low Stock Products
                    </span>

                </div>

            </div>

        </div>


        <!-- =================================
             QUICK ACTIONS
        ================================= -->

        <div class="dashboard-section-title">

            <h2>Quick Actions</h2>

            <p>
                Manage your FashionFlair store
            </p>

        </div>


        <div class="quick-actions">


            <a
                href="add_product.php"
                class="quick-action-card"
            >

                <span class="quick-action-icon">
                    ➕
                </span>

                <strong>
                    Add Product
                </strong>

                <small>
                    Add a new fashion product
                </small>

            </a>


            <a
                href="products.php"
                class="quick-action-card"
            >

                <span class="quick-action-icon">
                    🛍️
                </span>

                <strong>
                    Manage Products
                </strong>

                <small>
                    View and manage products
                </small>

            </a>


            <a
                href="customers.php"
                class="quick-action-card"
            >

                <span class="quick-action-icon">
                    👥
                </span>

                <strong>
                    Customers
                </strong>

                <small>
                    View registered customers
                </small>

            </a>


            <a
                href="sales.php"
                class="quick-action-card"
            >

                <span class="quick-action-icon">
                    💰
                </span>

                <strong>
                    Sales & Reports
                </strong>

                <small>
                    View sales information
                </small>

            </a>


            <a
                href="order_history.php"
                class="quick-action-card"
            >

                <span class="quick-action-icon">
                    📦
                </span>

                <strong>
                    Orders
                </strong>

                <small>
                    Manage customer orders
                </small>

            </a>


            <a
                href="profile.php"
                class="quick-action-card"
            >

                <span class="quick-action-icon">
                    👤
                </span>

                <strong>
                    My Profile
                </strong>

                <small>
                    Manage admin profile
                </small>

            </a>

        </div>


        <!-- =================================
             RECENT ORDERS + TOP PRODUCTS
        ================================= -->

        <div class="dashboard-columns">


            <!-- Recent Orders -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>📦 Recent Orders</h2>

                        <p>
                            Latest customer orders
                        </p>

                    </div>

                </div>


                <?php if (
                    $recent_orders_result &&
                    $recent_orders_result->num_rows > 0
                ): ?>

                    <div class="dashboard-table-wrapper">

                        <table class="dashboard-table">

                            <thead>

                                <tr>

                                    <th>Order</th>

                                    <th>Customer</th>

                                    <th>Amount</th>

                                    <th>Status</th>

                                </tr>

                            </thead>

                            <tbody>

                            <?php while (
                                $order =
                                $recent_orders_result->fetch_assoc()
                            ): ?>

                                <tr>

                                    <td>
                                        #<?php
                                        echo $order['OrderID'];
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $order['CustomerName']
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        Rs.
                                        <?php
                                        echo number_format(
                                            $order['TotalAmount'],
                                            2
                                        );
                                        ?>
                                    </td>

                                    <td>

                                        <span class="order-status">

                                            <?php
                                            echo htmlspecialchars(
                                                $order['OrderStatus']
                                            );
                                            ?>

                                        </span>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="dashboard-empty">
                        📦 No orders available yet.
                    </div>

                <?php endif; ?>

            </div>


            <!-- Top Products -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>🔥 Top Products</h2>

                        <p>
                            Best selling products
                        </p>

                    </div>

                </div>


                <?php if (
                    $top_products_result &&
                    $top_products_result->num_rows > 0
                ): ?>

                    <div class="top-products-list">

                    <?php

                    $rank = 1;

                    while (
                        $product =
                        $top_products_result->fetch_assoc()
                    ):

                    ?>

                        <div class="top-product-item">

                            <div class="product-rank">

                                <?php
                                echo $rank;
                                ?>

                            </div>

                            <div class="top-product-info">

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $product['ProductName']
                                    );
                                    ?>

                                </strong>

                                <span>

                                    <?php
                                    echo $product['TotalSold'];
                                    ?>

                                    items sold

                                </span>

                            </div>

                        </div>

                    <?php

                    $rank++;

                    endwhile;

                    ?>

                    </div>

                <?php else: ?>

                    <div class="dashboard-empty">

                        🛍️ No sales data available yet.

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- =================================
             FOOTER ACTION
        ================================= -->

        <div class="dashboard-bottom">

            <a
                href="index.php"
                class="dashboard-home-btn"
            >
                🏠 VIEW STORE
            </a>

            <a
                href="logout.php"
                class="dashboard-logout-btn"
            >
                🚪 LOGOUT
            </a>

        </div>


    </div>

</section>


<?php require_once "includes/footer.php"; ?>