<?php

namespace app\General;

class Database
{
    private $db_host;
    private $db_name;
    private $charset;
    private $db_user;
    private $db_pass;
    private $db;

    public function __construct($db_host, $db_user, $db_pass, $db_name, $charset)
    {
        $this->db_host = $db_host;
        $this->db_name = $db_name;
        $this->charset = $charset;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
    }

    public function connect()
    {
        global $debug, $log_file;

        try {
            $dsn = "mysql:host=". $this->db_host .";dbname=". $this->db_name .";charset=". $this->charset;
            $this->db = new \PDO($dsn, $this->db_user, $this->db_pass);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

            $response['status'] = "success";
            $result = "Connection created with success."; 
            $response['message'] = $result;
            if($debug) {
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }
            return $response;
        } catch (\PDOException $e) {
            $response['status'] = "error";
            $response['message'] = "Caught PDO exception: ". $e->getCode() ."]: ". $e->getMessage();

            if($debug) {
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }
            return $response;
        }
    }

    public function getResults($map)
    {
        try {
            foreach ($map as $result_field => $db_field_name) {
                $fields[] = $db_field_name['db_field_name'];
            }
            $fields[] = 'created_on';

            $query = 'SELECT ';
            $fields_imploded = implode(', ', $fields);
            $query .= $fields_imploded;
            $query .= ' FROM results;';

            $stmt = $this->db->query($query);

            $results = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = $row;
            }

            $stmt = null;
            $db = null;

            return $results;
        } catch (PDOException $e) {
            $response['status'] = "error";
            $response['message'] = "Caught PDO exception: ". $e->getCode() ."]: ". $e->getMessage();
            if($debug) {
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }
            return $response;
        }
    }

    public function getResult($map)
    {
        try {
            foreach ($map as $result_field => $db_field_name) {
                $fields[] = $db_field_name['db_field_name'];
            }
            $fields[] = 'created_on';

            $query = 'SELECT ';
            $fields_imploded = implode(', ', $fields);
            $query .= $fields_imploded;
            $query .= ' FROM results';
            $query .= ' LIMIT 1;';

            $stmt = $this->db->query($query);

            $results = array();
            while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $results[] = $row;
            }

            $stmt = null;
            $db = null;

            return $results;
        } catch (PDOException $e) {
            $response['status'] = "error";
            $response['message'] = "Caught PDO exception: ". $e->getCode() ."]: ". $e->getMessage();
            if($debug) {
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }
            return $response;
        }
    }

    public function searchResults($map, $clauses_array)
    {
        try {
            foreach ($map as $result_field => $db_field_name) {
                $fields[] = $db_field_name['db_field_name'];
            }

            $query = 'SELECT ';
            $fields_imploded = implode(', ', $fields);
            $query .= $fields_imploded;
            $query .= ' FROM results';
            $clauses_count = count($clauses_array);
            $clauses_counter = 0;
            foreach ($clauses_array as $clause_field => $clause_value) {
                if ($clauses_counter == 0) {
                    $query .= ' WHERE MATCH(';
                } else {
                    $query .= ' AND ';
                }
                $query .= $clause_field .') AGAINST (:'. $clause_field .' IN BOOLEAN MODE)';
                $clauses_counter++;
            }
            $query .= ';';

            $stmt = $this->db->prepare($query);

            foreach ($clauses_array as $clause_field => $clause_value) {
                $stmt->bindValue(':'. $clause_field, $clause_value.'*', \PDO::PARAM_STR);
            }
            $stmt->execute();

            $results = array();
            while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $results[] = $row;
            }

            $stmt = null;
            $db = null;

            return $results;
        } catch (PDOException $e) {
            $response['status'] = "error";
            $response['message'] = "Caught PDO exception: ". $e->getCode() ."]: ". $e->getMessage();
            if($debug) {
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }
            return $response;
        }
    }

    public function truncateResults()
    {
        global $debug;

        try {
            $query = 'TRUNCATE TABLE results;';
            $this->db->beginTransaction();
            $stmt = $this->db->query($query);

            $stmt = null;
            $db = null;

            $response['status'] = "success";
            $result = "Records truncated with success."; 
            $response['message'] = $result;
            if($debug) {
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }
            return $response;
        } catch (PDOException $e) {
            $response['status'] = "error";
            $response['message'] = "Caught PDO exception: ". $e->getCode() ."]: ". $e->getMessage();
            if($debug) {
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }
            return $response;
        }
    }

    public function insertResults($results, $map)
    {
        global $debug, $log_file;

        try {
            foreach ($map as $result_field => $db_field_name) {
                $fields[] = $db_field_name['db_field_name'];
            }
            $fields[] = 'created_on';

            $query = 'INSERT INTO results (';
            $fields_imploded = implode(', ', $fields);
            $query .= $fields_imploded;
            $query .= ') VALUES (:';
            $query .= str_replace(', ', ', :', $fields_imploded);
            $query .= ');';

            $stmt = $this->db->prepare($query);
            $created_on = time();

            foreach ($results as $result) {
                foreach ($map as $result_field => $db_field_name) {
                    $stmt->bindValue(':'. $db_field_name['db_field_name'], $result[$result_field], \PDO::PARAM_STR);
                }
                $stmt->bindValue(':created_on', $created_on, \PDO::PARAM_INT);
                $stmt->execute();
            }
            $this->db->commit();

            $response['status'] = "success";
            $result = "Records inserted with success."; 
            $response['message'] = $result;
            if($debug) {
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }

            return $response;

            $stmt = null;
            $this->db = null;
        } catch (PDOException $e) {
            $response['status'] = "error";
            $response['message'] = "Caught PDO exception: ". $e->getCode() ."]: ". $e->getMessage();
            if($debug) {
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }
            return $response;
        }
    }
}
