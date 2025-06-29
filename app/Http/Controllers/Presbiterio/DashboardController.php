<?php

namespace App\Http\Controllers\Presbiterio;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $community = Auth::user()->community;
        return view('presbiterio.dashboard', compact('community'));
    }
}
