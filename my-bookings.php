<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_login();
$pageTitle = 'My Bookings';
$userId = (int)current_user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    if ($bookingId) {
        try {
            $conn->begin_transaction();
            $s = $conn->prepare("SELECT id,idol_id,quantity,status,stock_released FROM bookings WHERE id=? AND user_id=? FOR UPDATE");
            $s->bind_param('ii', $bookingId, $userId); $s->execute();
            $b = $s->get_result()->fetch_assoc();
            if (!$b || !in_array($b['status'], ['Pending','Confirmed'], true)) throw new RuntimeException('This booking can no longer be cancelled.');
            $u = $conn->prepare("UPDATE bookings SET status='Cancelled',stock_released=1 WHERE id=?");
            $u->bind_param('i', $bookingId); $u->execute();
            if (!(int)$b['stock_released']) {
                $r = $conn->prepare('UPDATE idols SET stock=stock+? WHERE id=?');
                $r->bind_param('ii', $b['quantity'], $b['idol_id']); $r->execute();
            }
            $conn->commit(); flash('Booking cancelled. Reserved stock was returned.', 'success');
        } catch (Throwable $e) {
            $conn->rollback(); flash($e instanceof RuntimeException ? $e->getMessage() : 'Could not cancel booking.', 'error');
        }
    }
    header('Location: my-bookings.php'); exit;
}
$stmt = $conn->prepare('SELECT b.*,i.name AS idol_name FROM bookings b JOIN idols i ON i.id=b.idol_id WHERE b.user_id=? ORDER BY b.created_at DESC');
$stmt->bind_param('i', $userId); $stmt->execute(); $bookings = $stmt->get_result();
require 'includes/header.php';
?>
<h1>My Bookings</h1>
<div class="table-wrap"><table><thead><tr><th>ID / Idol</th><th>Delivery</th><th>Total</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php while ($b = $bookings->fetch_assoc()): ?>
<tr>
<td>#<?= (int)$b['id'] ?> · <?= e($b['idol_name']) ?><br>Qty: <?= (int)$b['quantity'] ?></td>
<td><?= e($b['booking_date']) ?><br><?= e($b['delivery_area']) ?>, <?= e($b['delivery_city']) ?> - <?= e($b['delivery_pincode']) ?></td>
<td>₹<?= number_format((float)$b['grand_total'],2) ?><br><span class="small muted">Includes ₹<?= number_format((float)$b['delivery_charge'],2) ?> delivery</span></td>
<td><span class="pill"><?= e($b['status']) ?></span></td>
<td><?php if (in_array($b['status'], ['Pending','Confirmed'], true)): ?>
<form method="post" onsubmit="return confirm('Cancel this booking?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>"><button class="danger" type="submit">Cancel</button></form>
<?php else: ?>—<?php endif; ?></td>
</tr>
<?php endwhile; ?>
</tbody></table></div>
<?php require 'includes/footer.php'; ?>
