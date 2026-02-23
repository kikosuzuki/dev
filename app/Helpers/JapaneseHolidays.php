<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Japanese national holidays calculator.
 *
 * Covers all holidays defined in 国民の祝日に関する法律 (Act on National Holidays),
 * including Happy Monday holidays, substitute holidays (振替休日),
 * and citizen's holidays (国民の休日).
 */
class JapaneseHolidays
{
    /**
     * Check if a given date is a Japanese national holiday.
     */
    public static function isHoliday(Carbon $date): bool
    {
        $holidays = static::getHolidaysForYear($date->year);

        return in_array($date->toDateString(), $holidays);
    }

    /**
     * Get all holiday dates for a given year as an array of 'Y-m-d' strings.
     */
    public static function getHolidaysForYear(int $year): array
    {
        $holidays = [];

        // --- Fixed-date holidays ---

        // 元日 (New Year's Day)
        $holidays[] = Carbon::create($year, 1, 1)->toDateString();

        // 建国記念の日 (National Foundation Day)
        $holidays[] = Carbon::create($year, 2, 11)->toDateString();

        // 天皇誕生日 (Emperor's Birthday) - Feb 23 from 2020
        if ($year >= 2020) {
            $holidays[] = Carbon::create($year, 2, 23)->toDateString();
        }

        // 昭和の日 (Showa Day)
        $holidays[] = Carbon::create($year, 4, 29)->toDateString();

        // 憲法記念日 (Constitution Memorial Day)
        $holidays[] = Carbon::create($year, 5, 3)->toDateString();

        // みどりの日 (Greenery Day)
        $holidays[] = Carbon::create($year, 5, 4)->toDateString();

        // こどもの日 (Children's Day)
        $holidays[] = Carbon::create($year, 5, 5)->toDateString();

        // 山の日 (Mountain Day) - Aug 11 from 2016
        if ($year >= 2016) {
            $holidays[] = Carbon::create($year, 8, 11)->toDateString();
        }

        // 文化の日 (Culture Day)
        $holidays[] = Carbon::create($year, 11, 3)->toDateString();

        // 勤労感謝の日 (Labor Thanksgiving Day)
        $holidays[] = Carbon::create($year, 11, 23)->toDateString();

        // --- Happy Monday holidays ---

        // 成人の日 (Coming of Age Day) - 2nd Monday of January
        $holidays[] = static::nthWeekday($year, 1, Carbon::MONDAY, 2)->toDateString();

        // 海の日 (Marine Day) - 3rd Monday of July
        $holidays[] = static::nthWeekday($year, 7, Carbon::MONDAY, 3)->toDateString();

        // 敬老の日 (Respect for the Aged Day) - 3rd Monday of September
        $holidays[] = static::nthWeekday($year, 9, Carbon::MONDAY, 3)->toDateString();

        // スポーツの日 (Sports Day) - 2nd Monday of October
        $holidays[] = static::nthWeekday($year, 10, Carbon::MONDAY, 2)->toDateString();

        // --- Calculated holidays ---

        // 春分の日 (Vernal Equinox Day) - around March 20-21
        $holidays[] = Carbon::create($year, 3, static::vernalEquinoxDay($year))->toDateString();

        // 秋分の日 (Autumnal Equinox Day) - around September 22-23
        $holidays[] = Carbon::create($year, 9, static::autumnalEquinoxDay($year))->toDateString();

        // --- Substitute holidays (振替休日) ---
        // When a holiday falls on Sunday, the next non-holiday weekday becomes a substitute.
        $baseHolidays = $holidays; // copy before adding substitutes
        foreach ($baseHolidays as $h) {
            $hDate = Carbon::parse($h);
            if ($hDate->dayOfWeek === Carbon::SUNDAY) {
                $substitute = $hDate->copy()->addDay();
                while (in_array($substitute->toDateString(), $holidays)) {
                    $substitute->addDay();
                }
                $holidays[] = $substitute->toDateString();
            }
        }

        // --- Citizen's Holiday (国民の休日) ---
        // A weekday sandwiched between two holidays becomes a holiday.
        foreach ($baseHolidays as $h) {
            $hDate = Carbon::parse($h);
            $nextDay = $hDate->copy()->addDay();
            $dayAfter = $hDate->copy()->addDays(2);

            if (
                $nextDay->dayOfWeek !== Carbon::SUNDAY &&
                !in_array($nextDay->toDateString(), $holidays) &&
                in_array($dayAfter->toDateString(), $baseHolidays)
            ) {
                $holidays[] = $nextDay->toDateString();
            }
        }

        sort($holidays);

        return array_unique($holidays);
    }

    /**
     * Get the n-th occurrence of a specific weekday in a given month.
     */
    private static function nthWeekday(int $year, int $month, int $dayOfWeek, int $nth): Carbon
    {
        $date = Carbon::create($year, $month, 1);

        // Move to first occurrence of the target weekday
        while ($date->dayOfWeek !== $dayOfWeek) {
            $date->addDay();
        }

        // Move to the nth occurrence
        $date->addWeeks($nth - 1);

        return $date;
    }

    /**
     * Approximate vernal equinox day for a given year.
     * Based on the formula from the National Astronomical Observatory of Japan.
     */
    private static function vernalEquinoxDay(int $year): int
    {
        if ($year >= 1980 && $year <= 2099) {
            return (int) floor(20.8431 + 0.242194 * ($year - 1980) - (int) floor(($year - 1980) / 4));
        }
        if ($year >= 2100 && $year <= 2150) {
            return (int) floor(21.8510 + 0.242194 * ($year - 1980) - (int) floor(($year - 1980) / 4));
        }

        return 21; // fallback
    }

    /**
     * Approximate autumnal equinox day for a given year.
     * Based on the formula from the National Astronomical Observatory of Japan.
     */
    private static function autumnalEquinoxDay(int $year): int
    {
        if ($year >= 1980 && $year <= 2099) {
            return (int) floor(23.2488 + 0.242194 * ($year - 1980) - (int) floor(($year - 1980) / 4));
        }
        if ($year >= 2100 && $year <= 2150) {
            return (int) floor(24.2488 + 0.242194 * ($year - 1980) - (int) floor(($year - 1980) / 4));
        }

        return 23; // fallback
    }
}
