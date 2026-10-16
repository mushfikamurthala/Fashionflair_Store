<?php

session_start();

require_once "config/db_connect.php";
require_once "includes/admin_check.php";


/* =========================================
   SEARCH CUSTOMER
========================================= */

$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}


/* =========================================
   GET CUSTOMERS
========================================= */

if ($search !== "") {

    $sql = "SELECT
                c.CustomerID,
                c.UserID,
                c.CustomerName,
                c.Phone,
                c.Email,
                c.Address,
                c.City,
                c.CreatedAt,
                u.Status
            FROM customers c
            INNER JOIN users u
            ON c.UserID = u.UserID
            WHERE
                c.CustomerName LIKE ?
                OR c.Email LIKE ?
                OR c.Phone LIKE ?
                OR c.City LIKE ?
            ORDER BY c.CustomerID DESC";

    $stmt = $conn->prepare($sql);

    $search_value = "%" . $search . "%";

    $stmt->bind_param(
        "ssss",
        $search_value,
        $search_value,
        $search_value,
        $search_value
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $sql = "SELECT
                c.CustomerID,
                c.UserID,
                c.CustomerName,
                c.Phone,
                c.Email,
                c.Address,
                c.City,
                c.CreatedAt,
                u.Status
            FROM customers c
            INNER JOIN users u
            ON c.UserID = u.UserID
            ORDER BY c.CustomerID DESC";

    $result = $conn->query($sql);
}


/* =========================================
   CUSTOMER COUNT
========================================= */

$count_sql = "SELECT COUNT(*) AS total_customers
              FROM customers";

$count_result = $conn->query($count_sql);

$count_data = $count_result->fetch_assoc();

$total_customers = $count_data['total_customers'];

?>

<?php require_once "includes/header.php"; ?>


<section class="customers-page">

    <div class="container">

        <!-- =================================
             PAGE HEADER
        ================================= -->

        <div class="customers-header">

            <span>FASHIONFLAIR ADMIN</span>

            <h1>👥 Customers</h1>

            <p>
                Manage registered FashionFlair customers
            </p>

        </div>


        <!-- =================================
             CUSTOMER SUMMARY
        ================================= -->

        <div class="customer-summary">

            <div class="summary-card">

                <div class="summary-icon">
                    👥
                </div>

                <div>

                    <h3>
                        <?php echo $total_customers; ?>
                    </h3>

                    <p>Total Customers</p>

                </div>

            </div>

        </div>


        <!-- =================================
             SEARCH
        ================================= -->

        <div class="customer-tools">

            <form
                method="GET"
                action="customers.php"
                class="customer-search-form"
            >

                <input
                    type="text"
                    name="search"
                    placeholder="Search by name, email, phone or city..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >

                <button type="submit">
                    🔍 SEARCH
                </button>

                <?php if ($search !== ""): ?>

                    <a href="customers.php">
                        ✖ CLEAR
                    </a>

                <?php endif; ?>

            </form>

        </div>


        <!-- =================================
             CUSTOMER TABLE
        ================================= -->

        <div class="customers-table-container">

            <?php if ($result && $result->num_rows > 0): ?>

                <table class="customers-table">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Customer</th>

                            <th>Contact</th>

                            <th>Address</th>

                            <th>City</th>

                            <th>Status</th>

                            <th>Registered Date</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while ($customer = $result->fetch_assoc()): ?>

                        <tr>

                            <!-- Customer ID -->

                            <td>
                                <strong>
                                    #<?php
                                    echo $customer['CustomerID'];
                                    ?>
                                </strong>
                            </td>


                            <!-- Customer -->

                            <td>

                                <div class="customer-name">

                                    <div class="customer-avatar">
                                        <?php
                                        echo strtoupper(
                                            substr(
                                                $customer['CustomerName'],
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
                                                $customer['CustomerName']
                                            );
                                            ?>
                                        </strong>

                                        <small>
                                            User ID:
                                            <?php
                                            echo $customer['UserID'];
                                            ?>
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <!-- Contact -->

                            <td>

                                <div class="contact-info">

                                    <span>
                                        📧
                                        <?php
                                        echo htmlspecialchars(
                                            $customer['Email']
                                        );
                                        ?>
                                    </span>

                                    <span>
                                        📞
                                        <?php
                                        echo htmlspecialchars(
                                            $customer['Phone']
                                        );
                                        ?>
                                    </span>

                                </div>

                            </td>


                            <!-- Address -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $customer['Address']
                                );
                                ?>

                            </td>


                            <!-- City -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $customer['City']
                                );
                                ?>

                            </td>


                            <!-- Status -->

                            <td>

                                <?php if ($customer['Status'] === 'Active'): ?>

                                    <span class="status-active">
                                        ● Active
                                    </span>

                                <?php else: ?>

                                    <span class="status-inactive">
                                        ● Inactive
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- Date -->

                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $customer['CreatedAt']
                                    )
                                );
                                ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>


            <?php else: ?>

                <div class="no-customers">

                    <div class="no-customer-icon">
                        👥
                    </div>

                    <h2>No Customers Found</h2>

                    <?php if ($search !== ""): ?>

                        <p>
                            No customers matched your search.
                        </p>

                        <a href="customers.php">
                            VIEW ALL CUSTOMERS
                        </a>

                    <?php else: ?>

                        <p>
                            There are no registered customers yet.
                        </p>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

        </div>


        <!-- =================================
             BACK TO DASHBOARD
        ================================= -->

        <div class="customer-bottom-actions">

            <a
                href="dashboard.php"
                class="back-dashboard-btn"
            >
                ← BACK TO DASHBOARD
            </a>

            <a
                href="products.php"
                class="view-products-btn"
            >
                🛍️ VIEW PRODUCTS
            </a>

        </div>

    </div>

</section>


<?php require_once "includes/footer.php"; ?>