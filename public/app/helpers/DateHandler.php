<?php
declare(strict_types=1);

class DateHandler
{
    private static function dateToText(DateTime $date): string
    {
        return $date->format('Y-m-d');
    }

    private static function incrementDate(string $date): string
    {
        $dateTime = new DateTime($date);
        $dateTime->modify('+1 day');
        return self::dateToText($dateTime);
    }

    public static function generateDateSpan(string $lastDate): array
    {
        $today = self::dateToText(new DateTime());
        $result = [];

        while($lastDate < $today) {
            $lastDate = self::incrementDate($lastDate);
            $result[] = $lastDate;
        }
        
        return $result;
    }
}