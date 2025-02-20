<?php
include 'db_config.php';

// Apply the same filter logic as before
$whereClauses = [];
$queryParams = [];

if (isset($_GET['name']) && !empty($_GET['name'])) {
    $name = '%' . $_GET['name'] . '%';
    $whereClauses[] = "name LIKE ?";
    $queryParams[] = $name;
}
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = $_GET['status'];
    $whereClauses[] = "status = ?";
    $queryParams[] = $status;
}
if (isset($_GET['role']) && !empty($_GET['role'])) {
    $role = $_GET['role'];
    $whereClauses[] = "role = ?";
    $queryParams[] = $role;
}

$sql = "SELECT id, name, email, role, status, created_at FROM users";
if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$stmt = $conn->prepare($sql);
if (!empty($queryParams)) {
    $types = str_repeat('s', count($queryParams));
    $stmt->bind_param($types, ...$queryParams);
}
$stmt->execute();
$result = $stmt->get_result();

// Output CSV headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="user_report.csv"');

// Open the output stream and write the data
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Email', 'Role', 'Status', 'Created At']); // CSV column names

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
$conn->close();
exit;
?>
