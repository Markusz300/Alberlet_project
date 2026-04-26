<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Varos;
use Illuminate\Http\Request;

class VarosController extends Controller
{
    public function index(Request $request)
    {
        $query = Varos::select('id as value', 'nev as label', 'megye_id');

        if ($request->query('filtered') === 'true') {
            $query->whereHas('alberletek', function ($q) {
                $q->where('aktiv', 1);
            });
        }

        return $query->orderBy('nev', 'asc')->get();
    }
}
