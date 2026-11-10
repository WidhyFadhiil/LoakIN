<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id', 'type', 'title', 'body', 'url', 'is_read', 'created_at'];

    public function unreadCount(int $userId): int
    {
        return $this->where('user_id', $userId)->where('is_read', 0)->countAllResults();
    }
}
