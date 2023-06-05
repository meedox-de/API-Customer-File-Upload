<?php

################# Directories #################
const CONFIG     = ROOT . 'config' . DIRECTORY_SEPARATOR;
const CONTROLLER = ROOT . 'controller' . DIRECTORY_SEPARATOR;
const DATA       = ROOT . 'data' . DIRECTORY_SEPARATOR;
const LIB        = ROOT . 'lib' . DIRECTORY_SEPARATOR;

################# includes #################
require_once(CONFIG . 'errorReporting.php');

################# configure timezone #################
date_default_timezone_set( 'UTC' );