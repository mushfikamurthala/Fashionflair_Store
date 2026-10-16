<?php
session_start();
require_once "config/db_connect.php";

// Get categories
$sql = "SELECT CategoryID, CategoryName, Description FROM category ORDER BY CategoryID ASC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FashionFlair - Categories</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background:
                linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
                url("https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=2000&q=85");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* NAVBAR */
        .navbar {
            width: 100%;
            background: rgba(255,255,255,0.96);
            padding: 18px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 3px 15px rgba(0,0,0,0.2);
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #d63384;
        }

        .nav-links {
            display: flex;
            gap: 25px;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            transition: 0.3s;
        }

        .nav-links a:hover {
            color: #d63384;
        }

        /* MAIN */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 60px auto;
        }

        .title {
            text-align: center;
            color: white;
            font-size: 42px;
            margin-bottom: 10px;
            text-shadow: 2px 2px 8px black;
        }

        .subtitle {
            text-align: center;
            color: white;
            font-size: 18px;
            margin-bottom: 45px;
        }

        /* CATEGORY GRID */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .category-card {
            min-height: 300px;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            transition: 0.4s;
        }

        .category-card:hover {
            transform: translateY(-10px) scale(1.02);
        }

        .category-card:nth-child(1) {
            background:
                linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.65)),
                url("https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=900&q=80");
            background-size: cover;
            background-position: center;
        }

        .category-card:nth-child(2) {
            background:
                linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.65)),
                url("https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=900&q=80");
            background-size: cover;
            background-position: center;
        }

        .category-card:nth-child(3) {
            background:
                linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.65)),
                url("https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=900&q=80");
            background-size: cover;
            background-position: center;
        }

        .category-card:nth-child(4) {
            background:
                linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.65)),
                url("https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80");
            background-size: cover;
            background-position: center;
        }

        .category-card:nth-child(5) {
            background:
                linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.65)),
                url("https://images.unsplash.com/photo-1559563458-527698bf5295?auto=format&fit=crop&w=900&q=80");
            background-size: cover;
            background-position: center;
        }

        .category-card:nth-child(6) {
            background:
                linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.65)),
                url("https://images.unsplash.com/photo-1523779917675-b6ed3a42a561?auto=format&fit=crop&w=900&q=80");
            background-size: cover;
            background-position: center;
        }

        .card-content {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 25px;
            color: white;
            text-align: center;
        }

        .card-content h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .card-content p {
            font-size: 15px;
            margin-bottom: 18px;
        }

        .view-btn {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(135deg, #ff4f9a, #d63384);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: 0.3s;
        }

        .view-btn:hover {
            background: linear-gradient(135deg, #d63384, #b51767);
            transform: scale(1.05);
        }

        /* MOBILE */
        @media (max-width: 700px) {

            .navbar {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }

            .nav-links {
                gap: 12px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .title {
                font-size: 32px;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">

    <div class="logo">
        FashionFlair
    </div>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="category.php">Categories</a>
        <a href="profile.php">Profile</a>
        <a href="cart.php">Cart 🛒</a>
    </div>

</div>


<!-- MAIN -->
<div class="container">

    <h1 class="title">Our Categories</h1>

    <p class="subtitle">
        Explore FashionFlair's latest fashion collections
    </p>


    <div class="category-grid">

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

            <div class="category-card">

                <div class="card-content">

                    <h2>
                        <?php echo htmlspecialchars($row['CategoryName']); ?>
                    </h2>

                    <p>
                        <?php echo htmlspecialchars($row['Description']); ?>
                    </p>

                    <a
                        class="view-btn"
                        href="products.php?category=<?php echo $row['CategoryID']; ?>"
                    >
                        View Products
                    </a>

                </div>

            </div>

        <?php } ?>

    </div>

</div>

</body>
</html>