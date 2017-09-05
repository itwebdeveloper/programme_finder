<?php
ini_set('display_startup_errors', 1);
ini_set('display_error', 1);
error_reporting(E_ALL);
date_default_timezone_set("UTC"); 

/* proxy.php settings START */
$debug = false; // If true the script prints additional debug lines to video
$log_file = "/var/log/programme_finder/debug.log";
/* proxy.php settings END */

$db_host = "localhost";
$db_user = "foouser";
$db_pass = "abc";
$db_name = "programme_finder";
$charset = 'utf8';

$programmes_api_endpoint = "http://10.0.0.10/fake/source-data.json";
$valid_result_ttl = 300; // Consider valid the results retrieved in the last 5 minutes
$image_path = '';
$image_extension = '.jpg';

define("ROOT_DIR", "/var/www/programme_finder/");

require __DIR__ . '/../vendor/autoload.php';