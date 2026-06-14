<?php
// Run with: php artisan tinker
// Then paste this whole block, or save as a file and:
//   php artisan tinker --execute="require 'find_bad_utf8.php';"

$checks = [
    'Staff' => ['first_name', 'last_name', 'job_title', 'bank_name', 'staff_no'],
    'Department' => ['name'],
    'Project' => ['title', 'location', 'project_code'],
    'Client' => ['name', 'address'],
];

foreach ($checks as $model => $fields) {
    $class = "App\\Models\\{$model}";

    if (! class_exists($class)) {
        echo "SKIP (model not found): {$model}\n";
        continue;
    }

    $class::all()->each(function ($record) use ($fields, $model) {
        foreach ($fields as $field) {
            $value = $record->getAttributes()[$field] ?? null;

            if (is_string($value) && ! mb_check_encoding($value, 'UTF-8')) {
                echo "BAD UTF-8: {$model} #{$record->id}, field '{$field}', raw bytes: ";
                echo bin2hex($value)."\n";
                echo "  -> as latin1: ".mb_convert_encoding($value, 'UTF-8', 'Windows-1252')."\n";
            }
        }
    });
}

echo "Done checking.\n";
