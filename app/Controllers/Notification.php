<?php

namespace App\Controllers;

use App\Libraries\NotificationService;

class Notification extends BaseController
{
    public function index(): string
    {
        return view('notification/index', [
            'title' => 'Notifikasi',
            'items' => (new NotificationService())->latest($this->uid),
        ]);
    }

    /** Tandai dibaca lalu arahkan ke tujuan notifikasi. */
    public function open(int $id)
    {
        $n = (new NotificationService())->markRead($this->uid, $id);
        if (! $n) {
            $this->notFound();
        }

        return redirect()->to($n['url'] ? site_url($n['url']) : site_url('notifications'));
    }

    public function readAll()
    {
        (new NotificationService())->markAllRead($this->uid);

        return redirect()->to('/notifications')->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
