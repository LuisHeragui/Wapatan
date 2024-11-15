<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Wapatan', [
            'products' => Product::all(),
            'ingredients' => Ingredient::all(),
        ]);
    }
}
