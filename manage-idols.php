<?php
require_once 'db.php';
require_once 'includes/functions.php';
require_admin();
$pageTitle = 'Manage Idols';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $description = trim($_POST['description'] ?? '');
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
        if (strlen($name) < 2 || strlen($name) > 120) $errors[] = 'Enter a valid idol name.';
        if ($price === false || $price === null || $price < 0) $errors[] = 'Enter a valid price.';
        if (strlen($description) < 3 || strlen($description) > 600) $errors[] = 'Enter a description.';
        if ($stock === false || $stock === null || $stock < 0 || $stock > 100000) $errors[] = 'Enter a valid stock quantity.';
        if (!$errors) {
            $stmt = $conn->prepare('INSERT INTO idols(name,price,description,stock,active) VALUES(?,?,?,?,1)');
            $stmt->bind_param('sdsi', $name, $price, $description, $stock); $stmt->execute();
            flash('Idol added successfully.', 'success'); header('Location: manage-idols.php'); exit;
        }
    } elseif ($action === 'stock') {
        $id = filter_input(INPUT_POST, 'idol_id', FILTER_VALIDATE_INT);
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
        if (!$id || $stock === false || $stock === null || $stock < 0 || $stock > 100000) flash('Invalid stock update.', 'error');
        else {
            $stmt = $conn->prepare('UPDATE idols SET stock=? WHERE id=?');
            $stmt->bind_param('ii', $stock, $id); $stmt->execute();
            flash('Stock updated.', 'success'); header('Location: manage-idols.php'); exit;
        }
    } elseif ($action === 'toggle') {
        $id = filter_input(INPUT_POST, 'idol_id', FILTER_VALIDATE_INT);
        if ($id) {
            $stmt = $conn->prepare('UPDATE idols SET active=1-active WHERE id=?');
            $stmt->bind_param('i', $id); $stmt->execute();
            flash('Idol visibility updated.', 'success');
        }
        header('Location: manage-idols.php'); exit;
    }
}
$rows = $conn->query('SELECT * FROM idols ORDER BY id DESC');
require 'includes/header.php';
?>
<h1>Manage Idols & Stock</h1>
<?php foreach ($errors as $error): ?><div class="notice error"><?= e($error) ?></div><?php endforeach; ?>
<div class="panel form">
<h2>Add New Idol</h2>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add">
<div class="field"><label>Idol name</label><input name="name" maxlength="120" required></div>
<div class="field"><label>Price (₹)</label><input type="number" name="price" min="0" step="0.01" required></div>
<div class="field"><label>Description</label><textarea name="description" maxlength="600" required></textarea></div>
<div class="field"><label>Opening stock</label><input type="number" name="stock" min="0" max="100000" required></div>
<button type="submit">Add Idol</button></form></div>
<h2>Current Idols</h2>
<div class="table-wrap"><table><thead><tr><th>Idol</th><th>Price</th><th>Stock</th><th>Visibility</th><th>Actions</th></tr></thead><tbody>
<?php while ($i = $rows->fetch_assoc()): ?><tr>
<td><?= e($i['name']) ?><br><span class="small muted"><?= e($i['description']) ?></span></td>
<td>₹<?= number_format((float)$i['price'],2) ?></td>
<td><form class="actions" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="stock"><input type="hidden" name="idol_id" value="<?= (int)$i['id'] ?>"><input type="number" name="stock" min="0" max="100000" value="<?= (int)$i['stock'] ?>" style="width:100px"><button type="submit">Save Stock</button></form></td>
<td><?= (int)$i['active'] ? 'Visible' : 'Hidden' ?></td>
<td><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="idol_id" value="<?= (int)$i['id'] ?>"><button class="secondary" type="submit"><?= (int)$i['active'] ? 'Hide' : 'Show' ?></button></form></td>
</tr><?php endwhile; ?>
</tbody></table></div>
<?php require 'includes/footer.php'; ?>
