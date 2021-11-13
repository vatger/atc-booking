<?php
require_once("../../conf.php");
require_once(_BASE_PATH_ . "datamanager.class.php");
require_once(_BASE_PATH_ . "imagerenderer.class.php");

$MASTER_DATE = new DateTime();
if (isset($_GET['nextWeek'])) {
    $MASTER_DATE->add(new DateInterval('P7D'));
}

$stationsFile = fopen(_DATA_PATH_ . "mil_allstations.csv", "r") or die("Unable to open allstations file!");
$allStationsString = fgets($stationsFile);
$all_stations =  explode(',',$allStationsString);

$booked_stations = DataManager::get_matched_station_array_bookings(clone $MASTER_DATE,7 , $all_stations);

$img = ImageRenderer::render_allshown($all_stations, $booked_stations, $MASTER_DATE);

header('Content-type: image/png');
imagepng($img);
imagedestroy($img);
