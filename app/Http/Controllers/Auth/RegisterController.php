<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Mostrar formulário de registro
     */
    public function create(): View
    {
        Log::info('Acessando tela de cadastro');

        $communities = Community::where('id', '!=', 1)->get();

        return view('auth.register', compact('communities'));
    }
}