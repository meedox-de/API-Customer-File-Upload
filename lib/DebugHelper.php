<?php

namespace lib;


class DebugHelper
{
    /**
     * Prints a pre-formatted output of the given value
     *
     * @param null|object|array $value
     */
    public static function pre(null|object|array $value) :void
    {
        if( $value === null )
        {
            echo 'pre-output: null';
            return;
        }

        echo '<pre>';
        print_r( $value );
    }
}