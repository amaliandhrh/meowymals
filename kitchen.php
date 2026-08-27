<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'kitchen'){
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "inseat_food_ordering_system_");

if(isset($_GET['preparing'])){
    $order_id = $_GET['preparing'];

    $conn->query("
        UPDATE `Order`
        SET Order_Status='Preparing'
        WHERE Order_ID='$order_id'
    ");

    header("Location: kitchen.php");
    exit();
}

if(isset($_GET['delivered'])){
    $order_id = $_GET['delivered'];

    $conn->query("
        UPDATE `Order`
        SET Order_Status='Delivered'
        WHERE Order_ID='$order_id'
    ");

    header("Location: kitchen.php");
    exit();
}

if(isset($_GET['delete'])){
    $order_id = $_GET['delete'];

    $conn->query("DELETE FROM Payment WHERE Order_ID='$order_id'");
    $conn->query("DELETE FROM Order_Item WHERE Order_ID='$order_id'");
    $conn->query("DELETE FROM `Order` WHERE Order_ID='$order_id'");

    header("Location: kitchen.php");
    exit();
}

/* SUMMARY COUNTERS */
$totalOrders = $conn->query("SELECT COUNT(*) AS total FROM `Order`")->fetch_assoc()['total'];
$totalPending = $conn->query("SELECT COUNT(*) AS total FROM `Order` WHERE Order_Status='Pending'")->fetch_assoc()['total'];
$totalPreparing = $conn->query("SELECT COUNT(*) AS total FROM `Order` WHERE Order_Status='Preparing'")->fetch_assoc()['total'];
$totalDelivered = $conn->query("SELECT COUNT(*) AS total FROM `Order` WHERE Order_Status='Delivered'")->fetch_assoc()['total'];

$result = $conn->query("
    SELECT 
        o.Order_ID,
        o.Order_Status,
        c.Customer_Name,
        c.Phone_Number,
        c.Seat_Number
    FROM `Order` o
    JOIN Customer c ON o.Customer_ID = c.Customer_ID
    ORDER BY o.Order_ID DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Kitchen Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg,#ffd6e0,#cde7ff,#fff0c9);
    font-family: "Poppins", Arial, sans-serif;
    min-height:100vh;
}

.main-container{
    max-width:1100px;
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

.summary-card{
    background:rgba(255,255,255,0.92);
    padding:25px;
    border-radius:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.12);
    border:3px solid white;
    text-align:center;
    height:100%;
}

.summary-card h2{
    font-weight:800;
    color:#3f3f46;
    margin-bottom:8px;
}

.summary-card p{
    margin:0;
    font-weight:800;
    color:#444;
}

.summary-pending{
    background:linear-gradient(135deg,#fff0c9,#ffe29a);
}

.summary-preparing{
    background:linear-gradient(135deg,#cde7ff,#a8d8ff);
}

.summary-delivered{
    background:linear-gradient(135deg,#d8f3dc,#b8f2c2);
}

.summary-total{
    background:linear-gradient(135deg,#ffd6e0,#fbcfe8);
}

.card-box{
    background:rgba(255,255,255,0.90);
    padding:25px;
    border-radius:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.12);
    margin-top:25px;
    border:2px solid #ffffff;
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

.name-badge{
    display:inline-block;
    background:#ffd6e0;
    padding:8px 16px;
    border-radius:20px;
    font-weight:700;
    color:#444;
}

.seat-badge{
    display:inline-block;
    background:#fff0c9;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    color:#7a5c00;
}

.food-badge{
    display:inline-block;
    background:#ffd6e0;
    padding:6px 12px;
    border-radius:18px;
    font-weight:700;
    color:#444;
    margin-bottom:4px;
}

.qty-badge{
    display:inline-block;
    background:#d8f3dc;
    padding:6px 12px;
    border-radius:18px;
    font-weight:700;
    color:#245c36;
    margin-bottom:4px;
}

.status-pending{
    display:inline-block;
    background:#ffe29a;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    color:#6b4e00;
}

.status-preparing{
    display:inline-block;
    background:#a8d8ff;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    color:#1e3a5f;
}

.status-delivered{
    display:inline-block;
    background:#b8f2c2;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    color:#245c36;
}

.btn-preparing{
    background:#a8d8ff;
    border:none;
    color:#1e3a5f;
    border-radius:12px;
    font-weight:700;
}

.btn-delivered{
    background:#b8f2c2;
    border:none;
    color:#245c36;
    border-radius:12px;
    font-weight:700;
}

.btn-delete{
    background:#ffb3b3;
    border:none;
    color:#7f1d1d;
    border-radius:12px;
    font-weight:700;
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

<div class="d-flex justify-content-between mt-3">

    <a href="index.php" class="home-card">
        🏠 Home
    </a>

    <a href="logout.php" class="home-card">
        🚪 Logout
    </a>

</div>

<h2 class="title">👨‍🍳 Kitchen Order Management</h2>
<p class="subtitle">View customer orders and update food preparation status</p>

<!-- SUMMARY CARDS -->
<div class="row g-3 mb-4">

    <div class="col-md-3">
        <div class="summary-card summary-total">
            <h2><?= $totalOrders ?></h2>
            <p>📋 Total Orders</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card summary-pending">
            <h2><?= $totalPending ?></h2>
            <p>⏳ Pending</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card summary-preparing">
            <h2><?= $totalPreparing ?></h2>
            <p>👨‍🍳 Preparing</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card summary-delivered">
            <h2><?= $totalDelivered ?></h2>
            <p>✅ Delivered</p>
        </div>
    </div>

</div>

<div class="card-box">

<table class="table table-hover">
<thead>
<tr>
    <th>Customer</th>
    <th>Phone</th>
    <th>Seat</th>
    <th>Food Ordered</th>
    <th>Quantity</th>
    <th>Food Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php while($row = $result->fetch_assoc()) { ?>
<tr>
    <td>
        <span class="name-badge">
            <?= $row['Customer_Name'] ?>
        </span>
    </td>

    <td><?= $row['Phone_Number'] ?></td>

    <td>
        <span class="seat-badge">
            <?= $row['Seat_Number'] ?>
        </span>
    </td>

    <td>
        <?php
        $order_id = $row['Order_ID'];

        $items = $conn->query("
            SELECT 
                m.Item_Name,
                oi.Quantity
            FROM Order_Item oi
            JOIN MenuItem m ON oi.MenuItem_ID = m.MenuItem_ID
            WHERE oi.Order_ID='$order_id'
        ");

        while($item = $items->fetch_assoc()){
            echo "<span class='food-badge'>" . $item['Item_Name'] . "</span><br>";
        }
        ?>
    </td>

    <td>
        <?php
        $items2 = $conn->query("
            SELECT 
                m.Item_Name,
                oi.Quantity
            FROM Order_Item oi
            JOIN MenuItem m ON oi.MenuItem_ID = m.MenuItem_ID
            WHERE oi.Order_ID='$order_id'
        ");

        while($item2 = $items2->fetch_assoc()){
            echo "<span class='qty-badge'>x" . $item2['Quantity'] . "</span><br>";
        }
        ?>
    </td>

    <td>
        <?php if($row['Order_Status'] == 'Preparing') { ?>
            <span class="status-preparing">Preparing</span>
        <?php } elseif($row['Order_Status'] == 'Delivered') { ?>
            <span class="status-delivered">Delivered</span>
        <?php } else { ?>
            <span class="status-pending">Pending</span>
        <?php } ?>
    </td>

    <td>
        <?php if($row['Order_Status'] != 'Preparing') { ?>
            <a href="kitchen.php?preparing=<?= $row['Order_ID'] ?>"
               class="btn btn-preparing btn-sm mb-1">
               Preparing
            </a>
        <?php } ?>

        <?php if($row['Order_Status'] != 'Delivered') { ?>
            <a href="kitchen.php?delivered=<?= $row['Order_ID'] ?>"
               class="btn btn-delivered btn-sm mb-1">
               Delivered
            </a>
        <?php } ?>

        <a href="kitchen.php?delete=<?= $row['Order_ID'] ?>"
           class="btn btn-delete btn-sm mb-1"
           onclick="return confirm('Delete this order?')">
           Delete
        </a>
    </td>
</tr>
<?php } ?>

</tbody>
</table>

</div>

</div>

</body>
</html>