<?php

/**
 * Helper tampilan & sesi LoakIN (dimuat otomatis lewat Config\Autoload::$helpers).
 */

use App\Models\NotificationModel;
use App\Models\UserModel;

if (! function_exists('rupiah')) {
    function rupiah($n): string
    {
        return 'Rp ' . number_format((float) $n, 0, ',', '.');
    }
}

if (! function_exists('price_label')) {
    /** "Gratis", "Rp 25.000", atau "Rp 25.000 (nego)" untuk kartu listing. */
    function price_label(array $l): string
    {
        if ($l['price_type'] === 'gratis') {
            return 'Gratis';
        }

        return rupiah($l['price']) . ($l['price_type'] === 'nego' ? ' (nego)' : '');
    }
}

if (! function_exists('time_ago')) {
    function time_ago(?string $datetime): string
    {
        if (! $datetime) {
            return '-';
        }
        $diff = time() - strtotime($datetime);
        if ($diff < 60) {
            return 'baru saja';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' menit lalu';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' jam lalu';
        }
        if ($diff < 86400 * 7) {
            return floor($diff / 86400) . ' hari lalu';
        }

        return tanggal($datetime);
    }
}

if (! function_exists('tanggal')) {
    function tanggal(?string $datetime, bool $withTime = false): string
    {
        if (! $datetime) {
            return '-';
        }
        $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $t     = strtotime($datetime);
        $s     = date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);

        return $withTime ? $s . ', ' . date('H:i', $t) : $s;
    }
}

if (! function_exists('listing_status_label')) {
    function listing_status_label(string $s): string
    {
        return config('Loakin')->listingStatuses[$s] ?? ucfirst($s);
    }
}

if (! function_exists('offer_status_label')) {
    function offer_status_label(string $s): string
    {
        return config('Loakin')->offerStatuses[$s] ?? ucfirst($s);
    }
}

if (! function_exists('status_class')) {
    /** Kelas CSS untuk lencana status (listing maupun pengajuan). */
    function status_class(string $s): string
    {
        return match ($s) {
            'tersedia', 'diterima', 'approved' => 'is-ok',
            'diproses', 'menunggu', 'counter', 'pending' => 'is-warn',
            'selesai' => 'is-done',
            default => 'is-off',
        };
    }
}

if (! function_exists('current_user')) {
    /** Data user yang sedang login (di-cache per request). */
    function current_user(): ?array
    {
        static $cache = [];
        $id = session()->get('user_id');
        if (! $id) {
            return null;
        }
        if (! array_key_exists($id, $cache)) {
            $cache[$id] = model(UserModel::class)->find($id);
        }

        return $cache[$id];
    }
}

if (! function_exists('logged_in')) {
    function logged_in(): bool
    {
        return (bool) session()->get('user_id');
    }
}

if (! function_exists('has_role')) {
    function has_role(string ...$roles): bool
    {
        return in_array(session()->get('role'), $roles, true);
    }
}

if (! function_exists('unread_notifications')) {
    function unread_notifications(): int
    {
        static $n = null;
        if ($n === null) {
            $n = logged_in() ? model(NotificationModel::class)->unreadCount((int) session()->get('user_id')) : 0;
        }

        return $n;
    }
}

if (! function_exists('role_label')) {
    function role_label(string $role): string
    {
        return ['penyetor' => 'Penyetor', 'pendaur' => 'Pendaur Ulang', 'admin' => 'Admin'][$role] ?? $role;
    }
}

if (! function_exists('cover_url')) {
    function cover_url(?string $file): string
    {
        return $file ? base_url('uploads/listings/' . $file) : base_url('assets/img/placeholder.svg');
    }
}

if (! function_exists('avatar_url')) {
    function avatar_url(?array $user): ?string
    {
        return ! empty($user['photo']) ? base_url('uploads/avatars/' . $user['photo']) : null;
    }
}

if (! function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $s     = mb_substr($parts[0] ?? '?', 0, 1);
        if (isset($parts[1])) {
            $s .= mb_substr($parts[1], 0, 1);
        }

        return mb_strtoupper($s);
    }
}

if (! function_exists('wa_link')) {
    /** Nomor 08xx / +62xx -> https://wa.me/628xx */
    function wa_link(?string $phone, string $text = ''): ?string
    {
        $n = preg_replace('/\D+/', '', (string) $phone);
        if ($n === '') {
            return null;
        }
        if (str_starts_with($n, '0')) {
            $n = '62' . substr($n, 1);
        } elseif (str_starts_with($n, '8')) {
            $n = '62' . $n;
        }

        return 'https://wa.me/' . $n . ($text !== '' ? '?text=' . rawurlencode($text) : '');
    }
}

if (! function_exists('stars')) {
    /** Rating bintang: "★★★★☆" */
    function stars(float $avg): string
    {
        $full = (int) round($avg);

        return str_repeat('★', $full) . str_repeat('☆', 5 - $full);
    }
}

if (! function_exists('kg')) {
    function kg($n): string
    {
        return rtrim(rtrim(number_format((float) $n, 1, ',', '.'), '0'), ',') . ' kg';
    }
}

if (! function_exists('field_error')) {
    /** Pesan error validasi untuk satu field (dari flash 'errors'). */
    function field_error(string $field): string
    {
        $errors = session('errors');
        if (is_array($errors) && isset($errors[$field])) {
            return '<p class="field-error" role="alert">' . esc($errors[$field]) . '</p>';
        }

        return '';
    }
}

if (! function_exists('err_class')) {
    function err_class(string $field): string
    {
        $errors = session('errors');

        return is_array($errors) && isset($errors[$field]) ? ' input-error' : '';
    }
}

if (! function_exists('nav_current')) {
    /** aria-current="page" jika URL saat ini diawali $path. */
    function nav_current(string $path, bool $exact = false): string
    {
        $uri = trim(service('request')->getUri()->getPath(), '/');
        $path = trim($path, '/');
        $hit = $exact ? $uri === $path : ($path === '' ? $uri === '' : str_starts_with($uri, $path));

        return $hit ? ' aria-current="page"' : '';
    }
}

if (! function_exists('verification_label')) {
    function verification_label(string $s): string
    {
        return ['none' => 'Belum diajukan', 'pending' => 'Menunggu verifikasi', 'approved' => 'Terverifikasi', 'rejected' => 'Ditolak'][$s] ?? $s;
    }
}
