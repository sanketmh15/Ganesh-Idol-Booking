
<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:5173');

require_once 'db.php';

try {
    $sql = "SELECT id, name, price, stock FROM idols";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception('Database query failed');
    }

    $idols = [];

    while ($row = $result->fetch_assoc()) {
        $idols[] = $row;
    }

    echo json_encode([
        'success' => true,
        'idols' => $idols
    ]);

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to fetch idols'
    ]);
}
?>
