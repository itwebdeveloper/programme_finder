<?php
require_once(__DIR__ .'/../../config/config_backend.php');

use app\General\Api as Api;
use app\General\Database as Database;
use app\Proxy\Controllers\Proxy as Proxy;
use app\Search\Controllers\Search as Search;

$api = new Api($programmes_api_endpoint);
$db = new Database($db_host, $db_user, $db_pass, $db_name, $charset);
$proxy = new Proxy($api, $db, $valid_result_ttl);
$proxy->prepare();

$search = new Search($api, $db);

$title = '';
if (isset ($_GET['title'])) {
	$title = $_GET['title'];
}

$search_clause = array(
	'title' => $title
);
$response = $search->execute($search_clause);

// For 4.3.0 <= PHP <= 5.4.0
if (!function_exists('http_response_code'))
{
    function http_response_code($newcode = NULL)
    {
        static $code = 200;
        if($newcode !== NULL)
        {
            header('X-PHP-Response-Code: '.$newcode, true, $newcode);
            if(!headers_sent())
                $code = $newcode;
        }       
        return $code;
    }
}

header('Content-Type: application/json');

if (isset($response['status']) && $response['status'] == 'error') {
	http_response_code(400);
} else {
	http_response_code(200);
}

echo json_encode($response);