<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

require_once(dirname(__FILE__) . '/../_config.php');
require_once(LIBS . '/class.app.php');
require_once(TESTS . '/db_generic.php');

spl_autoload_register('app::autoloader');