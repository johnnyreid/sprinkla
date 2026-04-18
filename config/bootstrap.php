<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helpers/log.php';


// Tell PHP that we're using UTF-8 strings until the end of the script
mb_internal_encoding('UTF-8');

// Tell PHP that we'll be outputting UTF-8 to the browser
mb_http_output('UTF-8');

setlocale(LC_ALL, 'en_AU.UTF-8');

date_default_timezone_set('Australia/Brisbane');

