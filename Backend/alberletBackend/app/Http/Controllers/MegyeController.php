<?php

namespace App\Http\Controllers;

use App\Models\Megye;
use Illuminate\Http\Request;

class MegyeController extends Controller
{
    public function index() {
    return Megye::select('id as value', 'nev as label')
        ->orderBy('nev', 'asc')
        ->get();
}
}
