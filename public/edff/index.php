<?php
require_once ("../../conf.php");
require_once(_BASE_PATH_ . "datamanager.class.php");
require_once(_BASE_PATH_ . "imagerenderer.class.php");

$MASTER_DATE = new DateTime();
if (isset($_GET['nextWeek'])) {
    $MASTER_DATE->add(new DateInterval('P7D'));
}


$stationsFile = fopen(_DATA_PATH_ . "edff_allstations.csv", "r") or die("Unable to open allstations file!");
$minStationsFile = fopen(_DATA_PATH_ . "edff_minstations.csv", "r") or die("Unable to open minstations file!");
$allStationsString = fgets($stationsFile);
$minStationsString = fgets($minStationsFile);


$bookingsString = file_get_contents(_DATA_PATH_ . "edff_weekly.json") or die("Unable to open weekly file!");
$weeklyBookings = json_decode($bookingsString);

$min_stations = explode(',',$minStationsString);
$main_stations =  explode(',',$allStationsString);

$booked_stations = DataManager::get_matched_station_array_bookings(clone $MASTER_DATE,7 , $main_stations);

$img = ImageRenderer::render($min_stations, $main_stations, $booked_stations, $MASTER_DATE, $weeklyBookings);

header('Content-type: image/png');
imagepng($img);
imagedestroy($img);
