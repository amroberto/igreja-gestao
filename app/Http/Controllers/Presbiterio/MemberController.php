<?php

namespace App\Http\Controllers\Presbiterio;

use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function index()
    {
        $community = Auth::user()->community_id;
        $members = Member::where('community_id', $community)->get();

        return view('presbiterio.members.index', [
            'members' => $members
        ]);
    }
}
