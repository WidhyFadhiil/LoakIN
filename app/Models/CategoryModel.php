<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table         = 'categories';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'slug', 'description', 'co2_factor', 'is_active'];

    public function active(): array
    {
        return $this->where('is_active', 1)->orderBy('name')->findAll();
    }

    /** Kategori aktif beserta jumlah listing yang masih terbuka. */
    public function withOpenCount(): array
    {
        return $this->db->table('categories c')
            ->select('c.*, (SELECT COUNT(*) FROM listings l WHERE l.category_id = c.id AND l.status = \'tersedia\') AS open_count', false)
            ->where('c.is_active', 1)
            ->orderBy('c.name')
            ->get()->getResultArray();
    }

    public function makeSlug(string $name, ?int $ignoreId = null): string
    {
        $base = url_title($name, '-', true) ?: 'kategori';
        $slug = $base;
        $i    = 2;
        while (true) {
            $q = $this->where('slug', $slug);
            if ($ignoreId) {
                $q->where('id !=', $ignoreId);
            }
            if (! $q->first()) {
                return $slug;
            }
            $slug = $base . '-' . $i++;
        }
    }
}
