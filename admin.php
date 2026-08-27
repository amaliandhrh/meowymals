<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "inseat_food_ordering_system_");

/* ADD MENU */
if(isset($_POST['add_menu'])){
    $item = $_POST['item_name'];
    $price = $_POST['price'];

    $conn->query("
        INSERT INTO MenuItem (Item_Name, Price)
        VALUES ('$item', '$price')
    ");

    header("Location: admin.php");
    exit();
}

/* UPDATE MENU */
if(isset($_POST['update_menu'])){
    $menu_id = $_POST['menu_id'];
    $item_name = $_POST['item_name'];
    $price = $_POST['price'];

    $conn->query("
        UPDATE MenuItem
        SET Item_Name='$item_name',
            Price='$price'
        WHERE MenuItem_ID='$menu_id'
    ");

    header("Location: admin.php");
    exit();
}

/* DELETE MENU */
if(isset($_GET['delete_menu'])){
    $menu_id = $_GET['delete_menu'];

    $conn->query("
        DELETE FROM MenuItem
        WHERE MenuItem_ID='$menu_id'
    ");

    header("Location: admin.php");
    exit();
}

/* DELETE ORDER */
if(isset($_GET['delete_order'])){
    $order_id = $_GET['delete_order'];

    $conn->query("DELETE FROM Payment WHERE Order_ID='$order_id'");
    $conn->query("DELETE FROM Order_Item WHERE Order_ID='$order_id'");
    $conn->query("DELETE FROM `Order` WHERE Order_ID='$order_id'");

    header("Location: admin.php");
    exit();
}

/* SUMMARY COUNTERS */
$totalCustomers = $conn->query("SELECT COUNT(*) AS total FROM Customer")->fetch_assoc()['total'];
$totalMenu = $conn->query("SELECT COUNT(*) AS total FROM MenuItem")->fetch_assoc()['total'];
$totalOrders = $conn->query("SELECT COUNT(*) AS total FROM `Order`")->fetch_assoc()['total'];
$totalPayments = $conn->query("SELECT COUNT(*) AS total FROM Payment")->fetch_assoc()['total'];

$orders = $conn->query("
    SELECT 
        o.Order_ID,
        o.Order_Status,
        c.Customer_Name,
        c.Phone_Number,
        c.Seat_Number,
        p.Total_Amount,
        p.Payment_Status
    FROM `Order` o
    JOIN Customer c ON o.Customer_ID = c.Customer_ID
    LEFT JOIN Payment p ON o.Order_ID = p.Order_ID
    ORDER BY o.Order_ID DESC
");

$menus = $conn->query("SELECT * FROM MenuItem");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

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

.summary-customers{
    background:linear-gradient(135deg,#ffd6e0,#fbcfe8);
}

.summary-menu{
    background:linear-gradient(135deg,#fff0c9,#ffe29a);
}

.summary-orders{
    background:linear-gradient(135deg,#cde7ff,#a8d8ff);
}

.summary-payments{
    background:linear-gradient(135deg,#d8f3dc,#b8f2c2);
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
    background:#ffd6e0;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    display:inline-block;
}

.seat-badge{
    background:#fff0c9;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    display:inline-block;
}

.food-badge{
    background:#ffd6e0;
    padding:6px 12px;
    border-radius:18px;
    font-weight:700;
    display:inline-block;
    margin-bottom:4px;
}

.status-badge{
    background:#a8d8ff;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    display:inline-block;
}

.payment-badge{
    background:#d8f3dc;
    padding:8px 14px;
    border-radius:20px;
    font-weight:700;
    display:inline-block;
}

.btn-pastel{
    background:linear-gradient(135deg,#a8d8ff,#cdb4db);
    border:none;
    color:#333;
    border-radius:15px;
    font-weight:700;
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

.form-control{
    border-radius:15px;
    border:2px solid #fbcfe8;
    padding:12px;
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

<h2 class="title">👨‍💼 Admin Dashboard</h2>
<p class="subtitle">Manage menu, view customer orders, update menu, delete orders and print receipts</p>

<!-- SUMMARY CARDS -->
<div class="row g-3 mb-4">

    <div class="col-md-3">
        <div class="summary-card summary-customers">
            <h2><?= $totalCustomers ?></h2>
            <p>👥 Customers</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card summary-menu">
            <h2><?= $totalMenu ?></h2>
            <p>🍿 Menu Items</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card summary-orders">
            <h2><?= $totalOrders ?></h2>
            <p>📋 Orders</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card summary-payments">
            <h2><?= $totalPayments ?></h2>
            <p>💳 Payments</p>
        </div>
    </div>

</div>

<!-- MENU MANAGEMENT -->
<div class="card-box">

<span class="section-title">🍿 Menu Management</span>

<form method="POST" class="row mb-3">
    <div class="col-md-5">
        <input class="form-control" name="item_name" placeholder="Food Name" required>
    </div>

    <div class="col-md-4">
        <input class="form-control" name="price" placeholder="Price" required>
    </div>

    <div class="col-md-3">
        <button class="btn btn-pastel w-100" name="add_menu">
            Add Menu
        </button>
    </div>
</form>

<table class="table table-hover">
<thead>
<tr>
    <th>Food Name</th>
    <th>Price</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
<?php while($menu = $menus->fetch_assoc()) { ?>
<tr>
    <td>
        <form method="POST" id="form<?= $menu['MenuItem_ID'] ?>">
            <input type="hidden" name="menu_id" value="<?= $menu['MenuItem_ID'] ?>">

            <input class="form-control"
                   name="item_name"
                   value="<?= $menu['Item_Name'] ?>"
                   required>
        </form>
    </td>

    <td>
        <input class="form-control"
               name="price"
               form="form<?= $menu['MenuItem_ID'] ?>"
               value="<?= $menu['Price'] ?>"
               required>
    </td>

    <td>
        <button class="btn btn-pastel btn-sm mb-1"
                name="update_menu"
                form="form<?= $menu['MenuItem_ID'] ?>"
                type="submit">
            Update
        </button>

        <a href="admin.php?delete_menu=<?= $menu['MenuItem_ID'] ?>"
           class="btn btn-delete btn-sm mb-1"
           onclick="return confirm('Delete this menu item?')">
           Delete
        </a>
    </td>
</tr>
<?php } ?>
</tbody>
</table>

</div>

<!-- CUSTOMER ORDER DETAILS -->
<div class="card-box">

<span class="section-title">📋 Customer Order Details</span>

<table class="table table-hover">
<thead>
<tr>
    <th>Customer</th>
    <th>Phone</th>
    <th>Seat</th>
    <th>Food Ordered</th>
    <th>Total</th>
    <th>Food Status</th>
    <th>Payment Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php while($row = $orders->fetch_assoc()) { ?>
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
            SELECT m.Item_Name, oi.Quantity
            FROM Order_Item oi
            JOIN MenuItem m ON oi.MenuItem_ID = m.MenuItem_ID
            WHERE oi.Order_ID='$order_id'
        ");

        while($item = $items->fetch_assoc()){
            echo "<span class='food-badge'>" . $item['Item_Name'] . " x" . $item['Quantity'] . "</span><br>";
        }
        ?>
    </td>

    <td>
        RM <?= number_format($row['Total_Amount'] ?? 0, 2) ?>
    </td>

    <td>
        <span class="status-badge">
            <?= $row['Order_Status'] ?>
        </span>
    </td>

    <td>
        <span class="payment-badge">
            <?= $row['Payment_Status'] ?? 'Paid' ?>
        </span>
    </td>

    <td>
        <a href="receipt.php?order_id=<?= $row['Order_ID'] ?>"
           class="btn btn-print btn-sm mb-1"
           target="_blank">
           🖨 Print Receipt
        </a>

        <a href="admin.php?delete_order=<?= $row['Order_ID'] ?>"
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

<br>

</div>

</body>
</html>