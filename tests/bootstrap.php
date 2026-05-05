<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap: ensure MySQL test database exists (CREATE DATABASE IF NOT EXISTS).
 * Uses DB_DATABASE from phpunit.xml / env and DB_HOST, DB_USERNAME, DB_PASSWORD, DB_PORT from .env
 * (loaded without overriding PHPUnit-injected variables).
 */
$projectRoot = dirname(__DIR__);

require $projectRoot.'/vendor/autoload.php';

if (is_file($projectRoot.'/.env')) {
    \Dotenv\Dotenv::createImmutable($projectRoot)->safeLoad();
}

$dbConnection = $_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: 'mysql';
$dbName = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '';
$host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
$user = $_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root';
$pass = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

if ($dbConnection === 'mysql' && $dbName !== '' && preg_match('/^[a-zA-Z0-9_]+$/', $dbName)) {
    try {
        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%s', $host, $port),
            is_string($user) ? $user : 'root',
            is_string($pass) ? $pass : '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $safe = str_replace('`', '``', $dbName);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.$safe.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    } catch (Throwable) {
        // Leave Laravel / PHPUnit to report connection errors if creation is not permitted.
    }
}
