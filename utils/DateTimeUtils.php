<?php

class DateTimeUtils
{
    public static function createDateTime($fecha)
    {
        return \DateTime::createFromFormat('d/m/Y', $fecha);
    }

    public static function createDateTimeNow()
    {
        return new \DateTime();
    }
}
