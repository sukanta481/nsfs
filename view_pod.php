<?php
/**
 * POD Image Viewer
 * Serves POD images through PHP to bypass any server restrictions on direct file access
 */

// Get the POD file path from query parameter
$pod_file = isset($_GET['file']) ? $_GET['file'] : '';

// Security: Validate the path
if (empty($pod_file)) {
    http_response_code(400);
    die('No file specified');
}

// Security: Only allow files from uploads/pod directory
if (strpos($pod_file, 'uploads/pod/') !== 0) {
    http_response_code(403);
    die('Invalid file path');
}

// Security: Prevent directory traversal
if (strpos($pod_file, '..') !== false) {
    http_response_code(403);
    die('Invalid file path');
}

// Build full file path
$file_path = __DIR__ . '/' . $pod_file;

// Check if file exists
if (!file_exists($file_path)) {
    http_response_code(404);
    die('File not found');
}

// Get file info
$file_info = pathinfo($file_path);
$extension = strtolower($file_info['extension'] ?? '');

// Set content type based on extension
$content_types = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'pdf' => 'application/pdf'
];

if (!isset($content_types[$extension])) {
    http_response_code(415);
    die('Unsupported file type');
}

// Set headers
header('Content-Type: ' . $content_types[$extension]);
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: public, max-age=86400'); // Cache for 24 hours

// Output the file
readfile($file_path);
exit;
