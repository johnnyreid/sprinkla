<?php declare(strict_types=1);

require_once __DIR__ . '/router.php';

// Static GET
// In the URL -> http://localhost
// The output -> Index
get('/', 'public/sprinkla.php');

get('/scripts', 'scripts/test.php');

get('/gpio/read/$broadcomNumber', 'scripts/gpio/read');

get('/gpio/toggle/$broadcomNumber', 'scripts/gpio/toggle');