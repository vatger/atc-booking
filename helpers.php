<?php


/**
 * @param string $key
 * @param DateTime $MASTER_DATE
 * @return void
 */
function default_fir_render(string $key, DateTime $MASTER_DATE): void
{
    $weeklyBookings = Datahub::get_weekly_data($key);

    $min_stations = Datahub::get_stations_data($key, true);
    $main_stations = Datahub::get_stations_data($key, false);


    $booked_stations = DataManager::get_matched_station_array_bookings(clone $MASTER_DATE, 7, $main_stations);

    $img = ImageRenderer::render($min_stations, $main_stations, $booked_stations, $MASTER_DATE, $weeklyBookings);

    header('Content-type: image/png');
    imagepng($img);
    imagedestroy($img);
}
