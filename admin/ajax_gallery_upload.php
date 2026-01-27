<?php
/**
 * AJAX Gallery Multi-Upload Handler
 * Handles multiple image uploads with progress support
 */

// Suppress any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Include authentication and database connection
require 'check_auth.php';

// If conn wasn't loaded by check_auth, load it directly
if (!isset($conn) || !$conn) {
    require_once 'conn.php';
}

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'uploaded' => 0, 'failed' => 0, 'files' => []];

try {
    // Check if database connection exists
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection failed');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Invalid request method';
        echo json_encode($response);
        exit;
    }

    $gallery_category = isset($_POST['gallery_category']) ? intval($_POST['gallery_category']) : 0;

    if (!isset($_FILES['gallery_images']) || empty($_FILES['gallery_images']['name'][0])) {
        $response['message'] = 'No files selected';
        echo json_encode($response);
        exit;
    }

    $upload_dir = 'post_img/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }

    $files = $_FILES['gallery_images'];
    $total_files = count($files['name']);

    for ($i = 0; $i < $total_files; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
                UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
                UPLOAD_ERR_PARTIAL => 'File partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
                UPLOAD_ERR_EXTENSION => 'Upload blocked by extension'
            ];
            $response['files'][] = [
                'name' => $files['name'][$i],
                'success' => false,
                'error' => $error_messages[$files['error'][$i]] ?? 'Upload error: ' . $files['error'][$i]
            ];
            $response['failed']++;
            continue;
        }

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($files['tmp_name'][$i]);
        
        if (!in_array($file_type, $allowed_types)) {
            $response['files'][] = [
                'name' => $files['name'][$i],
                'success' => false,
                'error' => 'Invalid file type: ' . $file_type
            ];
            $response['failed']++;
            continue;
        }

        // Generate unique filename
        $original_name = pathinfo($files['name'][$i], PATHINFO_FILENAME);
        $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
        $image_name = time() . '_' . $i . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $original_name) . '.' . $extension;
        
        // Move uploaded file
        if (move_uploaded_file($files['tmp_name'][$i], $upload_dir . $image_name)) {
            // Generate title from filename
            $gallery_title = ucwords(str_replace(['_', '-'], ' ', $original_name));
            $gallery_title = mysqli_real_escape_string($conn, $gallery_title);
            $alias = strtolower(str_replace(' ', '-', $gallery_title));
            
            // Insert into database
            $sql = "INSERT INTO tbl_gallery (gallery_title, gallery_category_id, gallery_image, alise, status)
                    VALUES ('$gallery_title', '$gallery_category', '$image_name', '$alias', 1)";
            
            if (mysqli_query($conn, $sql)) {
                $response['files'][] = [
                    'name' => $files['name'][$i],
                    'success' => true,
                    'title' => $gallery_title,
                    'image' => $image_name
                ];
                $response['uploaded']++;
            } else {
                // Remove uploaded file if DB insert fails
                @unlink($upload_dir . $image_name);
                $response['files'][] = [
                    'name' => $files['name'][$i],
                    'success' => false,
                    'error' => 'DB: ' . mysqli_error($conn)
                ];
                $response['failed']++;
            }
        } else {
            $response['files'][] = [
                'name' => $files['name'][$i],
                'success' => false,
                'error' => 'Failed to save file'
            ];
            $response['failed']++;
        }
    }

    $response['success'] = $response['uploaded'] > 0;
    $response['message'] = $response['uploaded'] . ' of ' . $total_files . ' images uploaded successfully';

    if ($response['failed'] > 0) {
        $response['message'] .= ' (' . $response['failed'] . ' failed)';
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
