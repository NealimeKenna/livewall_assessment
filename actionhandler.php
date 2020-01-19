<?php
/**
 * Created by PhpStorm.
 * User: tborn
 * Date: 1/19/2020
 * Time: 8:21 PM
 */

if (isset($_POST) && isset($_POST['func'])) {
    \api\SpotifyCurl::$_POST['func']();
}