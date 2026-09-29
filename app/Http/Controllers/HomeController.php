<?php

namespace App\Http\Controllers;

use App\Models\Destination;

class HomeController extends Controller
{
    public function index()
    {
        // Mengambil semua data destinasi beserta pengelolanya (tenant) dari MySQL
        $destinations = Destination::with('tenant')->get();
        return view('landing', compact('destinations'));
    }
}