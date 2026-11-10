<?php

namespace App\Libraries;

/**
 * Menghitung dampak lingkungan. Hanya listing berstatus "selesai"
 * (barang benar-benar berpindah tangan) yang dihitung.
 */
class ImpactCalculator
{
    /**
     * @param int|null    $userId null = seluruh platform
     * @param string|null $role   'penyetor' | 'pendaur' (menentukan sisi mana yang dihitung)
     */
    public function summary(?int $userId = null, ?string $role = null): array
    {
        $db = db_connect();
        $b  = $db->table('listings l')
            ->select('l.id, l.weight_kg, l.completed_at, c.name AS category, c.co2_factor')
            ->join('categories c', 'c.id = l.category_id')
            ->where('l.status', 'selesai');

        if ($userId !== null && $role === 'pendaur') {
            $b->join('offers o', 'o.id = l.accepted_offer_id')->where('o.recycler_id', $userId);
        } elseif ($userId !== null) {
            $b->where('l.user_id', $userId);
        }

        $rows = $b->get()->getResultArray();

        $kg = 0.0;
        $co2 = 0.0;
        $byCat = [];
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[date('Y-m', strtotime("first day of -{$i} month"))] = 0.0;
        }

        foreach ($rows as $r) {
            $w    = (float) $r['weight_kg'];
            $kg  += $w;
            $co2 += $w * (float) $r['co2_factor'];
            $byCat[$r['category']] = ($byCat[$r['category']] ?? 0) + $w;
            $key = substr((string) $r['completed_at'], 0, 7);
            if (isset($months[$key])) {
                $months[$key] += $w;
            }
        }
        arsort($byCat);

        return [
            'count'       => count($rows),
            'total_kg'    => round($kg, 1),
            'total_co2'   => round($co2, 1),
            'trees'       => (int) round($co2 / max(1, config('Loakin')->co2PerTree)),
            'by_category' => array_map(static fn ($v) => round($v, 1), $byCat),
            'monthly'     => array_map(static fn ($v) => round($v, 1), $months),
        ];
    }
}
