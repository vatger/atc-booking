<?php
require_once("../../conf.php");
require_once(_BASE_PATH_ . "datamanager.class.php");
require_once(_BASE_PATH_ . "datahub.class.php");
require_once(_BASE_PATH_ . "imagerenderer.class.php");

$MASTER_DATE = new DateTime();
if (isset($_GET['nextWeek'])) {
    $MASTER_DATE->add(new DateInterval('P7D'));
}

$key = 'edll';

$weeklyBookings = Datahub::get_weekly_data($key);

$min_stations = Datahub::get_stations_data($key, true);
$main_stations = Datahub::get_stations_data($key, false);


$booked_stations = DataManager::get_matched_station_array_bookings(clone $MASTER_DATE, 7, $main_stations);

$img = ImageRenderer::render($min_stations, $main_stations, $booked_stations, $MASTER_DATE, $weeklyBookings);

header('Content-type: image/png');
imagepng($img);
imagedestroy($img);
