<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING FOREIGN KEY CONSTRAINTS ===\n\n";

$constraints = DB::select("
    SELECT 
        CONSTRAINT_NAME, 
        TABLE_NAME, 
        COLUMN_NAME, 
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND REFERENCED_TABLE_NAME = 'data_produk'
");

foreach ($constraints as $c) {
    echo "Table: {$c->TABLE_NAME}\n";
    echo "Column: {$c->COLUMN_NAME}\n";
    echo "Constraint Name: {$c->CONSTRAINT_NAME}\n";
    echo "References: {$c->REFERENCED_TABLE_NAME}.{$c->REFERENCED_COLUMN_NAME}\n";
    
    // Get constraint details
    $details = DB::select("
        SELECT 
            DELETE_RULE, 
            UPDATE_RULE
        FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_NAME = '{$c->CONSTRAINT_NAME}'
        AND CONSTRAINT_SCHEMA = DATABASE()
    ");
    
    if (!empty($details)) {
        echo "ON DELETE: {$details[0]->DELETE_RULE}\n";
        echo "ON UPDATE: {$details[0]->UPDATE_RULE}\n";
    }
    echo "\n";
}
