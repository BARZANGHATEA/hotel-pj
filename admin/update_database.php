<?php
require_once '../config/db.php';

// Check if user is admin
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Read the SQL update file
    $sql_file = '../config/update_blog_table.sql';
    if (!file_exists($sql_file)) {
        throw new Exception('Update file not found');
    }

    $sql_content = file_get_contents($sql_file);
    
    // Split queries and filter out empty ones
    $queries = array_filter(array_map('trim', explode(';', $sql_content)));
    
    $results = [];
    
    foreach ($queries as $query) {
        if (empty($query) || strpos($query, '--') === 0) continue;
        
        $result = $conn->query($query . ';');
        if ($result === false) {
            $results[] = [
                'query' => $query,
                'success' => false,
                'error' => $conn->error
            ];
        } else {
            $results[] = [
                'query' => $query,
                'success' => true
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database updated successfully',
        'results' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
