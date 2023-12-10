<?php
require_once("../../conf.php");
require_once(_BASE_PATH_ . "datamanager.class.php");
require_once(_BASE_PATH_ . "imagerenderer.class.php");
require_once(_BASE_PATH_ . "helpers.php");

$MASTER_DATE = new DateTime();
if (isset($_GET['nextWeek'])) {
    $MASTER_DATE->add(new DateInterval('P7D'));
}

$match_function = function (string $station_ident) {
    return str_ends_with($station_ident, '_TWR') || str_starts_with($station_ident, 'EDXX_');
};
$booked_stations = DataManager::get_matched_bookings(clone $MASTER_DATE, 7, $match_function);


$img = ImageRenderer::simple_render($booked_stations, $MASTER_DATE);

header('Content-type: image/png');
imagepng($img);
imagedestroy($img);
