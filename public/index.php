<?php
require_once ("../conf.php");
require_once(_BASE_PATH_ . "color_mode.php");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VATGER Bookings - Settings</title>
</head>
<body>
    <text>Current mode set: <code><?php echo (_COLOR_MODE_);?></text>

    <ul>
        <?php foreach (_COLOR_MODES_AVAIL_ as $mode) {?>
        <li>
            Mode <code><?php echo ($mode);?></code>
            <a href="?set_mode_<?php echo($mode);?>">set</a>
        </li>
        <?php } ?>
    </ul>

</body>
</html>

