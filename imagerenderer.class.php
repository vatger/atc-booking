<?php
require_once("conf.php");
require_once("booking.class.php");
require_once("color_mode.php");


class ImageRenderer
{

    /**
     * @param array<Booking> $booked_stations
     * @param DateTime $MASTER_DATE
     * @param int $next_airport_row_distance
     * @return false|GdImage
     */
    static function simple_render(array $booked_stations, DateTime $MASTER_DATE, int $next_airport_row_distance=1) : false|GdImage
    {
        $main_stations = [];
        foreach ($booked_stations as $s){
            if(!in_array($s->callsign, $main_stations)) {
                $main_stations[] = $s->callsign;
            }
        }
        sort($main_stations);
        return self::render([], $main_stations, $booked_stations, $MASTER_DATE, [], $next_airport_row_distance);
    }

    /**
     * @param array<string> $allstations
     * @param array<Booking> $booked_stations
     * @param DateTime $MASTER_DATE
     * @param int $next_airport_row_distance
     * @return false|GdImage
     */
    static function render_allshown(array $allstations, array $booked_stations, DateTime $MASTER_DATE, int $next_airport_row_distance=1) : false|GdImage
    {
        //sort($allstations);
        return self::render($allstations, $allstations, $booked_stations, $MASTER_DATE, [], $next_airport_row_distance);
    }


