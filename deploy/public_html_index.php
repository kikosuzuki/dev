<?php

/**
 * Xserver用 index.php
 *
 * このファイルを public_html/consul/ に設置してください。
 *
 * 推奨ディレクトリ構成:
 *   /home/ctwasia2/ycscampaign.com/
 *     ├── public_html/
 *     │   └── consul/          ← このファイル + public/ の中身(.htaccessなど)
 *     └── consul-app/          ← Laravelアプリ本体（public/以外の全て）
 *
 * ※ consul-app の名前は $laravelPath を変更すれば自由に変えられます。
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Laravel本体のパス（public_html の一つ上 → ドメインルート → consul-app/）
$laravelPath = dirname(__DIR__, 2) . '/consul-app';

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
