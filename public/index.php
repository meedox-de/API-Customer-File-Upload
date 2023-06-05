<?php

use controller\ApiCustomerFileUpload;

define( 'ROOT', dirname( __DIR__, 2 ) . DIRECTORY_SEPARATOR . 'api-customer-file-upload' . DIRECTORY_SEPARATOR );
require_once('../config/config.php');

// autoloader
function autoload($class)
{
    $file = ROOT . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';

    if( file_exists( $file ) )
    {
        require_once $file;
    }
}

spl_autoload_register( 'autoload', true );


$token = \lib\FunctionalHelper::get( 't' );

if( !empty( $token ) && $token === 'v84kdod9bj9084jfa' )
{
    new ApiCustomerFileUpload();
}

die();