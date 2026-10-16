<?php

require_once "includes/auth_check.php";
require_once "config/db_connect.php";

$user_id = (int) $_SESSION['UserID'];

/* Get orders belonging to the logged-in customer */
$stmt = $conn->prepare("
    SELECT
        o.OrderID,
        o.OrderDate,
        o.TotalAmount,
        o.OrderStatus,
        o.PaymentMethod,
        o.PaymentStatus
    FROM orders o
    INNER JOIN customers c
        ON o.CustomerID = c.CustomerID
    WHERE c.UserID = ?
    ORDER BY o.OrderDate DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$orders = $stmt->get_result();

$total_orders = $orders->num_rows;

$total_spent = 0;

$customer_orders = [];

while ($order = $orders->fetch_assoc()) {
    $customer_orders[] = $order;

    if ($order['OrderStatus'] !== 'Cancelled') {
        $total_spent += (float) $order['TotalAmount'];
    }
}

$stmt->close();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Order History | FashionFlair</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            background:
                linear-gradient(
                    rgba(12, 12, 12, 0.75),
                    rgba(12, 12, 12, 0.75)
                ),
                url("images/belt.png");

            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
        }

        .page-wrapper {
            min-height: 100vh;
            padding: 40px 20px 60px;
        }

        .container {
            width: 100%;
            max-width: 1150px;
            margin: 0 auto;
        }

        /* Brand */

        .brand {
            text-align: center;
            color: white;
            font-size: 23px;
            font-weight: bold;
            letter-spacing: 6px;
            margin: 5px 0 15px;
        }

        .brand span {
            color: #fff;
        }

        .brand strong {
            color: #d4af37;
        }

        .gold-line {
            width: 100px;
            height: 3px;
            margin: 0 auto 35px;
            background: #d4af37;
            border-radius: 5px;
        }

        /* Main card */

        .history-card {
            padding: 38px;
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid rgba(212, 175, 55, 0.5);
            border-radius: 18px;
            box-shadow: 0 20px 55px rgba(0, 0, 0, 0.3);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .eyebrow {
            color: #aa8618;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 3px;
        }

        h1 {
            margin: 12px 0;
            color: #151515;
            font-size: 34px;
        }

        .page-description {
            color: #777;
            font-size: 14px;
            line-height: 1.7;
            margin: 0;
        }

        .back-btn {
            display: inline-block;
            padding: 12px 18px;
            background: #111;
            color: #fff;
            border-radius: 7px;
            font-size: 13px;
            font-weight: bold;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: #d4af37;
            color: #111;
        }

        /* Summary cards */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .summary-card {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 23px;
            background: #fff;
            border: 1px solid #eee;
            border-left: 4px solid #d4af37;
            border-radius: 10px;
        }

        .summary-icon {
            width: 52px;
            height: 52px;
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #171717;
            color: #d4af37;
            border-radius: 12px;
            font-size: 24px;
        }

        .summary-card span {
            display: block;
            margin-bottom: 8px;
            color: #777;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .summary-card strong {
            color: #151515;
            font-size: 23px;
        }

        /* Table */

        .section-heading {
            margin: 0 0 8px;
            font-size: 22px;
        }

        .section-description {
            margin: 0 0 22px;
            color: #888;
            font-size: 13px;
            line-height: 1.7;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #e8e8e8;
            border-radius: 10px;
        }

        .orders-table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
            background: #fff;
        }

        .orders-table th {
            padding: 17px 14px;
            text-align: left;
            background: #171717;
            color: #d4af37;
            font-size: 11px;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        .orders-table td {
            padding: 18px 14px;
            border-bottom: 1px solid #eee;
            color: #444;
            font-size: 13px;
            vertical-align: middle;
        }

        .orders-table tbody tr:last-child td {
            border-bottom: none;
        }

        .orders-table tbody tr:hover {
            background: #fffaf0;
        }

        .order-number {
            color: #111;
            font-weight: bold;
        }

        .amount {
            color: #967516;
            font-weight: bold;
            white-space: nowrap;
        }

        .status {
            display: inline-block;
            padding: 7px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        .status-pending {
            background: #fff1cf;
            color: #855d00;
        }

        .status-confirmed,
        .status-processing {
            background: #e5efff;
            color: #2156a5;
        }

        .status-shipped {
            background: #eee7ff;
            color: #6238a1;
        }

        .status-delivered {
            background: #dff6e6;
            color: #19703a;
        }

        .status-cancelled,
        .status-failed {
            background: #ffe4e4;
            color: #a32222;
        }

        .status-paid {
            background: #dff6e6;
            color: #19703a;
        }

        .status-unknown {
            background: #eee;
            color: #555;
        }

        .view-btn {
            display: inline-block;
            padding: 10px 13px;
            background: #d4af37;
            color: #111;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
            transition: 0.3s;
        }

        .view-btn:hover {
            background: #111;
            color: #fff;
        }

        /* Empty state */

        .empty-state {
            padding: 55px 20px;
            text-align: center;
            background: #fff;
            border: 1px dashed #d8c58a;
            border-radius: 12px;
        }

        .empty-icon {
            font-size: 55px;
            margin-bottom: 15px;
        }

        .empty-state h2 {
            margin: 0 0 12px;
            color: #222;
            font-size: 24px;
        }

        .empty-state p {
            margin: 0 auto 25px;
            max-width: 450px;
            color: #777;
            font-size: 14px;
            line-height: 1.8;
        }

        .shop-btn {
            display: inline-block;
            padding: 14px 24px;
            background: #111;
            color: #d4af37;
            border-radius: 7px;
            font-size: 13px;
            font-weight: bold;
            transition: 0.3s;
        }

        .shop-btn:hover {
            background: #d4af37;
            color: #111;
        }

        /* Footer */

        .page-footer {
            margin-top: 30px;
            text-align: center;
            color: #fff;
        }

        .page-footer strong {
            color: #d4af37;
            letter-spacing: 3px;
            font-size: 12px;
        }

        .page-footer p {
            margin: 10px 0 0;
            font-size: 12px;
            color: #eee;
        }

        @media (max-width: 650px) {
            .page-wrapper {
                padding: 25px 12px 35px;
            }

            .history-card {
                padding: 23px 16px;
            }

            .page-header {
                align-items: flex-start;
            }

            h1 {
                font-size: 27px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .summary-card {
                padding: 18px;
            }

            .gold-line {
                margin-bottom: 25px;
            }
        }
    </style>
</head>

<body>

<div class="page-wrapper">

    <div class="container">

        <div class="brand">
            <span>FASHION</span><strong>FLAIR</strong>
        </div>

        <div class="gold-line"></div>

        <main class="history-card">

            <div class="page-header">

                <div>
                    <span class="eyebrow">
                        YOUR ACCOUNT
                    </span>

                    <h1>📦 My Order History</h1>

                    <p class="page-description">
                        View your previous orders and track
                        their current status.
                    </p>
                </div>

                <a href="products.php" class="back-btn">
                    ← CONTINUE SHOPPING
                </a>

            </div>

            <div class="summary-grid">

                <div class="summary-card">
                    <div class="summary-icon">📦</div>

                    <div>
                        <span>TOTAL ORDERS</span>
                        <strong>
                            <?php echo $total_orders; ?>
                        </strong>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon">💰</div>

                    <div>
                        <span>ORDER VALUE</span>
                        <strong>
                            Rs. <?php
                            echo number_format($total_spent, 2);
                            ?>
                        </strong>
                    </div>
                </div>

            </div>

            <h2 class="section-heading">
                Your Orders
            </h2>

            <p class="section-description">
                Your orders are displayed below, starting
                with the most recent.
            </p>

            <?php if ($total_orders > 0): ?>

                <div class="table-wrapper">

                    <table class="orders-table">

                        <thead>
                            <tr>
                                <th>ORDER ID</th>
                                <th>ORDER DATE</th>
                                <th>TOTAL AMOUNT</th>
                                <th>ORDER STATUS</th>
                                <th>PAYMENT STATUS</th>
                                <th>PAYMENT METHOD</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($customer_orders as $order): ?>

                            <?php
                            $order_status = $order['OrderStatus'];
                            $payment_status = $order['PaymentStatus'];

                            $order_class = strtolower(
                                preg_replace(
                                    '/[^a-zA-Z0-9]+/',
                                    '-',
                                    $order_status
                                )
                            );

                            $payment_class = strtolower(
                                preg_replace(
                                    '/[^a-zA-Z0-9]+/',
                                    '-',
                                    $payment_status
                                )
                            );
                            ?>

                            <tr>

                                <td class="order-number">
                                    #<?php echo (int)$order['OrderID']; ?>
                                </td>

                                <td>
                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime($order['OrderDate'])
                                    );
                                    ?>
                                    <br>
                                    <small>
                                        <?php
                                        echo date(
                                            "h:i A",
                                            strtotime($order['OrderDate'])
                                        );
                                        ?>
                                    </small>
                                </td>

                                <td class="amount">
                                    Rs. <?php
                                    echo number_format(
                                        $order['TotalAmount'],
                                        2
                                    );
                                    ?>
                                </td>

                                <td>
                                    <span class="status status-<?php
                                        echo e($order_class);
                                    ?>">
                                        <?php echo e($order_status); ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="status status-<?php
                                        echo e($payment_class);
                                    ?>">
                                        <?php echo e($payment_status); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php echo e($order['PaymentMethod']); ?>
                                </td>

                                <td>
                                    <a
                                        href="order_details.php?id=<?php
                                        echo (int)$order['OrderID'];
                                        ?>"
                                        class="view-btn"
                                    >
                                        👁 VIEW DETAILS
                                    </a>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <div class="empty-icon">🛍️</div>

                    <h2>No Orders Yet</h2>

                    <p>
                        You haven't placed any orders yet.
                        Explore our fashion collection and
                        discover something you love.
                    </p>

                    <a href="products.php" class="shop-btn">
                        EXPLORE PRODUCTS →
                    </a>

                </div>

            <?php endif; ?>

        </main>

        <footer class="page-footer">
            <strong>FASHIONFLAIR</strong>

            <p>
                Discover your style. Express yourself.
            </p>
        </footer>

    </div>

</div>

</body>
</html>
