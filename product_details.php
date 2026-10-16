<?php

require_once "config/db_connect.php";
require_once "includes/header.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = (int) $_GET['id'];

$sql = "SELECT 
            p.ProductID,
            p.ProductName,
            p.Brand,
            p.Size,
            p.Color,
            p.Price,
            p.StockQuantity,
            p.Description,
            p.Image,
            c.CategoryName
        FROM products p
        INNER JOIN categories c
        ON p.CategoryID = c.CategoryID
        WHERE p.ProductID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='error-box'>Product not found.</div>";
    require_once "includes/footer.php";
    exit;
}

$product = $result->fetch_assoc();

?>

<section class="product-details-page">

    <div class="container">

        <div class="product-details-card">

            <!-- Product Image -->

            <div class="product-details-image">

                <img 
                    src="images/<?php echo htmlspecialchars($product['Image']); ?>"
                    alt="<?php echo htmlspecialchars($product['ProductName']); ?>"
                >

            </div>


            <!-- Product Information -->

            <div class="product-details-info">

                <span class="product-category">
                    <?php echo htmlspecialchars($product['CategoryName']); ?>
                </span>

                <h1>
                    <?php echo htmlspecialchars($product['ProductName']); ?>
                </h1>

                <p class="product-brand">
                    Brand:
                    <strong>
                        <?php echo htmlspecialchars($product['Brand']); ?>
                    </strong>
                </p>

                <div class="product-price">

                    Rs.
                    <?php echo number_format($product['Price'], 2); ?>

                </div>

                <div class="product-info">

                    <p>
                        <strong>Size:</strong>
                        <?php echo htmlspecialchars($product['Size']); ?>
                    </p>

                    <p>
                        <strong>Color:</strong>
                        <?php echo htmlspecialchars($product['Color']); ?>
                    </p>

                    <p>
                        <strong>Available Stock:</strong>
                        <?php echo $product['StockQuantity']; ?>
                    </p>

                </div>


                <div class="product-description">

                    <h3>Product Description</h3>

                    <p>
                        <?php 
                        echo nl2br(
                            htmlspecialchars($product['Description'])
                        ); 
                        ?>
                    </p>

                </div>


                <!-- Add To Cart -->

                <?php if ($product['StockQuantity'] > 0): ?>

                    <form action="cart.php" method="POST" class="details-cart-form">

                        <input 
                            type="hidden"
                            name="product_id"
                            value="<?php echo $product['ProductID']; ?>"
                        >

                        <label for="quantity">
                            Quantity:
                        </label>

                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            value="1"
                            min="1"
                            max="<?php echo $product['StockQuantity']; ?>"
                            required
                        >

                        <button 
                            type="submit"
                            name="add_to_cart"
                            class="details-cart-btn"
                        >
                            🛒 ADD TO CART
                        </button>

                    </form>

                <?php else: ?>

                    <button class="out-of-stock-btn" disabled>
                        OUT OF STOCK
                    </button>

                <?php endif; ?>


                <!-- Back Button -->

                <a href="products.php" class="back-products-btn">
                    ← BACK TO PRODUCTS
                </a>

            </div>

        </div>

    </div>

</section>

<?php

require_once "includes/footer.php";

?>