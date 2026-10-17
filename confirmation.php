<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $conn->prepare("SELECT b.*, i.name AS idol_name FROM bookings b JOIN idols i ON i.id=b.idol_id WHERE b.id=? AND b.user_id=?");
$userId = (int)current_user()['id'];
$stmt->bind_param('ii', $id, $userId); $stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
if (!$booking) { http_response_code(404); exit('Booking not found.'); }
$pageTitle = 'Booking Confirmation';
require 'includes/header.php';
?>
<div class="panel">
<h1>🙏 Booking Saved Successfully!</h1>
<p>Your booking has been saved to the database. Keep this booking ID for reference.</p>
<h2>Booking #<?= (int)$booking['id'] ?></h2>
<p><strong>Idol:</strong> <?= e($booking['idol_name']) ?></p>
<p><strong>Quantity:</strong> <?= (int)$booking['quantity'] ?></p>
<p><strong>Delivery date:</strong> <?= e($booking['booking_date']) ?></p>
<p><strong>Delivery address:</strong> <?= e($booking['delivery_address']) ?>, <?= e($booking['delivery_area']) ?>, <?= e($booking['delivery_city']) ?> - <?= e($booking['delivery_pincode']) ?></p>
<p><strong>Idol total:</strong> ₹<?= number_format((float)$booking['idol_total'],2) ?></p>
<p><strong>Delivery charge:</strong> ₹<?= number_format((float)$booking['delivery_charge'],2) ?></p>
<p class="price">Grand total: ₹<?= number_format((float)$booking['grand_total'],2) ?></p>
<p><strong>Status:</strong> <span class="pill"><?= e($booking['status']) ?></span></p>
<a class="btn" href="my-bookings.php">View My Bookings</a>
</div>
<?php require 'includes/footer.php'; ?>
