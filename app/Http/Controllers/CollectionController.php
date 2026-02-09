<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::query()
            ->select('id', 'slug', 'name')
            ->where('is_active', true)
            ->with('media')
            ->get();
        
        return view('collections.index', compact('collections'));
    }
}
