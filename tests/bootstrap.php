<?php

require_once __DIR__ . '/../vendor/autoload.php';

// https://github.com/vlucas/phpdotenv?tab=readme-ov-file#putenv-and-getenv
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/..');
$dotenv->load();
