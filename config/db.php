<?php
// Database settings - XAMPP defaults. Change them here if your MySQL uses a password.
const DB_HOST = 'localhost';
const DB_NAME = 'adopit_db';
const DB_USER = 'root';
const DB_PASS = '';

// UTC+8, same clock as this PC. Change it if you run the system elsewhere.
date_default_timezone_set('Asia/Singapore');

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $ex) {
            http_response_code(500);
            exit('<div style="font-family:system-ui,sans-serif;max-width:560px;margin:80px auto;padding:28px;border:3px solid #99F6E4;border-radius:22px;color:#134E4A">'
                . '<h1 style="margin-top:0">AdopIT! cannot reach its database</h1>'
                . '<p>Start <strong>MySQL</strong> in the XAMPP Control Panel, then run the one-click installer.</p>'
                . '<p><a href="setup.php" style="color:#C2410C;font-weight:700">Open setup.php</a></p>'
                . '<p style="color:#475569;font-size:14px">' . htmlspecialchars($ex->getMessage()) . '</p></div>');
        }
    }
    return $pdo;
}
