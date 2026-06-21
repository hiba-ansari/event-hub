<?php
    session_start();
    $dsn = 'mysql:host=talsprddb02.int.its.rmit.edu.au;dbname=COSC3046_2402_UGRD_1479_G12';
    $user = 'COSC3046_2402_UGRD_1479_G12';
    $pass = 'LtEXbUiTF7Fm';
    $conn = new PDO($dsn, $user, $pass);
    $sql = "SELECT ShoppingCart.EventID, NumTickets, Price, EventName, EventImage 
            FROM ShoppingCart 
            JOIN Events ON ShoppingCart.EventID = Events.EventID
            Where UserID='1'";
    $result = $conn->query($sql);
    $cart = $result->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@600&family=Poppins:wght@300;400;600&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<div class='topnav'>
        <div class='column left'>
            <a href='../homePage/homepage.php' class="logo" style='font-size:40px;font-weight:600'><img src='../images/logo.png' style='width:50px;vertical-align:middle'> Local Event Hub</a>
        </div>
        <div class='column right'>
            <p class='nav'><a href="../profilePage/account.php">Account</a></p>
            <?php
                if (isset($_SESSION['userID'])){
                    echo "
                    <p class='nav'><a href=\"../shoppingCart/shopping-cart.php\">Cart</a></p>
                    <p class='nav'><a href=\"../calendar/events.php\">My Events</a></p>";
                }
            ?>
            <p class='nav'><a href="../discussion/discussion.php">Discussions</a></p>
            <p class='nav'><a href="../searchPage/search.php">Search</a></p>
        </div>
    </div>
<div class="main-container">
    <!-- Cart Section -->
    <div class="cart-section">
        <div class="cart-header">
            <h2>Shopping cart</h2>
        </div>

        <!-- Cart Items -->
        <?php
if (!empty($cart)) {  
    foreach ($cart as $item) {
        $eventImage = $item['EventImage'];
        $tickets = $item['NumTickets'];
        $price = $item['Price'];
        $eventName = $item['EventName']; 
        $eventID = $item['EventID'];
        $itemTotal = number_format($tickets * $price, 2);

        echo "
            <div class=\"cart-card\">
                <div class=\"cart-items\">
                    <span class=\"event-id\" style=\"display:none;\">$eventID</span>
                    <span class=\"price\" style=\"display:none;\">$price</span>

                    <img src=\"$eventImage\" alt=\"$eventName\">
                    <div>
                        <h3>$eventName</h3>
                        <p>No idea if this is needed</p>
                    </div>
                    <div class=\"quantity\">
                        <input type=\"number\" value=\"$tickets\" min='1'>
                    </div>
                    <p class=\"item-total\">\$$itemTotal</p>
                    <button class=\"delete-btn\">Delete</button>
                </div>
            </div>
            ";
    }
}   
?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    function updateCartTotal() {
        let cartTotal = 0;
        document.querySelectorAll('.cart-items').forEach(item => {
            const itemTotalText = item.querySelector('.item-total').textContent.replace('$', '');
            const itemTotal = parseFloat(itemTotalText);
            cartTotal += itemTotal;
        });
        document.querySelectorAll('.total-price').forEach(totalPriceElement => {
            totalPriceElement.textContent = `$${cartTotal.toFixed(2)}`;
        });
    }
    const quantityInputs = document.querySelectorAll('.quantity input');
    quantityInputs.forEach(input => {
        input.addEventListener('input', function() {
            const tickets = parseInt(this.value);
            const cartItem = this.closest('.cart-items');
            const priceElement = cartItem.querySelector('.price');
            const eventIDElement = cartItem.querySelector('.event-id');
            const price = parseFloat(priceElement.textContent.replace('$', ''));
            const eventID = parseInt(eventIDElement.textContent);

            const itemTotal = (tickets * price).toFixed(2);
            cartItem.querySelector('.item-total').textContent = `$${itemTotal}`;
            updateCartTotal();

            fetch('update-cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `eventID=${eventID}&quantity=${tickets}`
            })
        });
    });
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cartCard = this.closest('.cart-card');
            const eventIDElement = cartCard.querySelector('.event-id');
            const eventID = parseInt(eventIDElement.textContent);

            fetch('delete-cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `eventID=${eventID}`
            })
            cartCard.remove();
            updateCartTotal();
        });
    });

    updateCartTotal();
});


</script>

    <!-- Card Details Section -->
    <div class="card-section">
    <h2>Card Details</h2>
    <form>
        <div class="card-details">
            <p>Card Type</p>
            <img src="../images/Mastercard-logo.png" alt="MasterCard">
            <img src="../images/VISA-logo.png" alt="Visa">
        </div>
        <div class="card-details">
            <label for="name">Name on card</label><br>
            <input type="text" id="name" placeholder="Name" required>
        </div>
        <div class="card-details">
            <label for="card-number">Card Number</label><br>
            <input type="tel" id="card-number" placeholder="1111222233334444" maxlength="16" pattern="\d{16}" title="Please enter a 16-digit card number" required>
        </div>
        <div class="card-details" style="display: flex; justify-content: space-between;">
            <div>
                <label for="expiry">Expiration Date</label><br>
                <input type="tel" id="expiry" placeholder="MMYY" maxlength="4" pattern="\d{4}" title="Please enter expiration date in MMYY format" required>
            </div>
            <div>
                <label for="cvv">CVV</label><br>
                <input type="tel" id="cvv" placeholder="123" maxlength="3" pattern="\d{3}" title="Please enter a 3-digit CVV" required>
            </div>
        </div>
        <button type="submit">Submit</button>
    </form>

        <div class="cart-summary">
            <p>Total (Tax incl.)</p>
            <p class="total-price">$0.00</p>
        </div>
        <a href="../shoppingCart/payment-confirmed.html" class="checkout-btn t">Checkout</a>
    </div>
</div>

    <!-- Footer Section -->
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
