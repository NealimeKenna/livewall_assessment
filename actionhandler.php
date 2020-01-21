<?php
/**
 * Created by PhpStorm.
 * User: tborn
 * Date: 1/19/2020
 * Time: 8:21 PM
 */

require_once('config.php');

$spotify = api\Spotify::getInstance();

if (isset($_POST)) {
    if (isset($_POST['func']) && $spotify->getAccessToken()) {
        $function = $_POST['func'];

        echo json_encode($spotify->$function());
        exit();
    }

    if (isset($_POST['logout'])) {
        session_destroy();
    }
}