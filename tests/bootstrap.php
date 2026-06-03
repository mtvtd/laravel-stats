<?php

/*
 * Orchestra Testbench's default config files reference constants that are
 * deprecated on PHP 8.5 (e.g. PDO::MYSQL_ATTR_SSL_CA). Those notices have
 * nothing to do with this package's behaviour, so we suppress E_DEPRECATED
 * before the autoloader runs to keep the strict PHPUnit settings meaningful.
 */
error_reporting(error_reporting() & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

require __DIR__ . '/../vendor/autoload.php';
