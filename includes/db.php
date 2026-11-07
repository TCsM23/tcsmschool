<?php
/**
 * Database connection. Call db() anywhere to get the shared mysqli handle.
 * mysqli throws mysqli_sql_exception on errors, so every query either
 * succeeds or stops the request (no silent failures).
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function db(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $conn->set_charset('utf8mb4');
    } catch (mysqli_sql_exception $ex) {
        error_log('DB connection failed: ' . $ex->getMessage());
        http_response_code(500);
        exit('Database connection failed. Check config/config.php and that MySQL is running in XAMPP.');
    }
    return $conn;
}

/**
 * Run a prepared statement and return the mysqli_stmt.
 * $types is the bind_param type string ('s', 'i', 'd'); params are bound in order.
 */
function db_query(string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $stmt = db()->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

/** Fetch all rows as associative arrays. */
function db_all(string $sql, string $types = '', array $params = []): array
{
    $stmt = db_query($sql, $types, $params);
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

/** Fetch a single row, or null. */
function db_one(string $sql, string $types = '', array $params = []): ?array
{
    $stmt = db_query($sql, $types, $params);
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

/** Run an INSERT/UPDATE/DELETE; returns affected rows. */
function db_exec(string $sql, string $types = '', array $params = []): int
{
    $stmt = db_query($sql, $types, $params);
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}
