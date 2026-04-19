<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Varos;
use Illuminate\Http\Request;

class VarosController extends Controller
{
    public function index(Request $request) 
{
    $query = Varos::select('id as value', 'nev as label', 'megye_id');

    // Ha szűrni kell (SearchPage)
    if ($request->query('filtered') === 'true') {
        $query->whereHas('alberletek', function($q) {
            $q->where('aktiv', 1);
        });
    } 
    // Ha nem kell szűrni, de alapból csak azokat akarod látni amiknek van hirdetése (opcionális)
    // De a CreatePage-re javaslom, hogy ne legyen semmilyen szűrés:
    // else { $query->has('alberletek'); } // Ezt vedd ki, ha minden várost látni akarsz feladáskor!

    return $query->orderBy('nev', 'asc')->get();
}
}
