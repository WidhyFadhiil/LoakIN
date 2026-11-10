<?php

namespace App\Libraries;

use App\Models\NotificationModel;

/**
 * Notifikasi dalam web untuk kejadian penting
 * (pengajuan baru, diterima/ditolak, counter offer, pesan, selesai, kedaluwarsa, verifikasi).
 */
class NotificationService
{
    private NotificationModel $model;

    public function __construct()
    {
        $this->model = model(NotificationModel::class);
    }

    public function push(int $userId, string $type, string $title, ?string $body = null, ?string $url = null): void
    {
        $this->model->insert([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => mb_substr($title, 0, 150),
            'body'       => $body !== null ? mb_substr($body, 0, 255) : null,
            'url'        => $url,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function latest(int $userId, int $limit = 50): array
    {
        return $this->model->where('user_id', $userId)->orderBy('id', 'DESC')->findAll($limit);
    }

    public function markRead(int $userId, int $id): ?array
    {
        $n = $this->model->where('user_id', $userId)->find($id);
        if ($n) {
            $this->model->update($id, ['is_read' => 1]);
        }

        return $n;
    }

    public function markAllRead(int $userId): void
    {
        $this->model->where('user_id', $userId)->where('is_read', 0)->set('is_read', 1)->update();
    }
}
