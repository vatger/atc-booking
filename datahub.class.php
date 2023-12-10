<?php
include_once("conf.php");

class Datahub
{

    public static function get_weekly_data(string $key): array
    {
        $key = strtolower($key);
        $url = "https://raw.githubusercontent.com/VATGER-Nav/datahub/main/event_schedules/$key.json";
        $data = self::get_json($url, "datahub-event_schedules-$key.json");
        return $data;
    }

    public static function get_stations_data(string $key, bool $min_station_only): array
    {
        $url = 'https://raw.githubusercontent.com/VATGER-Nav/datahub/main/legacy/schedule.json';
        $data = self::get_json($url, 'datahub-legacy-schedule.json');
        $result = [];
        foreach ($data as $element) {
            if (strtoupper($element->name) == strtoupper($key)) {
                if ($min_station_only) {
                    $result = $element->schedule_minstation;
                } else {
                    $result = array_merge($element->schedule_group, $element->schedule_minstation);
                }
                return $result;
            }
        }
        $suffix_sort = function ($a, $b) {
            $order = ['CTR', 'APP', 'DEP', 'TWR', 'GND', 'DEL'];
            $suffixA = substr($a, -1 * 3);
            $suffixB = substr($b, -1 * 3);
            $indexA = array_search($suffixA, $order);
            $indexB = array_search($suffixB, $order);
            return $indexA - $indexB;
        };
        $prefix_sort = function ($a, $b) {
            $prefixA = substr($a, 0, 4);
            $prefixB = substr($b, 0, 4);
            return strcmp($prefixA, $prefixB);
        };
        usort($result, $suffix_sort);
        usort($result, $prefix_sort);
        return $result;
    }


    private static function get_json(string $url, string $fileName): object|array
    {
        $fileName = _BASE_PATH_ . "cache/datahub_" . $fileName . ".json";
        if (!file_exists($fileName) || filemtime($fileName) + 60 < time()) {
            $response_json = self::do_curl_request($url);
            if ($response_json != false) {
                file_put_contents($fileName, $response_json);
            }
        }
        $file_content = file_get_contents($fileName);
        return json_decode($file_content);
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
