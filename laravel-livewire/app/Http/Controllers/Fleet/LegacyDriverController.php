<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LegacyDriverController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('fleet.drivers.index');
    }

    public function create(): View
    {
        return view('pages.fleet.drivers.create');
    }

    public function edit(Driver $driver): View
    {
        return view('pages.fleet.drivers.edit', ['driver' => $driver]);
    }
}
