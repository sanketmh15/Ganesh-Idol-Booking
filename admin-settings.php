<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_admin();
$pageTitle = 'Delivery Settings';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $charge = filter_input(INPUT_POST, 'delivery_charge', FILTER_VALIDATE_FLOAT);
    if ($charge === false || $charge === null || $charge < 0 || $charge > 100000) $error = 'Enter a delivery charge from ₹0 to ₹100000.';
    else {
        $value = number_format((float)$charge, 2, '.', '');
        $stmt = $conn->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('delivery_charge',?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        $stmt->bind_param('s', $value); $stmt->execute();
        flash('Delivery charge updated. Existing bookings keep their original charge.', 'success');
        header('Location: admin-settings.php'); exit;
    }
}
$current = setting_value($conn, 'delivery_charge', '100.00');
require 'includes/header.php';
?>
<h1>Delivery Charge Settings</h1>
<div class="panel form">
<p>Set one standard home-delivery charge. This amount is added to the booking total and saved with each booking.</p>
<?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="field"><label>Delivery charge (₹)</label><input type="number" name="delivery_charge" min="0" max="100000" step="0.01" required value="<?= e($current) ?>"></div>
<button type="submit">Save Delivery Charge</button>
</form></div>
<?php require 'includes/footer.php'; ?>
