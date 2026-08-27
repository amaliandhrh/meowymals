<?php
$conn = new mysqli("localhost", "root", "", "inseat_food_ordering_system_");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $seat = $_POST['seat'];
    $payment_method = $_POST['payment_method'];

    $conn->query("
        INSERT INTO Customer (Customer_Name, Phone_Number, Seat_Number)
        VALUES ('$name', '$phone', '$seat')
    ");

    $customer_id = $conn->insert_id;

    $conn->query("
        INSERT INTO `Order` (Customer_ID, Order_Status)
        VALUES ('$customer_id', 'Pending')
    ");

    $order_id = $conn->insert_id;

    $total = 0;

    foreach ($_POST['qty'] as $menu_id => $qty) {

        if ($qty > 0) {

            $menu_result = $conn->query("
                SELECT * FROM MenuItem
                WHERE MenuItem_ID='$menu_id'
            ");

            $menu = $menu_result->fetch_assoc();

            $price = $menu['Price'];
            $subtotal = $price * $qty;
            $total += $subtotal;

            $conn->query("
                INSERT INTO Order_Item (Order_ID, MenuItem_ID, Quantity, Subtotal)
                VALUES ('$order_id', '$menu_id', '$qty', '$subtotal')
            ");
        }
    }

    $conn->query("
        INSERT INTO Payment (Order_ID, Total_Amount, Payment_Status, Payment_Method)
        VALUES ('$order_id', '$total', 'Paid', '$payment_method')
    ");

    header("Location: receipt.php?order_id=$order_id");
    exit();
}

$menus = $conn->query("SELECT * FROM MenuItem");
?>

<!DOCTYPE html>
<html>
<head>
<title>Customer Order</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg,#ffd6e0,#cde7ff,#fff0c9);
    font-family: "Poppins", Arial, sans-serif;
    min-height:100vh;
}

.main-container{
    max-width:850px;
    margin:auto;
}

.title{
    text-align:center;
    margin-top:25px;
    color:#3f3f46;
    font-weight:800;
    letter-spacing:1px;
}

.subtitle{
    text-align:center;
    color:#6b7280;
    margin-bottom:18px;
}

.hero-img{
    width:100%;
    max-width:720px;
    border-radius:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
    border:6px solid white;
}

.card-box{
    background:rgba(255,255,255,0.90);
    padding:25px;
    border-radius:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.12);
    margin-top:25px;
    border:2px solid #ffffff;
}

.form-control{
    border-radius:15px;
    border:2px solid #fbcfe8;
    padding:12px;
}

.form-control:focus{
    border-color:#93c5fd;
    box-shadow:0 0 0 0.2rem rgba(147,197,253,0.25);
}

.btn-pastel{
    background:linear-gradient(135deg,#a8d8ff,#cdb4db);
    border:none;
    color:#333;
    border-radius:18px;
    padding:12px;
    font-weight:700;
    transition:0.3s;
}

.btn-pastel:hover{
    transform:translateY(-2px);
    background:linear-gradient(135deg,#90cfff,#b892cc);
}

.table{
    border-radius:18px;
    overflow:hidden;
    text-align:center;
}

.table thead{
    background:#cdb4db;
    color:white;
}

.table tbody tr:nth-child(odd){
    background:#fff7fb;
}

.table tbody tr:nth-child(even){
    background:#f0f9ff;
}

.table tbody tr:hover{
    background:#e0f7ff;
}

.food-badge{
    display:inline-block;
    background:#ffd6e0;
    padding:8px 16px;
    border-radius:20px;
    font-weight:700;
    color:#444;
    min-width:150px;
}

.price-badge{
    display:inline-block;
    background:#d8f3dc;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    color:#245c36;
}

.subtotal-badge{
    display:inline-block;
    background:#fff0c9;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    color:#7a5c00;
}

.qty-box{
    width:85px;
    margin:auto;
    text-align:center;
}

.section-title{
    background:#fff0c9;
    padding:10px 15px;
    border-radius:18px;
    font-weight:800;
    color:#444;
    display:inline-block;
    margin-bottom:12px;
}

.total-box{
    background:#d8f3dc;
    padding:15px;
    border-radius:20px;
    text-align:right;
    font-weight:800;
    font-size:22px;
    color:#245c36;
    margin-top:15px;
}
.top-nav{
    margin-top:25px;
    margin-bottom:20px;
    text-align:left;
}

.home-card{
    display:inline-block;
    background:linear-gradient(135deg,#ffd6e0,#cdb4db) !important;
    color:#444 !important;
    padding:14px 28px !important;
    border-radius:25px !important;
    text-decoration:none !important;
    font-weight:800 !important;
    box-shadow:0 8px 18px rgba(0,0,0,0.15) !important;
    border:4px solid white !important;
    transition:0.3s !important;
}

.home-card:hover{
    background:linear-gradient(135deg,#cde7ff,#fff0c9) !important;
    color:#444 !important;
    transform:translateY(-3px);
}

</style>
</head>

<body>

<div class="container main-container">

<div class="top-nav">
    <a href="index.php" class="home-card">🏠 Back Home</a>
</div>
<h2 class="title">🍿 Customer Food Order</h2>
<p class="subtitle">Order your cinema snacks directly from your seat 🎬</p>

<div class="text-center mb-3">
    <img src="images/cinema_seat.jpeg" class="hero-img">
</div>

<div class="card-box">

<form method="POST">

<span class="section-title">🎟 Customer Details</span>

<input class="form-control mb-2" name="name" placeholder="Customer Name" required>

<input class="form-control mb-2" name="phone" placeholder="Phone Number" required>

<input class="form-control mb-3" name="seat" placeholder="Seat Number, example: A1" required>

<span class="section-title">🍿 Choose Your Food</span>

<table class="table table-hover mt-2">
<thead>
<tr>
    <th>Food</th>
    <th>Price</th>
    <th>Quantity</th>
    <th>Subtotal</th>
</tr>
</thead>

<tbody>
<?php while($row = $menus->fetch_assoc()) { ?>
<tr>
    <td>
        <span class="food-badge">
            <?= $row['Item_Name'] ?>
        </span>
    </td>

    <td>
        <span class="price-badge">
            RM <?= number_format($row['Price'], 2) ?>
        </span>
    </td>

    <td>
        <input type="number"
               name="qty[<?= $row['MenuItem_ID'] ?>]"
               class="form-control qty-box"
               value="0"
               min="0"
               data-price="<?= $row['Price'] ?>"
               oninput="calculateTotal()">
    </td>

    <td>
        <span class="subtotal-badge subtotal">
            RM 0.00
        </span>
    </td>
</tr>
<?php } ?>
</tbody>
</table>

<div class="total-box">
    Total: <span id="grandTotal">RM 0.00</span>
</div>

<br>

<span class="section-title">💳 Payment Method</span>

<select class="form-control mb-3" name="payment_method" required>
    <option value="">Choose Payment Method</option>
    <option value="Cash">Cash</option>
    <option value="Debit Card">Debit Card</option>
    <option value="Online Banking">Online Banking</option>
    <option value="E-Wallet">E-Wallet</option>
</select>

<button class="btn btn-pastel w-100" type="submit">
    Place Order & Pay
</button>

</form>

</div>

<br>

</div>

<script>
function calculateTotal(){
    let total = 0;

    document.querySelectorAll('.qty-box').forEach(function(input){
        let price = parseFloat(input.dataset.price);
        let qty = parseInt(input.value) || 0;
        let subtotal = price * qty;

        total += subtotal;

        let row = input.closest('tr');
        row.querySelector('.subtotal').innerText = 'RM ' + subtotal.toFixed(2);
    });

    document.getElementById('grandTotal').innerText = 'RM ' + total.toFixed(2);
}
</script>

</body>
</html>