<?php
/**
 * Created by PhpStorm.
 * User: tborn
 * Date: 1/20/2020
 * Time: 7:48 PM
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();

define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . explode('?', $_SERVER['REQUEST_URI'], 2)[0]);

require_once('autoloader.php');