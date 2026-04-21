<?php

namespace App\Http\Controllers;

use App\Models\Megye;
use Illuminate\Http\Request;

class MegyeController extends Controller
{
    public function index(Request $request)
    {
        $query = Megye::select('id as value', 'nev as label');

        if ($request->query('filtered') === 'true') {
            $query->whereHas('varosok.alberletek', function ($q) {
                $q->where('aktiv', 1);
            });
        }

        return $query->orderBy('nev', 'asc')->get();
    }
}
