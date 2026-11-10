<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ImpactCalculator;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $db = db_connect();

        $count = static fn (string $table, array $where = []) => $db->table($table)->where($where)->countAllResults();

        $byStatus = [];
        foreach ($db->query('SELECT status, COUNT(*) AS n FROM listings GROUP BY status')->getResultArray() as $r) {
            $byStatus[$r['status']] = (int) $r['n'];
        }

        return view('admin/dashboard', [
            'title'   => 'Dasbor admin',
            'stats'   => [
                'penyetor' => $count('users', ['role' => 'penyetor']),
                'pendaur'  => $count('users', ['role' => 'pendaur']),
                'pending'  => $count('users', ['verification_status' => 'pending']),
                'listings' => $count('listings'),
                'offers'   => $count('offers'),
            ],
            'byStatus' => $byStatus,
            'impact'   => (new ImpactCalculator())->summary(),
            'recent'   => $db->query(
                'SELECT l.id, l.title, l.status, l.created_at, u.name AS owner FROM listings l JOIN users u ON u.id = l.user_id ORDER BY l.id DESC LIMIT 8'
            )->getResultArray(),
        ]);
    }
}
