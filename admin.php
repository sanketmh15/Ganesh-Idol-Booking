<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_admin();
$pageTitle = 'Admin Dashboard';
$stats = [
 'bookings'=>(int)$conn->query('SELECT COUNT(*) n FROM bookings')->fetch_assoc()['n'],
 'pending'=>(int)$conn->query("SELECT COUNT(*) n FROM bookings WHERE status='Pending'")->fetch_assoc()['n'],
 'idols'=>(int)$conn->query('SELECT COUNT(*) n FROM idols')->fetch_assoc()['n'],
 'stock'=>(int)$conn->query('SELECT COALESCE(SUM(stock),0) n FROM idols')->fetch_assoc()['n']
];
require 'includes/header.php';
?>
<h1>Admin Dashboard</h1>
<div class="grid">
<div class="card"><h3>Total bookings</h3><p class="price"><?= $stats['bookings'] ?></p></div>
<div class="card"><h3>Pending bookings</h3><p class="price"><?= $stats['pending'] ?></p></div>
<div class="card"><h3>Idol products</h3><p class="price"><?= $stats['idols'] ?></p></div>
<div class="card"><h3>Total stock</h3><p class="price"><?= $stats['stock'] ?></p></div>
</div>
<br>
<div class="panel">
<h2>Manage the shop</h2>
<div class="actions">
<a class="btn" href="manage-bookings.php">Manage Bookings</a>
<a class="btn" href="manage-idols.php">Manage Idols & Stock</a>
<a class="btn secondary" href="admin-settings.php">Delivery Charge Settings</a>
</div>
</div>
<?php require 'includes/footer.php'; ?>
