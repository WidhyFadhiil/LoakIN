<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'name', 'email', 'password', 'role', 'phone', 'city', 'address', 'bio', 'photo',
        'verification_status', 'verification_note', 'verification_doc', 'verified_at',
        'is_active', 'rating_avg', 'rating_count',
    ];
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    public const ROLE_SETOR  = 'penyetor';
    public const ROLE_DAUR   = 'pendaur';
    public const ROLE_ADMIN  = 'admin';

    /** Password di-hash otomatis setiap kali field password diisi. */
    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password'])) {
            if ($data['data']['password'] === '' || $data['data']['password'] === null) {
                unset($data['data']['password']);
            } else {
                $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            }
        }

        return $data;
    }

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    /** Pendaur ulang boleh mengajukan hanya jika sudah disetujui admin. */
    public static function canRecycle(?array $user): bool
    {
        return $user !== null
            && $user['role'] === self::ROLE_DAUR
            && $user['verification_status'] === 'approved';
    }
}
