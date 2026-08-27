<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'cashier'){
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "inseat_food_ordering_system_");

if(isset($_GET['delete'])){
    $payment_id = $_GET['delete'];

    $conn->query("
        DELETE FROM Payment
        WHERE Payment_ID='$payment_id'
    ");

    header("Location: cashier.php");
    exit();
}

/* SUMMARY COUNTERS */
$totalPayments = $conn->query("SELECT COUNT(*) AS total FROM Payment")->fetch_assoc()['total'];
$totalPaid = $conn->query("SELECT COUNT(*) AS total FROM Payment WHERE Payment_Status='Paid'")->fetch_assoc()['total'];
$totalSales = $conn->query("SELECT SUM(Total_Amount) AS total FROM Payment WHERE Payment_Status='Paid'")->fetch_assoc()['total'];
$totalReceipts = $conn->query("SELECT COUNT(*) AS total FROM Payment")->fetch_assoc()['total'];

$result = $conn->query("
    SELECT 
        p.Payment_ID,
        p.Order_ID,
        p.Total_Amount,
        p.Payment_Status,
        p.Payment_Method,
        c.Customer_Name,
        c.Phone_Number,
        c.Seat_Number
    FROM Payment p
    LEFT JOIN `Order` o ON p.Order_ID = o.Order_ID
    LEFT JOIN Customer c ON o.Customer_ID = c.Customer_ID
    ORDER BY p.Payment_ID DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Cashier Payment Records</title>

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

.summary-payment{
    background:linear-gradient(135deg,#ffd6e0,#fbcfe8);
}

.summary-paid{
    background:linear-gradient(135deg,#d8f3dc,#b8f2c2);
}

.summary-sales{
    background:linear-gradient(135deg,#fff0c9,#ffe29a);
}

.summary-receipt{
    background:linear-gradient(135deg,#cde7ff,#a8d8ff);
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

.amount-badge{
    display:inline-block;
    background:#d8f3dc;
    padding:8px 14px;
    border-radius:20px;
    font-weight:800;
    color:#245c36;
}

.method-badge{
    display:inline-block;
    background:#a8d8ff;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    color:#1e3a5f;
}

.status-paid{
    display:inline-block;
    background:#b8f2c2;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    color:#245c36;
}

.btn-delete{
    background:#ffb3b3;
    border:none;
    color:#7f1d1d;
    border-radius:12px;
    font-weight:700;
}

.btn-print{
    background:linear-gradient(135deg,#fff0c9,#ffd6a5);
    border:none;
    color:#7a5c00;
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

<h2 class="title">💳 Cashier Payment Records</h2>
<p class="subtitle">View completed payments and print customer receipts</p>

<!-- SUMMARY CARDS -->
<div class="row g-3 mb-4">

    <div class="col-md-3">
        <div class="summary-card summary-payment">
            <h2><?= $totalPayments ?></h2>
            <p>💳 Total Payments</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card summary-paid">
            <h2><?= $totalPaid ?></h2>
            <p>✅ Paid Payments</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card summary-sales">
            <h2>RM <?= number_format($totalSales ?? 0, 2) ?></h2>
            <p>💰 Total Sales</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card summary-receipt">
            <h2><?= $totalReceipts ?></h2>
            <p>🧾 Receipts</p>
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
    <th>Payment Method</th>
    <th>Total Amount</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php if($result && $result->num_rows > 0) { ?>

<?php while($row = $result->fetch_assoc()) { ?>
<tr>
    <td>
        <span class="name-badge">
            <?= $row['Customer_Name'] ?? 'Unknown' ?>
        </span>
    </td>

    <td><?= $row['Phone_Number'] ?? '-' ?></td>

    <td>
        <span class="seat-badge">
            <?= $row['Seat_Number'] ?? '-' ?>
        </span>
    </td>

    <td>
        <span class="method-badge">
            <?= $row['Payment_Method'] ?? '-' ?>
        </span>
    </td>

    <td>
        <span class="amount-badge">
            RM <?= number_format($row['Total_Amount'] ?? 0, 2) ?>
        </span>
    </td>

    <td>
        <span class="status-paid">
            <?= $row['Payment_Status'] ?? 'Paid' ?>
        </span>
    </td>

    <td>
        <a href="receipt.php?order_id=<?= $row['Order_ID'] ?>"
           class="btn btn-print btn-sm mb-1"
           target="_blank">
           🖨 Receipt
        </a>

        <a href="cashier.php?delete=<?= $row['Payment_ID'] ?>"
           class="btn btn-delete btn-sm mb-1"
           onclick="return confirm('Delete this payment record?')">
           Delete
        </a>
    </td>
</tr>
<?php } ?>

<?php } else { ?>

<tr>
    <td colspan="7">
        No payment records found yet. Please place an order from the customer page first.
    </td>
</tr>

<?php } ?>

</tbody>
</table>

</div>

</div>

</body>
</html>