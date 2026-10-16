<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/db_connect.php";

/* Only logged-in users can view order details. */
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['UserID'];
$is_admin = isset($_SESSION['Role']) && $_SESSION['Role'] === 'Admin';

if (!isset($_GET['id']) || !ctype_digit((string) $_GET['id']) || (int) $_GET['id'] < 1) {
    header("Location: order_history.php");
    exit;
}

$order_id = (int) $_GET['id'];
$message = "";
$message_type = "";

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* Update order status: admin only. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!$is_admin) {
        http_response_code(403);
        exit('Access denied. Only an administrator can update order status.');
    }

    $allowed_statuses = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    $new_status = trim($_POST['OrderStatus'] ?? '');

    if (in_array($new_status, $allowed_statuses, true)) {
        $update_stmt = $conn->prepare("UPDATE orders SET OrderStatus = ? WHERE OrderID = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("si", $new_status, $order_id);
            if ($update_stmt->execute() && $update_stmt->affected_rows >= 0) {
                $update_stmt->close();
                header("Location: order_details.php?id=" . $order_id . "&updated=1");
                exit;
            }
            $update_stmt->close();
        }
        $message = "The order status could not be updated. Please try again.";
        $message_type = "error";
    } else {
        $message = "Please select a valid order status.";
        $message_type = "error";
    }
}

/* Fetch the order. Customers can only see their own orders. */
if ($is_admin) {
    $order_stmt = $conn->prepare("\
        SELECT o.OrderID, o.CustomerID, o.OrderDate, o.TotalAmount,\
               o.OrderStatus, o.ShippingAddress, o.PaymentMethod, o.PaymentStatus,\
               c.CustomerName, c.Email, c.Phone, c.City, c.Address\
        FROM orders o\
        INNER JOIN customers c ON c.CustomerID = o.CustomerID\
        WHERE o.OrderID = ?\
        LIMIT 1\
    ");
    $order_stmt->bind_param("i", $order_id);
} else {
    $order_stmt = $conn->prepare("\
        SELECT o.OrderID, o.CustomerID, o.OrderDate, o.TotalAmount,\
               o.OrderStatus, o.ShippingAddress, o.PaymentMethod, o.PaymentStatus,\
               c.CustomerName, c.Email, c.Phone, c.City, c.Address\
        FROM orders o\
        INNER JOIN customers c ON c.CustomerID = o.CustomerID\
        WHERE o.OrderID = ? AND c.UserID = ?\
        LIMIT 1\
    ");
    $order_stmt->bind_param("ii", $order_id, $user_id);
}

if (!$order_stmt || !$order_stmt->execute()) {
    http_response_code(500);
    exit('Unable to load order details. Please check your database connection and table structure.');
}

$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();
$order_stmt->close();

if (!$order) {
    http_response_code(404);
    exit('Order not found, or you do not have permission to view it.');
}

if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $message = "Order status updated successfully.";
    $message_type = "success";
}

/* Fetch the products in this order. */
$items_stmt = $conn->prepare("\
    SELECT od.OrderDetailID, od.ProductID, od.Quantity, od.UnitPrice, od.SubTotal,\
           p.ProductName, p.Brand, p.Size, p.Color, p.Image\
    FROM order_details od\
    INNER JOIN products p ON p.ProductID = od.ProductID\
    WHERE od.OrderID = ?\
    ORDER BY od.OrderDetailID ASC\
");

if (!$items_stmt) {
    http_response_code(500);
    exit('Unable to load order items. Please check your database table names.');
}

$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = [];
$items_total = 0.0;

while ($item = $items_result->fetch_assoc()) {
    $order_items[] = $item;
    $items_total += (float) $item['SubTotal'];
}
$items_stmt->close();

