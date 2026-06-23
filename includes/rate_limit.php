<?php
// Rate limiting helper untuk mencegah brute force attacks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cek apakah IP/email sudah mencapai batas attempt login
 * Simpan data di session (untuk demo) atau database
 */
function is_rate_limited(string $identifier, int $max_attempts = 5, int $window_minutes = 15): bool
{
    $session_key = 'rate_limit_' . hash('sha256', $identifier);
    
    if (!isset($_SESSION[$session_key])) {
        $_SESSION[$session_key] = [];
    }
    
    $now = time();
    $window_start = $now - ($window_minutes * 60);
    
    // Hapus attempt yang sudah expired (lebih lama dari window)
    $_SESSION[$session_key] = array_filter($_SESSION[$session_key], function($timestamp) use ($window_start) {
        return $timestamp > $window_start;
    });
    
    // Jika sudah melampaui max_attempts dalam window, rate limited
    if (count($_SESSION[$session_key]) >= $max_attempts) {
        return true;
    }
    
    return false;
}

/**
 * Catat attempt login (failed)
 */
function record_login_attempt(string $identifier): void
{
    $session_key = 'rate_limit_' . hash('sha256', $identifier);
    
    if (!isset($_SESSION[$session_key])) {
        $_SESSION[$session_key] = [];
    }
    
    $_SESSION[$session_key][] = time();
}

/**
 * Reset rate limit setelah login berhasil
 */
function reset_rate_limit(string $identifier): void
{
    $session_key = 'rate_limit_' . hash('sha256', $identifier);
    unset($_SESSION[$session_key]);
}
