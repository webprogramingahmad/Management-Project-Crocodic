<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskDateRangeFilter
{
    public const TODAY = 'today';
    public const LAST_7_DAYS = 'last_7_days';
    public const LAST_30_DAYS = 'last_30_days';
    public const ALL_TIME = 'all_time';
    public const CUSTOM = 'custom';

    /**
     * @return array{
     *     preset: string,
     *     label: string,
     *     date: string|null,
     *     date_from: string|null,
     *     date_to: string|null
     * }
     */
    public static function fromRequest(Request $request): array
    {
        $preset = $request->query('date_filter');
        $date = $request->query('date');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if ($date) {
            $parsed = self::parseDate($date);

            return [
                'preset' => self::CUSTOM,
                'label' => $parsed ? $parsed->format('d/m/Y') : 'Custom date',
                'date' => $parsed?->toDateString(),
                'date_from' => $parsed?->toDateString(),
                'date_to' => $parsed?->toDateString(),
            ];
        }

        if ($preset === self::ALL_TIME) {
            return [
                'preset' => self::ALL_TIME,
                'label' => 'All time',
                'date' => null,
                'date_from' => null,
                'date_to' => null,
            ];
        }

        if ($preset === self::LAST_7_DAYS) {
            return self::daysBack(6, 'Last 7 days');
        }

        if ($preset === self::LAST_30_DAYS) {
            return self::daysBack(29, 'Last 30 days');
        }

        if ($preset === self::CUSTOM && ($dateFrom || $dateTo)) {
            $from = self::parseDate($dateFrom);
            $to = self::parseDate($dateTo);

            if ($from && $to && $from->greaterThan($to)) {
                [$from, $to] = [$to, $from];
            }

            return [
                'preset' => self::CUSTOM,
                'label' => self::customRangeLabel($from, $to),
                'date' => $from && $to && $from->isSameDay($to) ? $from->toDateString() : null,
                'date_from' => $from?->toDateString(),
                'date_to' => $to?->toDateString(),
            ];
        }

        return self::today();
    }

    /**
     * @param  array<string, mixed>  $filter
     * @return array<string, mixed>
     */
    public static function queryFilters(array $filter): array
    {
        return [
            'date' => $filter['date'] ?? null,
            'date_from' => $filter['date_from'] ?? null,
            'date_to' => $filter['date_to'] ?? null,
        ];
    }

    /**
     * @return array{
     *     preset: string,
     *     label: string,
     *     date: string,
     *     date_from: string,
     *     date_to: string
     * }
     */
    private static function today(): array
    {
        $today = now()->toDateString();

        return [
            'preset' => self::TODAY,
            'label' => 'Today',
            'date' => $today,
            'date_from' => $today,
            'date_to' => $today,
        ];
    }

    /**
     * @return array{
     *     preset: string,
     *     label: string,
     *     date: null,
     *     date_from: string,
     *     date_to: string
     * }
     */
    private static function daysBack(int $daysBack, string $label): array
    {
        return [
            'preset' => $daysBack === 6 ? self::LAST_7_DAYS : self::LAST_30_DAYS,
            'label' => $label,
            'date' => null,
            'date_from' => now()->subDays($daysBack)->toDateString(),
            'date_to' => now()->toDateString(),
        ];
    }

    private static function parseDate(?string $date): ?Carbon
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function customRangeLabel(?Carbon $from, ?Carbon $to): string
    {
        if ($from && $to) {
            return $from->isSameDay($to)
                ? $from->format('d/m/Y')
                : $from->format('d/m/Y') . ' - ' . $to->format('d/m/Y');
        }

        if ($from) {
            return 'From ' . $from->format('d/m/Y');
        }

        if ($to) {
            return 'Until ' . $to->format('d/m/Y');
        }

        return 'Custom date';
    }
}
