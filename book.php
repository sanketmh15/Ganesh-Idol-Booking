
<?php
require_once 'db.php';
require_once 'includes/functions.php';

require_login();

$pageTitle = 'Book Idol';

$idolId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idolId = filter_input(INPUT_POST, 'idol_id', FILTER_VALIDATE_INT);
}

if (!$idolId || $idolId < 1) {
    http_response_code(400);
    exit('Invalid idol.');
}

$stmt = $conn->prepare(
    'SELECT id, name, price, description, stock
     FROM idols WHERE id = ? AND active = 1'
);
$stmt->bind_param('i', $idolId);
$stmt->execute();

$idol = $stmt->get_result()->fetch_assoc();

if (!$idol) {
    http_response_code(404);
    exit('Idol not found.');
}

$deliveryCharge = (float) setting_value($conn, 'delivery_charge', '100.00');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $date = trim($_POST['booking_date'] ?? '');
    $address = trim($_POST['delivery_address'] ?? '');
    $area = trim($_POST['delivery_area'] ?? '');
    $city = trim($_POST['delivery_city'] ?? '');
    $pin = trim($_POST['delivery_pincode'] ?? '');
    $mobile = preg_replace('/\s+/', '', trim($_POST['delivery_mobile'] ?? ''));

    $dateObj = DateTime::createFromFormat('!Y-m-d', $date);

    if (!$quantity || $quantity < 1 || $quantity > 20) {
        $errors[] = 'Quantity must be between 1 and 20.';
    }

    if (
        !$dateObj ||
        $dateObj->format('Y-m-d') !== $date ||
        $date < date('Y-m-d')
    ) {
        $errors[] = 'Choose a valid delivery date today or later.';
    }

    if (strlen($address) < 5 || strlen($address) > 500) {
        $errors[] = 'Enter a complete delivery address.';
    }

    if (strlen($area) < 2 || strlen($area) > 120) {
        $errors[] = 'Enter your area/locality.';
    }

    if (strlen($city) < 2 || strlen($city) > 100) {
        $errors[] = 'Enter your city.';
    }

    if (!preg_match('/^[0-9]{6}$/', $pin)) {
        $errors[] = 'PIN code must contain 6 digits.';
    }

    if (!preg_match('/^[0-9+()\-]{10,15}$/', $mobile)) {
        $errors[] = 'Enter a valid delivery mobile number.';
    }

    if (!$errors) {
        try {
            $conn->begin_transaction();

            $lock = $conn->prepare(
                'SELECT price, stock FROM idols
                 WHERE id = ? AND active = 1 FOR UPDATE'
            );
            $lock->bind_param('i', $idolId);
            $lock->execute();

            $fresh = $lock->get_result()->fetch_assoc();

            if (!$fresh || (int) $fresh['stock'] < $quantity) {
                throw new RuntimeException(
                    'Not enough stock available. Please reduce quantity or choose another idol.'
                );
            }

            $unitPrice = (float) $fresh['price'];
            $idolTotal = round($unitPrice * $quantity, 2);
            $grandTotal = round($idolTotal + $deliveryCharge, 2);
            $userId = (int) current_user()['id'];

            $ins = $conn->prepare(
                'INSERT INTO bookings
                (user_id, idol_id, quantity, booking_date, delivery_address,
                 delivery_area, delivery_city, delivery_pincode, delivery_mobile,
                 delivery_charge, idol_total, grand_total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $ins->bind_param(
                'iiissssssddd',
                $userId,
                $idolId,
                $quantity,
                $date,
                $address,
                $area,
                $city,
                $pin,
                $mobile,
                $deliveryCharge,
                $idolTotal,
                $grandTotal
            );

            $ins->execute();
            $bookingId = $conn->insert_id;

            $upd = $conn->prepare(
                'UPDATE idols SET stock = stock - ?
                 WHERE id = ? AND stock >= ?'
            );
            $upd->bind_param('iii', $quantity, $idolId, $quantity);
            $upd->execute();

            if ($upd->affected_rows !== 1) {
                throw new RuntimeException(
                    'Stock changed while booking. Please try again.'
                );
            }

            $conn->commit();

            header('Location: confirmation.php?id=' . $bookingId);
            exit;

        } catch (Throwable $e) {
            $conn->rollback();

            $errors[] = $e instanceof RuntimeException
                ? $e->getMessage()
                : 'Booking failed. Please try again.';

            error_log($e->getMessage());
        }
    }
}

require 'includes/header.php';
?>

<section class="booking-heading">
    <p class="muted">🙏 GIRIJATMAK ARTS</p>
    <h1>Book Your Bappa</h1>
    <p class="muted">Complete your delivery details to reserve your Ganpati idol.</p>
</section>

<div class="panel form booking-panel">

    <h2><?= e($idol['name']) ?></h2>

    <p><?= e($idol['description']) ?></p>

    <p class="price">
        ₹<?= number_format((float) $idol['price'], 2) ?> per idol
    </p>

    <p>
        Available stock:
        <strong><?= (int) $idol['stock'] ?></strong>
    </p>

    <p>
        Delivery charge:
        <strong>₹<?= number_format($deliveryCharge, 2) ?></strong>
    </p>

    <?php foreach ($errors as $error): ?>
        <div class="notice error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <?php if ((int) $idol['stock'] > 0): ?>

        <form method="post" id="bookingForm">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <input
                type="hidden"
                name="idol_id"
                value="<?= (int) $idol['id'] ?>"
            >

            <div class="field">
                <label for="quantity">Quantity</label>
                <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    min="1"
                    max="20"
                    required
                    value="<?= e($_POST['quantity'] ?? '1') ?>"
                >
                <small class="field-error" id="quantityError"></small>
            </div>

            <div class="field">
                <label for="bookingDate">Preferred delivery date</label>
                <input
                    type="date"
                    id="bookingDate"
                    name="booking_date"
                    min="<?= e(date('Y-m-d')) ?>"
                    required
                    value="<?= e($_POST['booking_date'] ?? '') ?>"
                >
                <small class="field-error" id="dateError"></small>
            </div>

            <div class="field">
                <label for="deliveryAddress">House / building / street address</label>
                <textarea
                    id="deliveryAddress"
                    name="delivery_address"
                    maxlength="500"
                    required
                ><?= e($_POST['delivery_address'] ?? '') ?></textarea>
                <small class="field-error" id="addressError"></small>
            </div>

            <div class="field">
                <label for="deliveryArea">Area / locality</label>
                <input
                    id="deliveryArea"
                    name="delivery_area"
                    maxlength="120"
                    required
                    value="<?= e($_POST['delivery_area'] ?? '') ?>"
                >
                <small class="field-error" id="areaError"></small>
            </div>

            <div class="field">
                <label for="deliveryCity">City</label>
                <input
                    id="deliveryCity"
                    name="delivery_city"
                    maxlength="100"
                    required
                    value="<?= e($_POST['delivery_city'] ?? '') ?>"
                >
                <small class="field-error" id="cityError"></small>
            </div>

            <div class="field">
                <label for="deliveryPincode">6-digit PIN code</label>
                <input
                    type="text"
                    id="deliveryPincode"
                    name="delivery_pincode"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    minlength="6"
                    maxlength="6"
                    required
                    value="<?= e($_POST['delivery_pincode'] ?? '') ?>"
                >
                <small class="field-error" id="pinError"></small>
            </div>

            <div class="field">
                <label for="deliveryMobile">Delivery mobile number</label>
                <input
                    type="tel"
                    id="deliveryMobile"
                    name="delivery_mobile"
                    maxlength="15"
                    required
                    value="<?= e($_POST['delivery_mobile'] ?? '') ?>"
                >
                <small class="field-error" id="mobileError"></small>
            </div>

            <div class="booking-total">
                <p>Estimated total</p>
                <strong id="estimatedTotal">
                    ₹<?= number_format(
                        (float) $idol['price'] + $deliveryCharge,
                        2
                    ) ?>
                </strong>
                <small>
                    Final total = idol price × quantity + delivery charge.
                    Payment is not collected on this website.
                </small>
            </div>

            <p id="bookingMessage" class="notice error" hidden></p>

            <button type="submit" class="booking-submit">
                Confirm Booking →
            </button>
        </form>

    <?php else: ?>

        <div class="notice error">
            This idol is currently out of stock.
        </div>

        <a class="btn" href="idols.php">Browse Other Idols</a>

    <?php endif; ?>
</div>

<script>
    window.bookingConfig = {
        price: <?= json_encode((float) $idol['price']) ?>,
        deliveryCharge: <?= json_encode($deliveryCharge) ?>,
        maxStock: <?= json_encode((int) $idol['stock']) ?>
    };
</script>

<script src="assets/js/booking-validation.js" defer></script>

<?php require 'includes/footer.php'; ?>
