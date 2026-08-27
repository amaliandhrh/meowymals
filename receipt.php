<div class="top-nav">
    <a href="index.php" class="home-card">🏠 Back Home</a>
</div>
<?php

$conn = new mysqli("localhost", "root", "", "inseat_food_ordering_system_");

if(!isset($_GET['order_id'])){
    die("Invalid Receipt");
}

$order_id = $_GET['order_id'];

$result = $conn->query("
    SELECT
        c.Customer_Name,
        c.Phone_Number,
        c.Seat_Number,
        o.Order_Status,
        p.Total_Amount,
        p.Payment_Status,
        p.Payment_Method
    FROM `Order` o
    JOIN Customer c ON o.Customer_ID = c.Customer_ID
    JOIN Payment p ON o.Order_ID = p.Order_ID
    WHERE o.Order_ID='$order_id'
");

$data = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>

<title>Receipt</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background: linear-gradient(135deg,#ffd6e0,#cde7ff,#fff0c9);
    font-family: "Poppins", Arial, sans-serif;
}

.receipt-container{
    max-width:700px;
    margin:30px auto;
}

.receipt-card{
    background:white;
    border-radius:25px;
    padding:30px;
    box-shadow:0 10px 25px rgba(0,0,0,0.12);
}

.title{
    text-align:center;
    font-weight:800;
    color:#444;
}

.subtitle{
    text-align:center;
    color:#777;
}

.line{
    border-top:2px dashed #ddd;
    margin:20px 0;
}

.food-badge{
    background:#ffd6e0;
    padding:8px 12px;
    border-radius:15px;
    display:inline-block;
    margin-bottom:5px;
    font-weight:700;
}

.status-paid{
    background:#d8f3dc;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
}

.status-food{
    background:#a8d8ff;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
}

.total-box{
    background:#fff0c9;
    padding:15px;
    border-radius:20px;
    text-align:right;
    font-size:22px;
    font-weight:800;
}

.btn-print{
    background:linear-gradient(135deg,#a8d8ff,#cdb4db);
    border:none;
    border-radius:15px;
    font-weight:700;
    width:100%;
    padding:12px;
}

@media print{
    .no-print{
        display:none;
    }

    body{
        background:white;
    }
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

<div class="receipt-container">

<div class="receipt-card">

<h2 class="title">🎬 CINEMA FOOD RECEIPT</h2>

<p class="subtitle">
Thank You For Your Order 🍿
</p>

<div class="line"></div>

<h5>Customer Information</h5>

<p>
<strong>Name:</strong>
<?= $data['Customer_Name'] ?>
</p>

<p>
<strong>Phone:</strong>
<?= $data['Phone_Number'] ?>
</p>

<p>
<strong>Seat Number:</strong>
<?= $data['Seat_Number'] ?>
</p>

<div class="line"></div>

<h5>Food Ordered</h5>

<?php

$items = $conn->query("
SELECT
m.Item_Name,
oi.Quantity,
oi.Subtotal
FROM Order_Item oi
JOIN MenuItem m
ON oi.MenuItem_ID = m.MenuItem_ID
WHERE oi.Order_ID='$order_id'
");

while($item = $items->fetch_assoc()){

echo "
<div class='food-badge'>
".$item['Item_Name']."
 x".$item['Quantity']."
 (RM ".number_format($item['Subtotal'],2).")
</div><br>
";

}

?>

<div class="line"></div>

<h5>Payment Information</h5>

<p>
<strong>Payment Method:</strong>
<?= $data['Payment_Method'] ?>
</p>

<p>
<strong>Payment Status:</strong>

<span class="status-paid">
<?= $data['Payment_Status'] ?>
</span>

</p>

<p>
<strong>Food Status:</strong>

<span class="status-food">
<?= $data['Order_Status'] ?>
</span>

</p>

<div class="line"></div>

<div class="total-box">

Total Amount:
RM <?= number_format($data['Total_Amount'],2) ?>

</div>

<br>

<div class="no-print">

<button
class="btn btn-print"
onclick="window.print()">

🖨 Print Receipt

</button>

</div>

</div>

</div>

</body>
</html>