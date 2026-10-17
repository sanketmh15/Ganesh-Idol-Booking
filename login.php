<?php
require_once 'db.php';
require_once 'includes/functions.php';
$pageTitle = 'Login';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare('SELECT id,name,email,password_hash,role FROM users WHERE email=? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>(int)$user['id'], 'name'=>$user['name'], 'email'=>$user['email'], 'role'=>$user['role']];
        header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'index.php')); exit;
    }
    $error = 'Incorrect email or password.';
}
require 'includes/header.php';
?>
<h1>Login</h1>
<?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<form class="panel form" method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="field"><label>Email</label><input type="email" name="email" required></div>
<div class="field"><label>Password</label><input type="password" name="password" required></div>
<button type="submit">Login</button>
<p class="small muted">Admin and customers use this login page; permissions depend on the account role.</p>
</form>
<?php require 'includes/footer.php'; ?>
