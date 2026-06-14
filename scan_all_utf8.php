<?php
/**
 * COMPREHENSIVE UTF-8 SCANNER
 * -----------------------------------------------------------------
 * Run with:
 *   php artisan tinker --execute="require 'scan_all_utf8.php';"
 *
 * Scans EVERY text/varchar column in EVERY table in the database
 * for invalid UTF-8 byte sequences, using raw PDO (bypasses Eloquent
 * casts/mutators entirely so we see the exact bytes stored).
 */

$pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
$database = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();

// Get all text-like columns across all tables.
$columns = $pdo->query("
    SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = '{$database}'
    AND DATA_TYPE IN ('varchar','text','char','tinytext','mediumtext','longtext','json','enum')
    ORDER BY TABLE_NAME, ORDINAL_POSITION
")->fetchAll(PDO::FETCH_ASSOC);

$grouped = [];
foreach ($columns as $col) {
    $grouped[$col['TABLE_NAME']][] = $col['COLUMN_NAME'];
}

$totalBad = 0;

foreach ($grouped as $table => $cols) {
    // Skip Laravel internal tables that are usually large/irrelevant.
    if (in_array($table, ['jobs', 'cache', 'cache_locks', 'sessions', 'failed_jobs'])) {
        continue;
    }

    $colList = implode(', ', array_map(fn ($c) => "`{$c}`", $cols));

    try {
        $stmt = $pdo->query("SELECT `id`, {$colList} FROM `{$table}`");
    } catch (\PDOException $e) {
        // Table might not have an 'id' primary key column.
        $stmt = $pdo->query("SELECT {$colList} FROM `{$table}`");
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['id'] ?? '?';

        foreach ($cols as $col) {
            $value = $row[$col] ?? null;

            if (is_string($value) && $value !== '' && ! mb_check_encoding($value, 'UTF-8')) {
                $totalBad++;
                $latin1 = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252');

                echo "BAD UTF-8 -> table: {$table}, id: {$id}, column: {$col}\n";
                echo "  hex: ".bin2hex($value)."\n";
                echo "  as Windows-1252: ".($latin1 !== false ? $latin1 : '(conversion failed)')."\n";
                echo "  ----\n";
            }
        }
    }
}

echo "\nTotal bad values found: {$totalBad}\n";
echo "Scanned tables: ".implode(', ', array_keys($grouped))."\n";
