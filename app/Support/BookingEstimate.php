<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class BookingEstimate
{
    public static function boundary(CarbonImmutable $start, int $count, string $unit): CarbonImmutable
    {
        return match ($unit) {
            'bulan' => $start->addMonthsNoOverflow($count),
            'tahun' => $start->addYearsNoOverflow($count),
            'minggu' => $start->addWeeks($count),
            default => $start->addDays($count),
        };
    }

    private static function periodCount(CarbonImmutable $start, CarbonImmutable $end, string $unit): int
    {
        $days = max(1, (int) $start->diffInDays($end));
        if (in_array($unit, ['bulan', 'tahun'], true)) {
            $months = ($end->year - $start->year) * 12 + $end->month - $start->month;
            $units = max(0, $unit === 'tahun' ? intdiv($months, 12) : $months);
            $boundary = self::boundary($start, $units, $unit);
            $units = max(1, $units + ($boundary->lessThan($end) ? 1 : 0));
        } else {
            $units = $unit === 'minggu' ? (int) ceil($days / 7) : $days;
        }

        return $units;
    }

    public static function calculate(string $from, string $until, array $terms): array
    {
        $start = CarbonImmutable::parse($from)->startOfDay();
        $end = CarbonImmutable::parse($until)->startOfDay()->addDay();
        $unit = $terms['satuan'];
        $days = max(1, (int) $start->diffInDays($end));
        $units = self::periodCount($start, $end, $unit);
        $rate = (float) $terms['harga'];
        $lines = [['label' => 'Harga sewa pokok', 'rate' => $rate, 'units' => $units, 'unit' => $unit, 'total' => round($rate * $units, 2), 'calculation' => $units.' '.$unit.' × Rp '.number_format($rate, 0, ',', '.')]];
        $unknownUsage = false;
        foreach ($terms['biaya'] ?? [] as $fee) {
            $period = $fee['periode'] ?? $unit;
            $count = self::periodCount($start, $end, $period);
            $rate = (float) $fee['nominal'];
            $mode = $fee['metode'] ?? 'tetap';
            $amount = round($rate * $count, 2);
            $calculation = $count.' '.$period.' × Rp '.number_format($rate, 0, ',', '.');
            if ($mode === 'termasuk') {
                $amount = 0;
                $calculation = 'Termasuk harga sewa';
            } elseif ($mode === 'pemakaian') {
                $quantity = $fee['perkiraan_pemakaian'] ?? null;
                if ($quantity === null || $quantity === '') {
                    $amount = null;
                    $unknownUsage = true;
                    $calculation = 'Rp '.number_format($rate, 0, ',', '.').' / '.$fee['satuan_pemakaian'].' · pemakaian belum diperkirakan';
                } else {
                    $amount = round($rate * (float) $quantity * $count, 2);
                    $quantityLabel = rtrim(rtrim(number_format((float) $quantity, 2, ',', '.'), '0'), ',');
                    $calculation = $count.' '.$period.' × '.$quantityLabel.' '.$fee['satuan_pemakaian'].' × Rp '.number_format($rate, 0, ',', '.');
                }
            }
            $lines[] = ['label' => $fee['nama_biaya'].($mode === 'pemakaian' ? ' (estimasi)' : ''), 'rate' => $rate, 'units' => $count, 'unit' => $period, 'total' => $amount, 'calculation' => $calculation];
        }
        $minimum = $terms['durasi_minimal'] ?? '';
        $eligible = true;
        $minimumEnd = null;
        if (preg_match('/(?<![\d.,])([1-9][0-9]*)\s*(hari|minggu|bulan|tahun)\b/iu', $minimum, $match)) {
            $minimumEnd = self::boundary($start, (int) $match[1], strtolower($match[2]));
            $eligible = $end->greaterThanOrEqualTo($minimumEnd);
        }

        return [
            'units' => $units, 'unit' => $unit, 'days' => $days,
            'lines' => $lines, 'total' => round(array_sum(array_column($lines, 'total')), 2),
            'rental_total' => $lines[0]['total'], 'unknown_usage' => $unknownUsage,
            'rounded' => ! self::boundary($start, $units, $unit)->equalTo($end),
            'eligible' => $eligible, 'minimum' => $minimum,
            'minimum_end' => $minimumEnd?->subDay()->toDateString(),
        ];
    }
}
