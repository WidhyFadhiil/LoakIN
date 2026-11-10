<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table         = 'messages';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['offer_id', 'sender_id', 'body', 'created_at'];

    public function thread(int $offerId): array
    {
        return $this->select('messages.*, users.name AS sender_name')
            ->join('users', 'users.id = messages.sender_id')
            ->where('offer_id', $offerId)
            ->orderBy('messages.id')->findAll();
    }
}
