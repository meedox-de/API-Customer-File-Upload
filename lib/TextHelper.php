<?php

namespace lib;


class TextHelper
{
    public const FORMAT_DATE              = 'date';
    public const FORMAT_DATETIME          = 'dateTime';
    public const FORMAT_CURRENCY          = 'currency';
    public const FORMAT_IN_ARRAY          = 'inArray';
    public const FORMAT_USER_NAME         = 'userName';
    public const FORMAT_CHECKBOX_DISABLED = 'checkboxDisabled';
    public const FORMAT_NOT_NULL          = 'notNull';
    public const FORMAT_YES_OR_NO         = 'yesOrNo';


    /**
     * htmlspecialchars - output encoding
     *
     * @param string|null $content
     * @param bool        $doubleEncode
     *
     * @return string
     */
    public static function encode(?string $content, bool $doubleEncode = true) :string
    {
        if( is_null( $content ) )
        {
            return '';
        }
        return htmlspecialchars( trim( $content ), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode );
    }

    /**
     * formats data in the specified format
     *
     * @param int|string|null $outputData
     * @param string          $format
     * @param array|null      $formatData
     *
     * @return string
     * @throws \Exception
     */
    public static function format(int|string|null $outputData, string $format = '', array|string|null $formatData = []) :string
    {
        return match ($format)
        {
            self::FORMAT_DATE => self::encode( self::date( $outputData ) ),
            self::FORMAT_DATETIME => self::encode( self::dateTime( $outputData ) ),
            self::FORMAT_CURRENCY => self::encode( self::currency( $outputData ) ),
            self::FORMAT_IN_ARRAY => self::encode( self::inArray( $outputData, $formatData ) ),
            self::FORMAT_USER_NAME => self::encode( self::userName( $outputData ) ),
            self::FORMAT_CHECKBOX_DISABLED => self::checkboxDisabled( $outputData ),
            self::FORMAT_NOT_NULL => self::encode( self::notNull( $outputData, $formatData ) ),
            self::FORMAT_YES_OR_NO => self::yesOrNo( (bool) $outputData ),
            default => self::encode( $outputData ),
        };
    }

    /**
     * @param string $date
     *
     * @return string
     * @throws \Exception
     */
    private static function date(string $date) :string
    {
        if( $date === '0000-00-00' )
        {
            return '';
        }

        $dateObject = new \DateTime( $date );
        return $dateObject->format( 'd.m.Y' );
    }

    /**
     * @param string $dateTime
     *
     * @return string
     * @throws \Exception
     */
    private static function dateTime(string $dateTime) :string
    {
        if( $dateTime === '0000-00-00 00:00:00' )
        {
            return '';
        }

        $dateTimeObject = new \DateTime( $dateTime );
        return $dateTimeObject->format( 'd.m.Y / H:i:s' );
    }

    /**
     * @param int $amount
     *
     * @return string
     */
    private static function currency(int $amount) :string
    {
        return number_format( $amount / 100, 2, ',', '.' ) . ' €';
    }

    /**
     * @param int|string|null $needle
     * @param array           $array
     *
     * @return string
     */
    private static function inArray(int|string|null $needle, array $array) :string
    {
        if( is_null( $needle ) )
        {
            return '';
        }
        if( empty( $array ) )
        {
            return '';
        }

        foreach( $array as $key => $value )
        {
            if( $key === $needle )
            {
                return $value;
            }
            elseif( $value === $needle )
            {
                return $key;
            }
        }

        return '';
    }

    /**
     * @param bool $checked
     *
     * @return string
     */
    private static function checkboxDisabled(bool $checked) :string
    {
        if( $checked )
        {
            return '<i class="fa-solid fa-check"></i>';
        }
        return '';
    }

    /**
     * @param mixed  $output
     * @param string $formatData
     *
     * @return string
     */
    private static function notNull(mixed $output, string $formatData) :string
    {
        if( is_null( $output ) )
        {
            return '';
        }

        return $formatData;
    }

    /**
     * @param bool $output
     *
     * @return string
     */
    private static function yesOrNo(bool $output) :string
    {
        if( $output )
        {
            return 'Ja';
        }
        return 'Nein';
    }
}