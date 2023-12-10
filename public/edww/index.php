<?php
require_once("../../conf.php");
require_once(_BASE_PATH_ . "datamanager.class.php");
require_once(_BASE_PATH_ . "datahub.class.php");
require_once(_BASE_PATH_ . "imagerenderer.class.php");

$MASTER_DATE = new DateTime();
if (isset($_GET['nextWeek'])) {
    $MASTER_DATE->add(new DateInterval('P7D'));
}

$key = 'edww';

default_fir_render($key, $MASTER_DATE);
