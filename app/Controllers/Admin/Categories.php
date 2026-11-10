<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\ListingModel;

class Categories extends BaseController
{
    public function index(): string
    {
        $cats = model(CategoryModel::class)->orderBy('name')->findAll();
        foreach ($cats as &$c) {
            $c['listing_count'] = model(ListingModel::class)->where('category_id', $c['id'])->countAllResults();
        }

        return view('admin/categories/index', ['title' => 'Kategori', 'cats' => $cats]);
    }

    public function create(): string
    {
        return view('admin/categories/form', ['title' => 'Kategori baru', 'cat' => null]);
    }

    public function store()
    {
        $data = $this->input();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $m = model(CategoryModel::class);
        $m->insert($data + ['slug' => $m->makeSlug($data['name'])]);

        return redirect()->to('/admin/categories')->with('success', 'Kategori ditambahkan.');
    }

    public function edit(int $id): string
    {
        return view('admin/categories/form', ['title' => 'Ubah kategori', 'cat' => $this->find($id)]);
    }

    public function update(int $id)
    {
        $this->find($id);
        $data = $this->input();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $m = model(CategoryModel::class);
        $m->update($id, $data + ['slug' => $m->makeSlug($data['name'], $id)]);

        return redirect()->to('/admin/categories')->with('success', 'Kategori diperbarui.');
    }

    /** Hapus hanya jika belum dipakai listing; kalau sudah dipakai, nonaktifkan saja. */
    public function delete(int $id)
    {
        $cat = $this->find($id);
        $used = model(ListingModel::class)->where('category_id', $id)->countAllResults();
        if ($used > 0) {
            model(CategoryModel::class)->update($id, ['is_active' => 0]);

            return redirect()->to('/admin/categories')->with('success', "Kategori \"{$cat['name']}\" dipakai {$used} barang, jadi dinonaktifkan (tidak dihapus).");
        }
        model(CategoryModel::class)->delete($id);

        return redirect()->to('/admin/categories')->with('success', 'Kategori dihapus.');
    }

    public function toggle(int $id)
    {
        $cat = $this->find($id);
        model(CategoryModel::class)->update($id, ['is_active' => (int) $cat['is_active'] ? 0 : 1]);

        return redirect()->to('/admin/categories');
    }

    private function find(int $id): array
    {
        $c = model(CategoryModel::class)->find($id);
        if (! $c) {
            $this->notFound('Kategori tidak ditemukan.');
        }

        return $c;
    }

    private function input(): ?array
    {
        $post = $this->request->getPost();
        $post['co2_factor'] = $this->number($post['co2_factor'] ?? '');
        $v = service('validation');
        $v->setRules([
            'name'        => 'required|min_length[3]|max_length[80]',
            'description' => 'permit_empty|max_length[255]',
            'co2_factor'  => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        ], ['co2_factor' => ['decimal' => 'Faktor CO2 harus angka, contoh 1,5.']]);
        if (! $v->run($post)) {
            $this->validator = $v;

            return null;
        }

        return [
            'name'        => trim($post['name']),
            'description' => trim((string) ($post['description'] ?? '')),
            'co2_factor'  => (float) $post['co2_factor'],
            'is_active'   => empty($post['is_active']) ? 0 : 1,
        ];
    }
}
