<?php
declare(strict_types=1);

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/Amsterdam');

require __DIR__.'/../vendor/autoload.php';

ini_set('zend.exception_ignore_args', false);
ini_set('xdebug.var_display_max_depth', 100);
