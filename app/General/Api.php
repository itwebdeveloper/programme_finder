<?php

namespace app\General;

class Api
{
    private $api_endpoint;

    public function __construct($api_endpoint) {
        $this->api_endpoint = $api_endpoint;
    }

    public function sendRequest()
    {
        global $debug, $log_file;

        if($debug) {
            if (!is_writable($log_file)) {
                $response['status'] = "error";
                $response['message'] = "The file '". $log_file ."' is not writable.";
                return $response;
            }
        }

        try {
            // Build cURL request
            // Get cURL resource
            $curl = curl_init();

            $curl_opt_array = array(
                CURLOPT_URL => $this->api_endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache"
                )
            );

            curl_setopt_array($curl, $curl_opt_array);

            $curl_response = curl_exec($curl);
            $err = curl_error($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);

            if ($err) {
                $response['status'] = "error";
                $response['message'] = "A cURL error occurred while retrieving the results.\n" . $err;
                $response['message'] .= "Took ". $info['total_time'] ." seconds to send a request to ". $info['url'];
                $response['message'] .= print_r($info, true) . PHP_EOL;
                if($debug) {
                    file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
                }
                return $response;
            } else {
                if($debug) {
                    $response['status'] = "info";
                    $response['message'] =  "Successful response:\n". $curl_response;
                    file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
                }

                // Parse JSON response
                $curl_response_array = json_decode($curl_response);
                if (json_last_error() == JSON_ERROR_NONE) {
                    $response['status'] = "success";
                    $response['message'] = $curl_response_array;
                    return $response;
                } else {
                    $response['status'] = "error";
                    $response['message'] = "Invalid JSON response: ". json_last_error();
                    if($debug) {
                        file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
                    }
                    return $response;
                }
            }
        } catch (Exception $e) {
            $response['status'] = "error";
            $response['message'] = "Caught exception: ". $e->getMessage();
            if($debug) {
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }
            return $response;
        }
    }
}
