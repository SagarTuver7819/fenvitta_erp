<?php
/*
 * Database connection (MySQLi - procedural style)
 *
 * Simple helper functions used in whole project:
 *   q($sql, $params)      -> run any query (INSERT / UPDATE / DELETE / SELECT)
 *   rows($sql, $params)   -> get all rows as array
 *   row($sql, $params)    -> get single row (or null)
 *   val($sql, $params)    -> get single value (first column of first row)
 *   insert($table, $data) -> insert array and return new id
 *   update($table, $data, $id)
 *
 * All values are passed with "?" placeholders (prepared statement), so SQL injection is not possible.
 * Example:  $party = row("SELECT * FROM parties WHERE id = ?", [$id]);
 */
require_once __DIR__ . '/../config/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = null;

function db()
{
    global $conn;
    if ($conn === null) {
        try {
            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            mysqli_set_charset($conn, 'utf8mb4');
        } catch (mysqli_sql_exception $e) {
            http_response_code(500);
            die('<h3 style="font-family:sans-serif;color:#632a4a">Database connection failed.</h3>'
                . '<p style="font-family:sans-serif">Check <code>config/config.php</code> and import <code>database/fenvita_inventory.sql</code>.<br><small>'
                . htmlspecialchars($e->getMessage()) . '</small></p>');
        }
    }
    return $conn;
}

/** Run a query with ? placeholders. Returns mysqli_result for SELECT, true for others. */
function q($sql, $params = [])
{
    $stmt = mysqli_prepare(db(), $sql);
    if ($params) {
        $types = '';
        $values = [];
        foreach ($params as $p) {
            if (is_int($p)) {
                $types .= 'i';
            } elseif (is_float($p)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $p;
        }
        mysqli_stmt_bind_param($stmt, $types, ...$values);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result === false ? true : $result;
}

function rows($sql, $params = [])
{
    $result = q($sql, $params);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function row($sql, $params = [])
{
    $result = q($sql, $params);
    $r = mysqli_fetch_assoc($result);
    return $r ?: null;
}

function val($sql, $params = [])
{
    $result = q($sql, $params);
    $r = mysqli_fetch_row($result);
    return $r ? $r[0] : null;
}

function insert($table, $data)
{
    $cols = array_keys($data);
    $placeholders = implode(',', array_fill(0, count($cols), '?'));
    $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES ($placeholders)";
    q($sql, array_values($data));
    return mysqli_insert_id(db());
}

function update($table, $data, $id)
{
    $set = [];
    foreach (array_keys($data) as $col) {
        $set[] = "`$col` = ?";
    }
    $params = array_values($data);
    $params[] = (int)$id;
    q("UPDATE `$table` SET " . implode(', ', $set) . " WHERE id = ?", $params);
}

/** Transaction helpers (used when saving bill + items together) */
function db_begin()
{
    mysqli_begin_transaction(db());
}

function db_commit()
{
    mysqli_commit(db());
}

function db_rollback()
{
    mysqli_rollback(db());
}
