<?php
/**
 * Programme Finder Application
 *
 * @package Program-finder-application
 */

require_once(__DIR__ .'/config/config.php');

if (isset($_GET['search'])) {
    $search_query = $_GET['search'];

    $search_endpoint = $internal_proxy_endpoint. '?title='. $search_query;
    $response = file_get_contents($search_endpoint);
    $response_json = json_decode($response, true);
    if ($response_json['status'] == 'success') {
        $smarty->assign('search_query', $search_query);
        $smarty->assign('search_results', $response_json['message']);
    }
}

$smarty->assign('external_proxy_endpoint', $external_proxy_endpoint);
$smarty->assign('page', 'index');
$smarty->display('main'.TEMPLATE_EXT);