    /**
     * @param array<string> $min_stations
     * @param array<string> $main_stations
     * @param array<Booking> $booked_stations
     * @param DateTime $MASTER_DATE
     * @param $weeklyBookings
     * @param int $next_airport_row_distance
     * @return false|GdImage
     */
    static function render(array $min_stations, array $main_stations, array $booked_stations, DateTime $MASTER_DATE, $weeklyBookings, int $next_airport_row_distance=2) : false|GdImage
    {
        $booking_matrix = [];

        // Iterate over the stations and create a booking matrix
        for ($i = 0; $i < count($main_stations); $i++) {
            // First row contains the station
            $booking_matrix[$i][0] = $main_stations[$i];
            $day = clone $MASTER_DATE;
            // Loop over the next days
            for ($j = 1; $j < 8; $j++) {
                $found_booking = false;

                $cellObject = [];

                // Check if there is a booking at the specific date
                for ($k = 0; $k < count($booked_stations); $k++) {
                    $booking_date_start = DateTime::createFromFormat('Y-m-d\TH:i:s', $booked_stations[$k]->time_start);
                    $booking_date_end = DateTime::createFromFormat('Y-m-d\TH:i:s', $booked_stations[$k]->time_end);
                    // append all bookings as array
                    if ($booking_date_start->format("Y-m-d") === $day->format("Y-m-d") && $main_stations[$i] == $booked_stations[$k]->callsign) {
                        $cellContent = $booked_stations[$k]->abbreviation . " " . $booking_date_start->format("H") . "-" . $booking_date_end->format("H");
                        $isTraining = $booked_stations[$k]->training;
                        $isEvent = $booked_stations[$k]->event;
                        $cellObject[] = ["content" => $cellContent, "isTraining" => $isTraining, "isEvent" => $isEvent];
                        $found_booking = true;
                    }
                }
                // If no booking found, set it to open
                if (!$found_booking) {
                    if(in_array($main_stations[$i], $min_stations)){
                        $cellObject[] = ["content" => "open", "isTraining" => false, "isEvent" => false];
                    }
                    else {
                        $cellObject[] = ["content" => null, "isTraining" => null, "isEvent" => null];
                    }

                }

                $booking_matrix[$i][$j] = $cellObject;
                $day->add(new DateInterval("P1D"));
            }
        }

        $tempBookingMatrix = [];
        $additionalBookings = 0;
        for($i = 0; $i<count($booking_matrix); $i++){
            $hasBookings = false;
            for($j = 1; $j<count($booking_matrix[$i]); $j++){
                for($k = 0; $k<count($booking_matrix[$i][$j]);$k++){
                    if($booking_matrix[$i][$j][$k]['content'] !== null){
                        $hasBookings = true;
                        break;
                    }
                }

            }
            if($hasBookings){
                $tempBookingMatrix[] = $booking_matrix[$i];
                for ($k = 1; $k<count($booking_matrix[$i]); $k++){
                    if(count($booking_matrix[$i][$k]) > 1){
                        $additionalBookings = $additionalBookings + (count($booking_matrix[$i][$k]));
                    }
                }
            }
        }

// Set array with all users
        $all_users = [];
        for ($i = 0; $i < count($booked_stations); $i++) {
            $all_users[] = ["name" => $booked_stations[$i]->name, "abbreviation" => $booked_stations[$i]->abbreviation];
        }

        $booking_matrix = $tempBookingMatrix;
        $booking_groups = 0;


        $imageHeight = 5000; //temporary height
        $imageWidth = 820;

// Create images
        $im = @imagecreate($imageWidth, $imageHeight) or die("Cannot Initialize new GD image stream");
        $background_color = get_color_in_mode($im, "background");
        imagefill($im,0,0, $background_color);
        $color_black = get_color_in_mode($im, "black");
        $color_gray = get_color_in_mode($im, "gray");
        $color_red = get_color_in_mode($im, "red");
        $color_blue = get_color_in_mode($im, "blue");
        $color_orange = get_color_in_mode($im, "orange");



        $row = 1;
        $lineHeight = 20;
        $vertOffset = 4;
        $cell_width = 104;

// Set date header columns
        $day = clone $MASTER_DATE;
        for ($i = 1; $i < 8; $i++) {
            self::write_string($im, 3, $cell_width * $i, $row * $lineHeight, $day->format("D d.m."), $color_black);

            $day->add(new DateInterval('P1D'));
        }
        $row = 2;
        imageline($im, 0, $row * $lineHeight + 3, $imageWidth, $row * $lineHeight + 3, $color_gray);

        $row = 3;

// Draw matrix
        for ($i = 0; $i < count($booking_matrix); $i++) {
            $day = clone $MASTER_DATE;
            // Write station
            imageline($im, 0, ($row) * $lineHeight, $imageWidth, ($row) * $lineHeight, $color_gray);
            imageline($im, 0, ($row+1) * $lineHeight, $imageWidth, ($row+1) * $lineHeight, $color_gray);

            self::write_string($im, 3, 5, ($lineHeight * $row) + $vertOffset, $booking_matrix[$i][0], $color_black);
            $maxHeight = 1;
            //Loop over the days from the stations
            for ($j = 1; $j < count($booking_matrix[$i]); $j++) {
                // Check if there was a cell with more than one booking (so maxHeight is > 1)
                if (count($booking_matrix[$i][$j]) > $maxHeight) {
                    $maxHeight = count($booking_matrix[$i][$j]);
                }
                // Write bookings
                for ($k = 0; $k < count($booking_matrix[$i][$j]); $k++) {
                    $color = $color_gray;
                    if ($booking_matrix[$i][$j][$k] === null || $booking_matrix[$i][$j][$k]['content'] !== "open"){
                        $color = $color_black;
                    }
                    if($booking_matrix[$i][$j][$k]['content'] === "open" && self::isWeeklyPosition($weeklyBookings, $day->format("N"), $booking_matrix[$i][0])){
                        $color = $color_red;
                    }

                    if (isset($booking_matrix[$i][$j][$k]['isTraining']) && $booking_matrix[$i][$j][$k]['isTraining']) {
                        $color = $color_blue;
                    }

                    if (isset($booking_matrix[$i][$j][$k]['isEvent']) && $booking_matrix[$i][$j][$k]['isEvent']) {
                        $color = $color_orange;
                    }
                    // If is requested but open than make it to WANTED
                    if ($color === $color_red && $booking_matrix[$i][$j][$k]['content'] == "open") {
                        $booking_matrix[$i][$j][$k]['content'] = "WANTED";
                    }

                    self::write_string($im, 3, $cell_width * $j, ($lineHeight * $row) + $vertOffset, $booking_matrix[$i][$j][$k]['content'], $color);
                    $row++;
                }
                // Set row back to the first row if the current station for the next day
                $row = $row - count($booking_matrix[$i][$j]);
                $day->add(new DateInterval('P1D'));
            }
            // Add the maximum rows from the specific station at the specific date to the current row to be in the correct row again
            $row = $row + $maxHeight;

            // if next station is set and has other airport or center designator add empty line to it
            if (isset($booking_matrix[$i + 1]) && !self::next_station_is_same($booking_matrix[$i][0], $booking_matrix[$i + 1][0])) {
                $row += $next_airport_row_distance;//2;
            }
        }
        for ($i = 1; $i < 8; $i++) {
            imageline($im, ($cell_width *$i)-10 , 0, ($cell_width *$i)-10, $lineHeight*$row, $color_gray);
        }


        self::write_string($im, 3, 5, ($lineHeight * $row) + $vertOffset, "Wanted", $color_red);
        self::write_string($im, 3, $cell_width, ($lineHeight * $row) + $vertOffset, "Training", $color_blue);
        self::write_string($im, 3, $cell_width * 2 , ($lineHeight * $row) + $vertOffset, "Event", $color_orange);


        $row = $row + 3;



        // Remove duplicated users and sort them by their first names
        $all_users = array_map("unserialize", array_unique(array_map("serialize", $all_users)));
        array_multisort($all_users);

        $column = 0;

        // Draw Users
        for ($i = 0; $i < count($all_users); $i++) {
            self::write_string($im, 3, $column * 250 + 5, $lineHeight * $row, $all_users[$i]['abbreviation'] . ": " . $all_users[$i]['name'], $color_black);
            if ($column === 2) {
                $column = 0;
                $row++;
            } else {
                $column++;
            }
        }

        $row += 2;

        $generated_time = new DateTime();
        self::write_string($im, 2, 5, $lineHeight * $row, "Generated " . $generated_time->format("d.m.Y H:i:s"), $color_gray);

        $newHeight = ($row+1)*$lineHeight;


        $dstim = imagecreatetruecolor($imageWidth, $newHeight);
        imagecopyresized($dstim,$im,0,0,0,0,$imageWidth,$newHeight,$imageWidth,$newHeight);
        imagedestroy($im);
        return $dstim;
    }


    private static function next_station_is_same($currentElement, $nextElement) : bool
    {
        if (substr($currentElement, 0, 4) === substr($nextElement, 0, 4)) {
            return true;
        }
        return false;
    }

    /**
     * @param $weeklyObject
     * @param $day
     * @param $bookingStation
     * @return bool
     */
    private static function isWeeklyPosition($weeklyObject, $day, $bookingStation) : bool
    {
        if(!$weeklyObject) return false;
        $isStation = false;
        foreach($weeklyObject as $event){
            if ($event->day === $day){
                foreach($event->booking as $station){
                    $len = strlen($station);
                    $isStation = $isStation || (substr($bookingStation, 0, $len) === $station);
                }
            }
        }
        return $isStation;
    }

    private static function write_string($im, $font, $x, $y, $string, $color) : void
    {
        putenv('GDFONTPATH=' . realpath('.'));
        if (file_exists(dirname(__FILE__) . "/MyriadProRegular.ttf")) {
            imagettftext($im, 10, 0, $x, $y, $color, dirname(__FILE__) . "/MyriadProRegular.ttf", $string);
        } else {
            imagestring($im, $font, $x, $y, $string, $color);
        }
    }
}