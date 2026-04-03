<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$cachePath = sys_get_temp_dir().'/laravel-bootstrap-cache';

if (! is_dir($cachePath)) {
    mkdir($cachePath, 0777, true);
}

$_ENV['APP_SERVICES_CACHE'] = $cachePath.'/services.php';
$_SERVER['APP_SERVICES_CACHE'] = $cachePath.'/services.php';
$_ENV['APP_PACKAGES_CACHE'] = $cachePath.'/packages.php';
$_SERVER['APP_PACKAGES_CACHE'] = $cachePath.'/packages.php';
$_ENV['APP_CONFIG_CACHE'] = $cachePath.'/config.php';
$_SERVER['APP_CONFIG_CACHE'] = $cachePath.'/config.php';
$_ENV['APP_ROUTES_CACHE'] = $cachePath.'/routes-v7.php';
$_SERVER['APP_ROUTES_CACHE'] = $cachePath.'/routes-v7.php';
$_ENV['APP_EVENTS_CACHE'] = $cachePath.'/events.php';
$_SERVER['APP_EVENTS_CACHE'] = $cachePath.'/events.php';

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
	require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());