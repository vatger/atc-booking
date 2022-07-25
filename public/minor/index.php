<?php
require_once ("../../conf.php");
require_once(_BASE_PATH_ . "datamanager.class.php");
require_once(_BASE_PATH_ . "imagerenderer.class.php");

$MASTER_DATE = new DateTime();
if (isset($_GET['nextWeek'])) {
    $MASTER_DATE->add(new DateInterval('P7D'));
}

$match_function = function (string $station_ident) : bool
{
    $major_airports = ["EDDF", "EDDL", "EDDK", "EDDM", "EDDH", "EDDB"];
    foreach ($major_airports as $airport)
    {
        if(str_starts_with($station_ident, $airport)) return false;
    }
    if(str_ends_with($station_ident , '_CTR')) return false;
    return true;
};
$booked_stations = DataManager::get_matched_bookings(clone $MASTER_DATE,7 , $match_function);


$img = ImageRenderer::simple_render($booked_stations, $MASTER_DATE);

header('Content-type: image/png');
imagepng($img);
imagedestroy($img);
