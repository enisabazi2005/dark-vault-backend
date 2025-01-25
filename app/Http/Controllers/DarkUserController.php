<?php

namespace App\Http\Controllers;

use App\Http\Resources\DarkUserResource;
use App\Models\DarkUsers;
use Illuminate\Http\Request;

class DarkUserController extends Controller
{
    public function index() {
        $darkUsers = DarkUsers::all();

        return DarkUserResource::collection($darkUsers);
    }
}
