<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', config('db.host'), config('db.name'));
        $pdo = new PDO($dsn, config('db.user'), config('db.password'), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function db_query(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    foreach (array_values($params) as $i => $value) {
        $type = is_int($value) ? PDO::PARAM_INT : (is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue($i + 1, $value, $type);
    }
    $stmt->execute();
    return $stmt;
}

function db_all(string $sql, array $params = []): array
{
    return db_query($sql, $params)->fetchAll();
}

function db_one(string $sql, array $params = []): ?array
{
    $row = db_query($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function db_val(string $sql, array $params = [])
{
    $value = db_query($sql, $params)->fetchColumn();
    return $value === false ? null : $value;
}

function db_exec(string $sql, array $params = []): int
{
    return db_query($sql, $params)->rowCount();
}

function db_insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(', ', $cols),
        implode(', ', array_fill(0, count($cols), '?'))
    );
    db_query($sql, array_values($data));
    return (int) db()->lastInsertId();
}

function db_update(string $table, int $id, array $data): void
{
    $sets = implode(', ', array_map(fn ($c) => "$c = ?", array_keys($data)));
    db_query("UPDATE {$table} SET {$sets} WHERE id = ?", [...array_values($data), $id]);
}
