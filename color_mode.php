<?php
$avail_modes = ["dark", "bright"];

$MODE = "bright";
if (isset($_GET["theme"]) && $_GET["theme"] == "dark") {
    $MODE = "dark";
}

$colors_bright = [
    "background" => [240, 240, 240],
    "black" => [0, 0, 0],
    "gray" => [190, 190, 190],
    "red" => [190, 40, 40],
    "blue" => [072, 118, 255],
    "orange" => [255, 140, 0],
];

$colors_dark = [
    "background" => [23,23,23],
    "black" => [230, 230, 230],
    "gray" => [100, 100, 100],
    "red" => [190, 40, 40],
    "blue" => [072, 118, 255],
    "orange" => [255, 140, 0],
];

$all_colores = [
    "dark" => $colors_dark,
    "bright" => $colors_bright
];

define("_COLOR_MODE_", $MODE);
define("_COLOR_MODES_AVAIL_", $avail_modes);
define("_COLORS_ALL_", $all_colores);

function get_color_in_mode($im, string $identifier) : false|int
{
    $c = _COLORS_ALL_[_COLOR_MODE_][$identifier];
    return imagecolorallocate($im, $c[0], $c[1], $c[2]);
}
