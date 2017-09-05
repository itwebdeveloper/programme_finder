<?php

namespace app\Proxy\Controllers;

class Proxy
{
    private $api;
    private $db;
    private $map;
    private $valid_result_ttl;

    public function __construct($api, $db, $valid_result_ttl)
    {
        $this->api = $api;
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
        $this->valid_result_ttl = $valid_result_ttl;
    }

    public function execute()
    {
        $response = $this->api->sendRequest();
        if (isset($response) && $response['status'] == 'success') {
            if(isset($response['message']->atoz->tleo_titles)) {
                foreach ($response['message']->atoz->tleo_titles as $tleo_title) {
                    $programme['title'] = $tleo_title->programme->title;
                    $programme['short_synopsis'] = $tleo_title->programme->short_synopsis;
                    if(isset($tleo_title->programme->image->pid)) {
                        $programme['image_pid'] = $tleo_title->programme->image->pid;
                    }
                    $programmes[] = $programme;
                }
            } else {
                $response['status'] = "error";
                $response['message'] = "The response has the following content: ". $response['message'];
                if($debug) {
                    file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
                }
                echo json_encode($response);
                exit;
            }

            $response = $this->db->insertResults($programmes, $this->map);
            return $response;
        }
    }

    public function prepare()
    {
        global $debug;

        $created_on_min = time() - $this->valid_result_ttl;
        $db_connection_status = $this->db->connect();
        if (isset($db_connection_status) && $db_connection_status['status'] == 'error') {
            $response = $db_connection_status;
        } else {
            $results = $this->db->getResult($this->map);
        }

        if (count($results) == 0) {
            // Because it contains $this->db->truncateResults();
            $this->db->truncateResults();
            $response = $this->execute();
        } else {
            if ($results[0]['created_on'] < $created_on_min) {
                $this->db->truncateResults();
                $response = $this->execute();
            } else {
                $response['status'] = "success";
                $response['message'] = $results;
            }

            if($debug) {
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }
        }

        if ($response['status'] == "error"){
            return false;
        } else {
            return true;
        }
    }
}
