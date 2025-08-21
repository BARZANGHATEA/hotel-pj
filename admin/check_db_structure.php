<?php
require_once '../config/db.php';

// Function to check if a column exists in a table
function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

// Check required columns
$checks = [
    'blog_posts' => ['status', 'updated_at'],
    'blog_post_translations' => ['updated_at']
];

$missing_columns = [];

foreach ($checks as $table => $columns) {
    foreach ($columns as $column) {
        if (!columnExists($conn, $table, $column)) {
            $missing_columns[] = "$table.$column";
        }
    }
}

if (!empty($missing_columns)) {
    echo json_encode([
        'status' => 'missing_columns',
        'missing' => $missing_columns,
        'message' => 'Database structure needs to be updated'
    ]);
} else {
    echo json_encode([
        'status' => 'ok',
        'message' => 'Database structure is up to date'
    ]);
}
?>
