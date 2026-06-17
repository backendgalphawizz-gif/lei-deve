<?php

use Dotenv\Dotenv;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

if (file_exists($envFile = __DIR__.'/../.env')) {
    Dotenv::createImmutable(dirname($envFile))->safeLoad();
}

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$request = Request::capture();

$basePath = rtrim(parse_url(env('APP_URL', 'http://localhost/LEI'), PHP_URL_PATH) ?: '', '/');

if ($basePath !== '' && $basePath !== '/') {
    $uri = $request->server->get('REQUEST_URI', '');
    if (str_starts_with($uri, $basePath)) {
        $request->server->set('REQUEST_URI', substr($uri, strlen($basePath)) ?: '/');
    }
}

$app->handleRequest($request);
