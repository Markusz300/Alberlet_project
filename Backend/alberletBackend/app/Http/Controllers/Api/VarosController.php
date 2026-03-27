<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Varos;
use Illuminate\Http\Request;

class VarosController extends Controller
{
     public function index() {
        // Csak azokat a városokat adjuk vissza, ahol van aktív albérlet
        return Varos::has('alberletek')
            ->select('id as value', 'nev as label')
            ->orderBy('nev', 'asc')
            ->get();
    }
}
