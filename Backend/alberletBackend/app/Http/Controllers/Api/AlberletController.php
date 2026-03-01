<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlberletResource;
use App\Models\Alberlet;
use Illuminate\Http\Request;

class AlberletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
    $query = Alberlet::with(['kepek', 'varos', 'tipusKapcsolat'])
                     ->where('aktiv', 1);

    // Szűrő: város (varos_id)
    if ($request->filled('varos_id')) {
        $query->where('varos_id', $request->varos_id);
    }

    // Szűrő: szobaszám min-max
    if ($request->filled('min_szoba')) {
        $query->where('szobak_szama', '>=', $request->min_szoba);
    }
    if ($request->filled('max_szoba')) {
        $query->where('szobak_szama', '<=', $request->max_szoba);
    }

    // Szűrő: ár min-max
    if ($request->filled('min_ar')) {
        $query->where('ar', '>=', $request->min_ar);
    }
    if ($request->filled('max_ar')) {
        $query->where('ar', '<=', $request->max_ar);
    }

    // Szűrő: méret (alapterület) min-max
    if ($request->filled('min_meret')) {
        $query->where('meret', '>=', $request->min_meret);
    }
    if ($request->filled('max_meret')) {
        $query->where('meret', '<=', $request->max_meret);
    }

    // Szűrő: bútorozott (0/1 vagy mindkettő)
    if ($request->filled('butorozott')) {
        $query->where('butorozott', $request->butorozott);
    }

    // Szűrő: lift (0/1 vagy mindkettő)
    if ($request->filled('lift')) {
        $query->where('lift', $request->lift);
    }

    // Szűrő: tipus (0=szoba, 1=lakás, 2=ház)
    if ($request->filled('tipus')) {
        $query->where('tipus', $request->tipus);
    }

    // Rendezés (alapból legújabb elöl, de parameter alapján módosítható)
    if ($request->has('sort')) {
        switch ($request->sort) {
            case 'legujabb':
                $query->orderBy('hirdetes_datuma', 'desc');
                break;
            case 'ar_asc':
                $query->orderBy('ar', 'asc');
                break;
            case 'ar_desc':
                $query->orderBy('ar', 'desc');
                break;
            
            default:
                $query->orderBy('hirdetes_datuma', 'desc');
        }
    } else {
        $query->orderBy('hirdetes_datuma', 'desc');
    }

    //  12 rekord/oldal
    $alberletek = $query->paginate(12);

    // Visszaadás Resource-ként
    return AlberletResource::collection($alberletek);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Alberlet $alberlet)
{
    // Előre betöltjük a cuccokat
    $alberlet->load(['kepek', 'varos', 'tulajdonos', 'tipusKapcsolat']);

    // Visszaadjuk Resource ba
    return new AlberletResource($alberlet);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Alberlet $alberlet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Alberlet $alberlet)
    {
        //
    }
}
