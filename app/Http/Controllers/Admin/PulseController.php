<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PulseController extends Controller
{
    /**
     * Redirect admin users to Laravel Pulse.
     */
    public function index(Request $request)
    {
        return redirect()->away('https://pulse.laravel.com/');
    }
}
