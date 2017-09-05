<?php
require_once(__DIR__ .'/../../config/config_backend.php');

use app\General\Api as Api;
use app\General\Database as Database;
use app\Proxy\Controllers\Proxy as Proxy;

$api = new Api($programmes_api_endpoint);
$db = new Database($db_host, $db_user, $db_pass, $db_name, $charset);
$proxy = new Proxy($api, $db, $valid_result_ttl);
$proxy->prepare();
