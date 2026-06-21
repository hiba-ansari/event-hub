<?php
session_start();

$products = [
    1 => ["name" => "Paid Event", "price" => 10.00, "description" => "This is a paid event", "image" => "../images/canyon.jpg"],
    2 => ["name" => "Free Event", "price" => 0.00, "description" => "This is a free event", "image" => "../images/image.jpg"],
    3 => ["name" => "Placeholder Paid Event", "price" => 20.00, "description" => "This is a placeholder paid event", "image" => "../images/canyon.jpg"],
    
];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    if (isset($products[$product_id])) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $products[$product_id]['name'],
                'price' => $products[$product_id]['price'],
                'quantity' => $quantity,
                'image' => $products[$product_id]['image']
            ];
        }
    }
}

if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    if (isset($_SESSION['cart'][$product_id])) {
        if ($_SESSION['cart'][$product_id]['quantity'] > 1) {
            $_SESSION['cart'][$product_id]['quantity'] -= 1;
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = 0;
        }
    }
}

if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    if ($quantity < 0) {
        $_SESSION['cart'][$product_id]['quantity'] = 0;
    } else {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    }
    exit();
}

$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@600&family=Poppins:wght@300;400;600&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <script>
        function updateCart(productId, quantity) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    location.reload();
                }
            };
            xhr.send("product_id=" + productId + "&quantity=" + quantity);
        }
    </script>
</head>
<body>
    <div class='topnav'>
        <div class='column left'>
            <a href='../homePage/homepage.html' class="logo" style='font-size:40px;font-weight:600'><img src='../images/logo.png' style='width:50px;vertical-align:middle'> Local Event Hub</a>
        </div>
        <div class='column right'>
            <p class='nav'><a href="../profilePage/account.php">Account</a></p>
            <p class='nav'><a href="../calendar/events.php">My Events</a></p>
            <p class='nav'><a href="../groupsPage/groups.html">Groups</a></p>
            <p class='nav'><a href="../searchPage/search.php">Search</a></p>
        </div>
    </div>

    <div class="cart-section" style="display: flex; justify-content: space-between;">
        <div class="cart-items-container" style="width: 60%;">
            <div class="cart-header">
                <h2>Shopping cart</h2>
                <p>You have <?php echo count($_SESSION['cart']); ?> items in your cart</p>
            </div>

            <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                <div class="cart-card">
                    <div class="cart-items">
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                        <div>
                            <h3><?php echo $item['name']; ?></h3>
                            <p><?php echo $products[$product_id]['description']; ?></p>
                        </div>
                        <div class="quantity">
                            <input type="number" name="quantities[<?php echo $product_id; ?>]" value="<?php echo $item['quantity']; ?>" onchange="updateCart(<?php echo $product_id; ?>, this.value)">
                        </div>
                        <p><?php echo $item['price'] > 0 ? '$' . number_format($item['price'], 2) : 'Free'; ?></p>
                        <a href="?remove=<?php echo $product_id; ?>" class="delete-btn"><img src="../images/trash.png" alt="Delete" style="width: 20px; height: 20px;"></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card-section" style="width: 35%;">
            <h2>Card Details</h2>
            <div class="card-details">
                <p>Card Type</p>
                <img src="../images/Mastercard-logo.png" alt="MasterCard">
                <img src="../images/VISA-logo.png" alt="Visa">
                <button>See All</button>
            </div>
            <div class="card-details">
                <label for="name">Name on card</label><br>
                <input type="text" id="name" placeholder="Name">
            </div>
            <div class="card-details">
                <label for="card-number">Card Number</label><br>
                <input type="text" id="card-number" placeholder="1111 2222 3333 4444">
            </div>
            <div class="card-details" style="display: flex; justify-content: space-between;">
                <div>
                    <label for="expiry">Expiration Date</label><br>
                    <input type="text" id="expiry" placeholder="mm/yy">
                </div>
                <div>
                    <label for="cvv">CVV</label><br>
                    <input type="text" id="cvv" placeholder="123">
                </div>
            </div>
            <div class="cart-summary">
                <p>Subtotal</p>
                <p>$<?php echo number_format($subtotal, 2); ?></p>
            </div>
            <div class="cart-summary">
                <p>Total (Tax incl.)</p>
                <p>$<?php echo number_format($subtotal, 2); ?></p>
            </div>
            <button class="checkout-btn">$<?php echo number_format($subtotal, 2); ?> Checkout</button>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-column footer-logo">
                <img src="../images/logo.png" alt="Logo">
            </div>
            <div class="footer-column">
                <h3>Events</h3>
                <a href="../groupsPage/groups.html">Groups</a>
                <a href="../searchPage/search.php">Search</a>
            </div>
            <div class="footer-column">
                <h3>Account</h3>
                <a href="../profilePage/account.php">Account</a>
                <a href="../calendar/events.html">My Events</a>
                <a href="../profilePage/login.php"> Login </a>
            </div>
        </div>
        <p style="color: #202335; position: absolute; margin: 0; transform: translateX(50%); right: 50%; bottom: 5%;">Contact Group 9 - 
            <a href="mailto:s4100892@student.rmit.edu.au" style="color: inherit;">Hiba Ansari (s4100892)</a>
            ,
            <a href="mailto:s3842127@student.rmit.edu.au" style="color: inherit;">Robert Vo-Ho (s3842127)</a>
            ,
            <a href="mailto:s4038608@student.rmit.edu.au" style="color: inherit;">Mahita Jain (s4038608)</a>
            ,
            <a href="mailto:s4026932@student.rmit.edu.au" style="color: inherit;">Hami Faizal (s4026932)</a>
        </p>
    </footer>
</body>
</html>
