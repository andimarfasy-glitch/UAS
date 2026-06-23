<?php
/**
 * Image Upload Helper
 * Handles image file uploads dengan validasi MIME type dan ukuran
 */

/**
 * Upload dan simpan image file
 * 
 * @param array $file $_FILES array (e.g., $_FILES['gambar'])
 * @param string $upload_dir Directory untuk simpan file (relative to root)
 * @param int $max_size Maximum file size in bytes (default: 2MB)
 * @param array $allowed_mimes Allowed MIME types
 * @return array|null Array dengan ['success' => bool, 'filename' => string, 'error' => string] atau null jika no file
 */
function upload_image($file, $upload_dir, $max_size = 2097152, $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif']) {
    // No file provided
    if (empty($file['name'])) {
        return null;
    }
    
    $result = ['success' => false, 'filename' => null, 'error' => null];
    
    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_mimes)) {
        $result['error'] = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF.';
        return $result;
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        $result['error'] = 'Ukuran gambar terlalu besar. Maksimal ' . ($max_size / 1024 / 1024) . 'MB.';
        return $result;
    }
    
    // Create upload directory if not exists
    $full_upload_dir = __DIR__ . '/../' . $upload_dir;
    if (!is_dir($full_upload_dir)) {
        if (!mkdir($full_upload_dir, 0755, true)) {
            $result['error'] = 'Gagal membuat direktori upload.';
            return $result;
        }
    }
    
    // Generate unique filename
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $filepath = $full_upload_dir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        $result['error'] = 'Gagal upload file ke server.';
        return $result;
    }
    
    $result['success'] = true;
    $result['filename'] = $filename;
    return $result;
}

/**
 * Delete image file
 * 
 * @param string $filename Nama file (tanpa path)
 * @param string $upload_dir Directory file (relative to root)
 * @return bool Success status
 */
function delete_image($filename, $upload_dir) {
    if (empty($filename)) {
        return false;
    }
    
    $filepath = __DIR__ . '/../' . $upload_dir . $filename;
    if (file_exists($filepath) && is_file($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Get image URL untuk display di frontend
 * 
 * @param string $filename Nama file
 * @param string $upload_dir Directory file (relative to root)
 * @param string $placeholder URL placeholder jika tidak ada file
 * @return string URL gambar atau placeholder
 */
function get_image_url($filename, $upload_dir, $placeholder = null) {
    if (!empty($filename)) {
        return $upload_dir . htmlspecialchars($filename);
    }
    return $placeholder ?? 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100"%3E%3Crect fill="%23e0e0e0" width="100" height="100"/%3E%3Ctext x="50" y="50" text-anchor="middle" dy=".3em" font-size="12" fill="%23999"%3ENo Image%3C/text%3E%3C/svg%3E';
}
?>
