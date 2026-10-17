<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        exit('Invalid form token. Please go back, refresh and try again.');
    }
}
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}
function require_login(): void {
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}
function require_admin(): void {
    if (!current_user() || current_user()['role'] !== 'admin') {
        http_response_code(403);
        exit('Access denied. Admin account required.');
    }
}
function setting_value(mysqli $conn, string $key, string $fallback='0'): string {
    $stmt = $conn->prepare('SELECT setting_value FROM settings WHERE setting_key=?');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? $row['setting_value'] : $fallback;
}
function flash(string $message, string $type='info'): void {
    $_SESSION['flash'] = ['message'=>$message, 'type'=>$type];
}
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="notice '.e($f['type']).'">'.e($f['message']).'</div>';
        unset($_SESSION['flash']);
    }
}
