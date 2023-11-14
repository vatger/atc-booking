<?php
include_once("conf.php");
include_once("booking.class.php");


class DataManager
{
    /**
     * @param DateTime $start_date
     * @param int $length_days
     * @param array<string> $stations
     * @return array<Booking>
     */
    static function get_matched_station_array_bookings(DateTime $start_date, int $length_days, array $stations) : array
    {
        $match_function = function (string $station_ident) use ($stations)  :bool
        {
            for ($j = 0; $j < count($stations); $j++) {
                if (str_contains($station_ident, $stations[$j])) {
                    return true;
                }
            }
            return false;
        };
        return self::get_matched_bookings($start_date,$length_days,$match_function);
    }

    /**
     * @param DateTime $start_date
     * @param int $length_days
     * @param $match_function
     * @return array<Booking>
     */
    static function get_matched_bookings(DateTime $start_date, int $length_days, $match_function) : array
    {
        $atc_bookings = self::get_data($start_date, $length_days);
        $requested_bookings = [];
        foreach ($atc_bookings as $key => $booking) {
            if (isset($booking->station)) {
                $station_ident = $booking->station->ident;
                if($match_function($station_ident)){
                    $requested_bookings[] = new Booking(
                        $booking->controller->username_short,
                        $booking->starts_at,
                        $booking->ends_at,
                        $station_ident,
                        $booking->training,
                        $booking->event
                    );
                }
            }
        }
        return $requested_bookings;
    }

    private static function get_data(DateTime $date_start, int $length_days): mixed
    {
        try {
            return self::get_json($date_start, $length_days);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get the bookings as json object
     * @throws Exception
     */
    private static function get_json(DateTime $date_start, int $length_days): mixed
    {
        $fileName = _BASE_PATH_ . "cache/booking_" . $date_start->format("Y-m-d") . ".json";
        if (!file_exists($fileName) || filemtime($fileName) + 60 < time()) {
            $response_json = self::do_curl_request($date_start, $length_days);
            if($response_json != false){
                file_put_contents($fileName, $response_json);
                self::cleanup_cache();
            }
        }
        $file_content = file_get_contents($fileName);
        return json_decode($file_content);
    }

    private static function cleanup_cache(): void
    {
        $files = scandir(_BASE_PATH_ . "cache/");
        if(!$files) return;
        foreach ($files as $f) {
            $fileName = _BASE_PATH_ . "cache/" . $f;
            if (filemtime($fileName) + 60 * 30 < time()) unlink($fileName);
        }
    }

    /**
     * @throws Exception
     */
    private static function do_curl_request(DateTime $date_start, int $length_days): bool|string
    {
        $curl = curl_init();
        $date_start_string = $date_start->format("Y-m-d");

        $date_end = $date_start->add(new DateInterval('P' . $length_days . 'D'));
        $date_end_string = $date_end->format("Y-m-d");

        $url = API_PATH . "booking/$date_start_string/$date_end_string";

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
                'X-Requested-With: XMLHttpRequest',
                'Content-Type: application/json',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'Sec-Fetch-Dest: empty',
                'Accept-Language: de,en;q=0.9',
                'Accept: application/json, text/plain, */*',
                'Authorization: Token ' . API_KEY
            ),
        ));

        $response = curl_exec($curl);

        if (!$response) throw new Exception("Unable CURL REQ");
        curl_close($curl);
        return $response;
    }

}