function status_class($status): string {
    $value = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', (string) $status));
    return $value !== '' ? $value : 'unknown';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo (int) $order['OrderID']; ?> | FashionFlair</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            background-image: linear-gradient(rgba(12,12,12,.76), rgba(12,12,12,.76)), url("images/summer dress.png");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        a { text-decoration: none; }
        .page-wrap { min-height: 100vh; padding: 38px 18px 55px; }
        .container { width: 100%; max-width: 1120px; margin: 0 auto; }
        .brand { text-align: center; color: #fff; font-size: 22px; font-weight: 700; letter-spacing: 6px; margin-bottom: 14px; }
        .brand strong { color: #d4af37; }
        .gold-line { width: 100px; height: 3px; margin: 0 auto 30px; background: #d4af37; border-radius: 4px; }
        .card { padding: 34px; background: rgba(255,255,255,.97); border: 1px solid rgba(212,175,55,.55); border-radius: 18px; box-shadow: 0 22px 60px rgba(0,0,0,.32); }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap; margin-bottom: 28px; }
        .eyebrow { color: #a98517; font-size: 11px; font-weight: bold; letter-spacing: 2.5px; }
        h1 { margin: 10px 0; font-size: 32px; color: #171717; }
        .muted { color: #777; font-size: 14px; line-height: 1.7; margin: 0; }
        .btn { display: inline-block; border: 0; border-radius: 7px; padding: 12px 17px; font-size: 12px; font-weight: bold; cursor: pointer; transition: .25s; }
        .btn-dark { background: #171717; color: #fff; }
        .btn-dark:hover { background: #d4af37; color: #111; }
        .btn-gold { background: #d4af37; color: #111; }
        .btn-gold:hover { background: #171717; color: #fff; }
        .message { margin: 0 0 22px; padding: 13px 16px; border-radius: 8px; font-size: 14px; font-weight: bold; }
        .message.success { background: #e8f8ec; color: #176a32; border: 1px solid #a9dfb7; }
        .message.error { background: #ffebeb; color: #9c1c1c; border: 1px solid #efb0b0; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 15px; margin: 24px 0 32px; }
        .summary { padding: 20px; background: #fff; border: 1px solid #ececec; border-left: 4px solid #d4af37; border-radius: 10px; }
        .summary span { display: block; margin-bottom: 8px; color: #777; font-size: 11px; font-weight: bold; letter-spacing: 1px; }
        .summary strong { font-size: 17px; color: #181818; overflow-wrap: anywhere; }
        .status { display: inline-block; padding: 7px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .status-pending { background: #fff0c9; color: #825b00; }
        .status-confirmed, .status-processing { background: #e5efff; color: #2156a5; }
        .status-shipped { background: #eee7ff; color: #6238a1; }
        .status-delivered, .status-paid { background: #ddf6e5; color: #176b36; }
        .status-cancelled, .status-failed { background: #ffe2e2; color: #a11e1e; }
        .status-unknown { background: #eee; color: #555; }
        .section { margin-top: 28px; }
        .section-title { margin: 0 0 7px; font-size: 21px; }
        .section-subtitle { margin: 0 0 18px; color: #888; font-size: 13px; line-height: 1.6; }
        .info-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 14px; }
        .info-box { padding: 17px; background: #fff; border: 1px solid #ededed; border-radius: 9px; min-width: 0; }
        .info-box span { display: block; margin-bottom: 7px; color: #888; font-size: 10px; font-weight: bold; letter-spacing: 1px; }
        .info-box strong { color: #333; font-size: 14px; line-height: 1.7; overflow-wrap: anywhere; }
        .items-wrap { overflow-x: auto; border: 1px solid #e9e9e9; border-radius: 10px; }
        .items-table { width: 100%; min-width: 720px; border-collapse: collapse; background: #fff; }
        .items-table th { padding: 14px 12px; text-align: left; background: #171717; color: #d4af37; font-size: 10px; letter-spacing: 1px; }
        .items-table td { padding: 14px 12px; border-bottom: 1px solid #eee; color: #444; font-size: 13px; vertical-align: middle; }
        .items-table tbody tr:last-child td { border-bottom: 0; }
        .product-cell { display: flex; align-items: center; gap: 12px; min-width: 220px; }
        .product-cell img, .no-image { width: 58px; height: 65px; object-fit: cover; border-radius: 7px; background: #f2f2f2; flex-shrink: 0; }
        .no-image { display: flex; align-items: center; justify-content: center; font-size: 23px; }
        .product-name { display: block; margin-bottom: 5px; font-weight: bold; color: #222; }
        .product-meta { color: #888; font-size: 11px; line-height: 1.7; }
        .line-total { color: #9a7918; font-weight: bold; white-space: nowrap; }
        .total-box { max-width: 420px; margin: 20px 0 0 auto; padding: 20px; background: #fff; border: 1px solid #e9e9e9; border-radius: 10px; }
        .total-row { display: flex; justify-content: space-between; gap: 15px; padding: 8px 0; color: #777; font-size: 13px; }
        .total-row strong { color: #333; text-align: right; }
        .total-divider { height: 1px; margin: 10px 0; background: #e8e8e8; }
        .grand-total { display: flex; justify-content: space-between; gap: 15px; color: #111; font-size: 15px; font-weight: bold; }
        .grand-total strong { color: #a27f13; text-align: right; }
        .admin-box { margin-top: 28px; padding: 20px; border: 1px solid #eadcae; background: #fffaf0; border-radius: 10px; }
        .admin-form { display: flex; align-items: end; gap: 12px; flex-wrap: wrap; }
        .admin-form label { display: block; margin-bottom: 8px; font-size: 12px; font-weight: bold; }
        .admin-form select { min-width: 220px; padding: 12px; border: 1px solid #ddd; border-radius: 7px; background: #fff; }
        .bottom-actions { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; margin-top: 30px; }
        .footer { margin-top: 25px; text-align: center; color: #fff; font-size: 12px; }
        .footer strong { color: #d4af37; letter-spacing: 3px; }
        @media (max-width: 700px) {
            .page-wrap { padding: 24px 11px 35px; }
            .card { padding: 22px 15px; }
            h1 { font-size: 26px; }
            .summary-grid { grid-template-columns: 1fr; }
            .info-grid { grid-template-columns: 1fr; }
            .topbar { align-items: flex-start; }
            .admin-form { align-items: stretch; flex-direction: column; }
            .admin-form select, .admin-form .btn { width: 100%; }
            .total-box { max-width: 100%; }
        }
    </style>
</head>
<body>
<div class="page-wrap">
    <div class="container">
        <div class="brand">FASHION<strong>FLAIR</strong></div>
        <div class="gold-line"></div>

        <main class="card">
            <div class="topbar">
                <div>
                    <span class="eyebrow">FASHIONFLAIR ORDER MANAGEMENT</span>
                    <h1>Order #<?php echo (int) $order['OrderID']; ?></h1>
                    <p class="muted">Review your order, delivery address and purchased products.</p>
                </div>
                <a class="btn btn-dark" href="<?php echo $is_admin ? 'dashboard.php' : 'order_history.php'; ?>">← BACK</a>
            </div>

            <?php if ($message !== ''): ?>
                <div class="message <?php echo e($message_type); ?>"><?php echo e($message); ?></div>
            <?php endif; ?>

            <div class="summary-grid">
                <div class="summary">
                    <span>ORDER STATUS</span>
                    <strong><span class="status status-<?php echo e(status_class($order['OrderStatus'])); ?>"><?php echo e($order['OrderStatus']); ?></span></strong>
                </div>
                <div class="summary">
                    <span>PAYMENT STATUS</span>
                    <strong><span class="status status-<?php echo e(status_class($order['PaymentStatus'])); ?>"><?php echo e($order['PaymentStatus']); ?></span></strong>
                </div>
                <div class="summary">
                    <span>ORDER TOTAL</span>
                    <strong>Rs. <?php echo number_format((float) $order['TotalAmount'], 2); ?></strong>
                </div>
            </div>

            <section class="section">
                <h2 class="section-title">📋 Order Information</h2>
                <p class="section-subtitle">Order date, payment and shipping details.</p>
                <div class="info-grid">
                    <div class="info-box"><span>ORDER DATE</span><strong><?php echo e(date('d M Y, h:i A', strtotime($order['OrderDate']))); ?></strong></div>
                    <div class="info-box"><span>PAYMENT METHOD</span><strong><?php echo e($order['PaymentMethod']); ?></strong></div>
                    <div class="info-box"><span>SHIPPING ADDRESS</span><strong><?php echo nl2br(e($order['ShippingAddress'])); ?></strong></div>
                    <div class="info-box"><span>CUSTOMER</span><strong><?php echo e($order['CustomerName']); ?><br><?php echo e($order['Phone']); ?><br><?php echo e($order['Email']); ?></strong></div>
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">🛍️ Products in This Order</h2>
                <p class="section-subtitle">Items and quantities included in this order.</p>

                <?php if (count($order_items) > 0): ?>
                    <div class="items-wrap">
                        <table class="items-table">
                            <thead>
                            <tr><th>PRODUCT</th><th>UNIT PRICE</th><th>QUANTITY</th><th>SUBTOTAL</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <?php if (!empty($item['Image'])): ?>
                                                <img src="images/<?php echo rawurlencode(basename($item['Image'])); ?>" alt="<?php echo e($item['ProductName']); ?>">
                                            <?php else: ?>
                                                <div class="no-image">🛍️</div>
                                            <?php endif; ?>
                                            <div>
                                                <span class="product-name"><?php echo e($item['ProductName']); ?></span>
                                                <span class="product-meta">Brand: <?php echo e($item['Brand']); ?><br>Size: <?php echo e($item['Size']); ?> · Color: <?php echo e($item['Color']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rs. <?php echo number_format((float) $item['UnitPrice'], 2); ?></td>
                                    <td><?php echo (int) $item['Quantity']; ?></td>
                                    <td class="line-total">Rs. <?php echo number_format((float) $item['SubTotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="total-box">
                        <div class="total-row"><span>Items subtotal</span><strong>Rs. <?php echo number_format($items_total, 2); ?></strong></div>
                        <div class="total-divider"></div>
                        <div class="grand-total"><span>ORDER TOTAL</span><strong>Rs. <?php echo number_format((float) $order['TotalAmount'], 2); ?></strong></div>
                    </div>
                <?php else: ?>
                    <div class="info-box">No product details were found for this order.</div>
                <?php endif; ?>
            </section>

            <?php if ($is_admin): ?>
                <section class="admin-box">
                    <h2 class="section-title">⚙️ Update Order Status</h2>
                    <p class="section-subtitle">This control is available to administrators only.</p>
                    <form method="POST" class="admin-form">
                        <div>
                            <label for="OrderStatus">Order status</label>
                            <select id="OrderStatus" name="OrderStatus" required>
                                <?php foreach (['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $status): ?>
                                    <option value="<?php echo e($status); ?>" <?php echo $order['OrderStatus'] === $status ? 'selected' : ''; ?>><?php echo e($status); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-gold" type="submit" name="update_status">UPDATE STATUS</button>
                    </form>
                </section>
            <?php endif; ?>

            <div class="bottom-actions">
                <a class="btn btn-dark" href="<?php echo $is_admin ? 'dashboard.php' : 'order_history.php'; ?>">← BACK TO <?php echo $is_admin ? 'DASHBOARD' : 'ORDERS'; ?></a>
                <a class="btn btn-gold" href="products.php">🛍️ CONTINUE SHOPPING</a>
            </div>
        </main>

        <footer class="footer"><strong>FASHIONFLAIR</strong><p>Discover your style. Express yourself.</p></footer>
    </div>
</div>
</body>
</html>
