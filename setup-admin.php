<?php
require_once 'db.php';
require_once 'includes/functions.php';

$pageTitle = 'Create Admin Account';
$errors = [];

$adminExists = false;
$check = $conn->query("SELECT 1 FROM users WHERE role = 'admin' LIMIT 1");
if ($check && $check->num_rows > 0) {
    $adminExists = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $mobile = preg_replace('/\s+/', '', trim($_POST['mobile'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors[] = 'Name must be 2–100 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (!preg_match('/^[0-9+()-]{10,15}$/', $mobile)) {
        $errors[] = 'Enter a valid mobile number.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if (!$errors) {
        try {
            $countStmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
            $countStmt->execute();
            $existingAdmin = $countStmt->get_result()->fetch_assoc();

            if ($existingAdmin) {
                flash('An admin account already exists. Please log in.', 'success');
                header('Location: login.php');
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users(name, email, mobile, password_hash, role) VALUES(?, ?, ?, ?, 'admin')");
            $stmt->bind_param('ssss', $name, $email, $mobile, $hash);
            $stmt->execute();

            flash('Admin account created successfully. Please log in.', 'success');
            header('Location: login.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            $errors[] = $e->getCode() === 1062 ? 'That email is already registered.' : 'Could not create admin account. Please try again.';
        }
    }
}

require 'includes/header.php';
?>

<h1>Create First Admin Account</h1>

<?php if ($adminExists): ?>
    <div class="notice success">
        An admin user already exists. Please <a href="login.php">log in</a> to continue.
    </div>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <div class="notice error"><?= e($error) ?></div>
<?php endforeach; ?>

<?php if (!$adminExists): ?>
    <form class="panel form" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="field">
            <label>Full name</label>
            <input name="name" maxlength="100" required value="<?= e($_POST['name'] ?? '') ?>">
        </div>

        <div class="field">
            <label>Email</label>
            <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
        </div>

        <div class="field">
            <label>Mobile number</label>
            <input name="mobile" required maxlength="15" value="<?= e($_POST['mobile'] ?? '') ?>">
        </div>

        <div class="field">
            <label>Password (minimum 8 characters)</label>
            <input type="password" name="password" minlength="8" required>
        </div>

        <button type="submit">Create Admin Account</button>
    </form>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
