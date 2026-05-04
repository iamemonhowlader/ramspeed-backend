<?php

$migrationsPath = __DIR__ . '/database/migrations';
$files = scandir($migrationsPath);

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    
    $filePath = $migrationsPath . '/' . $file;
    $content = file_get_contents($filePath);
    
    // 1. Fix Primary Key (ID)
    $content = str_replace("\$table->unsignedInteger('id');", "\$table->increments('id');", $content);
    
    // 2. Fix Trailing Space in dropIfExists
    // Matches patterns like Schema::dropIfExists('table_name '); and replaces with Schema::dropIfExists('table_name');
    $content = preg_replace("/Schema::dropIfExists\('(.+?) '\);/", "Schema::dropIfExists('$1');", $content);
    
    file_put_contents($filePath, $content);
    echo "Fixed: $file\n";
}

echo "All migrations processed.\n";
