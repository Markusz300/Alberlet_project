<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Varos;
use Illuminate\Http\Request;

class VarosController extends Controller
{
    public function index() {
    // Hozzáadjuk a megye_id-t a válogatáshoz
    return Varos::has('alberletek')
        ->select('id as value', 'nev as label', 'megye_id') // <-- Itt a megye_id!
        ->orderBy('nev', 'asc')
        ->get();
}
}
