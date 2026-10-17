<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_admin();
$pageTitle = 'Manage Bookings';
$allowed = ['Pending','Confirmed','Out for Delivery','Delivered','Cancelled'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $newStatus = $_POST['status'] ?? '';
    if (!$bookingId || !in_array($newStatus, $allowed, true)) {
        flash('Invalid booking update.', 'error');
    } else {
        try {
            $conn->begin_transaction();
            $s = $conn->prepare('SELECT id,idol_id,quantity,status,stock_released FROM bookings WHERE id=? FOR UPDATE');
            $s->bind_param('i', $bookingId); $s->execute(); $b = $s->get_result()->fetch_assoc();
            if (!$b) throw new RuntimeException('Booking not found.');
            if ($b['status'] === 'Cancelled' && $newStatus !== 'Cancelled') throw new RuntimeException('Cancelled bookings cannot be reopened; create a new booking.');
            if ($newStatus === 'Cancelled' && $b['status'] !== 'Cancelled' && !(int)$b['stock_released']) {
                $r = $conn->prepare('UPDATE idols SET stock=stock+? WHERE id=?');
                $r->bind_param('ii', $b['quantity'], $b['idol_id']); $r->execute();
                $u = $conn->prepare('UPDATE bookings SET status=?,stock_released=1 WHERE id=?');
            } else {
                $u = $conn->prepare('UPDATE bookings SET status=? WHERE id=?');
            }
            $u->bind_param('si', $newStatus, $bookingId); $u->execute();
            $conn->commit(); flash('Booking status updated.', 'success');
        } catch (Throwable $e) {
            $conn->rollback(); flash($e instanceof RuntimeException ? $e->getMessage() : 'Could not update booking.', 'error');
        }
    }
    header('Location: manage-bookings.php'); exit;
}
$sql = "SELECT b.*,u.name AS customer_name,u.email AS customer_email,u.mobile AS customer_mobile,i.name AS idol_name FROM bookings b JOIN users u ON u.id=b.user_id JOIN idols i ON i.id=b.idol_id ORDER BY b.created_at DESC";
$rows = $conn->query($sql);
require 'includes/header.php';
?>
<h1>Manage Bookings & Delivery</h1>
<div class="table-wrap"><table><thead><tr><th>Booking / Customer</th><th>Idol / Qty</th><th>Delivery details</th><th>Total</th><th>Status</th><th>Update</th></tr></thead><tbody>
<?php while ($b = $rows->fetch_assoc()): ?>
<tr>
<td>#<?= (int)$b['id'] ?><br><?= e($b['customer_name']) ?><br><?= e($b['customer_email']) ?><br><?= e($b['customer_mobile']) ?></td>
<td><?= e($b['idol_name']) ?><br>Qty: <?= (int)$b['quantity'] ?></td>
<td><?= e($b['booking_date']) ?><br><?= e($b['delivery_address']) ?><br><?= e($b['delivery_area']) ?>, <?= e($b['delivery_city']) ?> - <?= e($b['delivery_pincode']) ?><br>Phone: <?= e($b['delivery_mobile']) ?></td>
<td>₹<?= number_format((float)$b['grand_total'],2) ?><br><span class="small muted">Delivery ₹<?= number_format((float)$b['delivery_charge'],2) ?></span></td>
<td><span class="pill"><?= e($b['status']) ?></span></td>
<td><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>"><select name="status"><?php foreach ($allowed as $status): ?><option value="<?= e($status) ?>" <?= $status === $b['status'] ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select><button type="submit">Save</button></form></td>
</tr>
<?php endwhile; ?>
</tbody></table></div>
<?php require 'includes/footer.php'; ?>
