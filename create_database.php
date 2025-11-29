<?php
/**
 * Script to create database on RDS MySQL if it doesn't exist
 * Run this before migrations: php create_database.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$host = env('DB_HOST', '127.0.0.1');
$database = env('DB_DATABASE', 'buypower_db');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', '');

try {
    // Connect without specifying database
    $pdo = new PDO(
        "mysql:host={$host};port=" . env('DB_PORT', 3306),
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$database}'");
    $exists = $stmt->rowCount() > 0;
    
    if (!$exists) {
        echo "Creating database '{$database}'...\n";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✓ Database '{$database}' created successfully!\n";
    } else {
        echo "✓ Database '{$database}' already exists.\n";
    }
    
    // Verify connection to the database
    $pdo = new PDO(
        "mysql:host={$host};port=" . env('DB_PORT', 3306) . ";dbname={$database}",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Successfully connected to database '{$database}'\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

