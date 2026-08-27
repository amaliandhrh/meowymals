<?php

$conn = new mysqli("localhost", "root", "", "inseat_food_ordering_system_");

$data = null;

if(isset($_POST['search'])){

    $seat = $_POST['seat'];

    $result = $conn->query("
        SELECT
            c.Customer_Name,
            c.Phone_Number,
            c.Seat_Number,
            o.Order_ID,
            o.Order_Status,
            p.Total_Amount,
            p.Payment_Status,
            p.Payment_Method
        FROM Customer c
        JOIN `Order` o ON c.Customer_ID = o.Customer_ID
        LEFT JOIN Payment p ON o.Order_ID = p.Order_ID
        WHERE c.Seat_Number='$seat'
        ORDER BY o.Order_ID DESC
        LIMIT 1
    ");

    if($result->num_rows > 0){
        $data = $result->fetch_assoc();
    }
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Check Order Status</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background: linear-gradient(135deg,#ffd6e0,#cde7ff,#fff0c9);
    font-family: "Poppins", Arial, sans-serif;
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
}

.card-box{
    background:rgba(255,255,255,0.90);
    padding:25px;
    border-radius:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.12);
    margin-top:25px;
}

.form-control{
    border-radius:15px;
    border:2px solid #fbcfe8;
}

.btn-pastel{
    background:linear-gradient(135deg,#a8d8ff,#cdb4db);
    border:none;
    color:#333;
    border-radius:15px;
    font-weight:700;
}

.badge-box{
    display:inline-block;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    margin-bottom:5px;
}

.pink{
    background:#ffd6e0;
}

.green{
    background:#d8f3dc;
}

.blue{
    background:#a8d8ff;
}

.yellow{
    background:#fff0c9;
}

.food-item{
    background:#ffd6e0;
    padding:8px 12px;
    border-radius:15px;
    display:inline-block;
    margin-bottom:5px;
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

<h2 class="title">🎬 Check Order Status</h2>

<div class="card-box">

<form method="POST">

<label>Seat Number</label>

<input
class="form-control mb-3"
name="seat"
placeholder="Example: A1"
required>

<button
class="btn btn-pastel w-100"
name="search">

Search Order

</button>

</form>

</div>

<?php if($data){ ?>

<div class="card-box">

<h4>Customer Information</h4>

<p>
<strong>Name:</strong>
<?= $data['Customer_Name'] ?>
</p>

<p>
<strong>Phone:</strong>
<?= $data['Phone_Number'] ?>
</p>

<p>
<strong>Seat:</strong>
<?= $data['Seat_Number'] ?>
</p>

<hr>

<h4>Food Ordered</h4>

<?php

$order_id = $data['Order_ID'];

$items = $conn->query("
SELECT
m.Item_Name,
oi.Quantity
FROM Order_Item oi
JOIN MenuItem m
ON oi.MenuItem_ID = m.MenuItem_ID
WHERE oi.Order_ID='$order_id'
");

while($item = $items->fetch_assoc()){

echo "
<div class='food-item'>
".$item['Item_Name']." x".$item['Quantity']."
</div><br>
";

}

?>

<hr>

<h4>Order Information</h4>

<p>
<strong>Total Amount:</strong>

<span class="badge-box green">
RM <?= number_format($data['Total_Amount'] ?? 0,2) ?>
</span>

</p>

<p>
<strong>Payment Method:</strong>

<span class="badge-box yellow">
<?= $data['Payment_Method'] ?? 'Not Available' ?>
</span>

</p>

<p>
<strong>Payment Status:</strong>

<span class="badge-box blue">
<?= $data['Payment_Status'] ?? 'Pending' ?>
</span>

</p>

<p>
<strong>Food Status:</strong>

<span class="badge-box pink">
<?= $data['Order_Status'] ?>
</span>

</p>

</div>

<?php } ?>

</div>

</body>
</html>