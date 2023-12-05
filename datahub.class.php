<?php

class Datahub
{

    private static function get_json()
    {
        /*
        $fileName = _BASE_PATH_ . "cache/booking_" . ".json";
        if (!file_exists($fileName) || filemtime($fileName) + 60 < time()) {
            $response_json = self::do_curl_request();
            if ($response_json != false) {
                file_put_contents($fileName, $response_json);
                //self::cleanup_cache();
            }
        }
        $file_content = file_get_contents($fileName);
        return json_decode($file_content);
        */
    }

    /**
     * @throws Exception
     */
    private static function do_curl_request(string $url): bool|string
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json, text/plain, */*',
            ),
        ));

        $response = curl_exec($curl);

        if (!$response) throw new Exception("Unable CURL REQ");
        curl_close($curl);
        return $response;
    }

}
