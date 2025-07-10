<?php
declare(strict_types=1);

class DateHandler
{
    private static function dateToText(DateTime $date): string
    {
        return $date->format('Y-m-d');
        // return $date->format('d-m-Y');
    }

    private static function incrementDate(string $date): string
    {
        
        $dateTime = new DateTime($date);
        // echo $dateTime;
        $dateTime->modify('+1 day');
        return self::dateToText($dateTime);
    }

    /**
     * @param string $from is exculsive
     * @param string $to is inculsive
     */
    public static function generateDateSpan(string $from, string $to): array
    {
        $result = [];

        while($from < $to) {
            $from = self::incrementDate($from);
            $result[] = $from;
        }
        
        return $result;
    }

    public static function getToday() {
        return self::dateToText(new DateTime());
    }
}