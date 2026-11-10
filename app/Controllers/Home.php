<?php

namespace App\Controllers;

use App\Libraries\ImpactCalculator;
use App\Models\CategoryModel;
use App\Models\ListingModel;

class Home extends BaseController
{
    public function index(): string
    {
        $listings = model(ListingModel::class);

        return view('home/index', [
            'title'      => 'LoakIN - Loakin barang, bikin berguna lagi',
            'latest'     => $listings->search(['sort' => 'terbaru'])->findAll(8),
            'categories' => model(CategoryModel::class)->withOpenCount(),
            'impact'     => (new ImpactCalculator())->summary(),
            'openCount'  => $listings->where('status', ListingModel::ST_OPEN)->countAllResults(),
        ]);
    }

    public function about(): string
    {
        return view('home/about', ['title' => 'Cara kerja LoakIN']);
    }
}
