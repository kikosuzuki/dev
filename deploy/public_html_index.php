<?php

/**
 * Xserver用 index.php
 *
 * このファイルを public_html/ に設置してください。
 * Laravelアプリ本体は ../laravel/ に配置する前提です。
 *
 * ディレクトリ構成:
 *   /home/ctwasia2/ドメイン名/
 *     ├── public_html/     ← このファイル + public/ の中身
 *     └── laravel/          ← Laravelアプリ本体（public/以外の全て）
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Laravel本体のパス（public_htmlの一つ上の laravel/ ディレクトリ）
$laravelPath = dirname(__DIR__) . '/laravel';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelPath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelPath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelPath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
