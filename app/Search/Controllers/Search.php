<?php

namespace app\Search\Controllers;

class Search
{
    private $db;
    private $map;

    public function __construct($api, $db)
    {
        $this->db = $db;
        $this->map = array(
            'title' => array(
                'db_field_name' => 'title',
                'db_field_type' => \PDO::PARAM_STR
            ),
            'short_synopsis' => array(
                'db_field_name' => 'short_synopsis',
                'db_field_type' => \PDO::PARAM_STR
            ),
            'image_pid' => array(
                'db_field_name' => 'image_pid',
                'db_field_type' => \PDO::PARAM_STR
            )
        );
    }

    public function execute($search_clause)
    {
        global $debug, $image_path, $image_extension;

        $db_connection_status = $this->db->connect();
        if (isset($db_connection_status) && $db_connection_status['status'] == 'error') {
            $response = $db_connection_status;
        } else {
            $results = $this->db->searchResults($this->map, $search_clause);

            foreach ($results as $result_key => $result) {
                $results[$result_key]['image'] = '';
                if (isset($results[$result_key]['image_pid']) && ($results[$result_key]['image_pid'] != '')) {
                    $results[$result_key]['image'] = $image_path . $results[$result_key]['image_pid'] . $image_extension;
                } else {
                    $results[$result_key]['image'] = '';
                }
            }
            $response['status'] = "success";
            $message['no_of_results'] = count($results);
            $message['results'] = $results;
            $response['message'] = $message;
        }
        return $response;
    }
}
