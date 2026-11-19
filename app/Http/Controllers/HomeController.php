<?php

namespace App\Http\Controllers;

use App\Models\Destination;

class HomeController extends Controller
{
    public function index(?string $destination = null)
    {
        // Jika URL memiliki slug destinasi, tampilkan katalog tiket.
        if ($destination !== null) {
            $selectedDestination = Destination::query()
                ->where('slug', $destination)
                ->whereHas('tenant', function ($query) {
                    $query->where('status', 'active');
                })
                ->firstOrFail();

            return view('customer.home', [
                'destination' => $selectedDestination->slug,
            ]);
        }

        // Jika membuka halaman utama, tampilkan landing page.
        $destinations = Destination::with('tenant')->get();

        return view('landing', compact('destinations'));
    }
}