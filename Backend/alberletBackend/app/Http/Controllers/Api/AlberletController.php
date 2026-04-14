<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlberletResource;
use App\Models\Alberlet;
use App\Models\Kep;
use App\Models\Tulajdonos;
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

                     
    // --- ÚJ: Megye szűrő ---
    if ($request->filled('megye_id')) {
        $query->whereHas('varos', function($q) use ($request) {
            $q->where('megye_id', $request->megye_id);
        });
    }

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
    // 1. Validáció – minden mező ellenőrzése
    $request->validate([
        'cim'             => 'required|string|max:255',
        'tipus'           => 'required|integer|in:0,1,2', // 0=szoba, 1=lakás, 2=ház
        'ar'              => 'required|integer|min:50000',
        'meret'           => 'nullable|integer|min:10',
        'szobak_szama'    => 'required|numeric|min:0.5|max:10',
        'emelet'          => 'nullable|integer',
        'lift'            => 'nullable|boolean',
        'butorozott'      => 'nullable|boolean',
        'leiras'          => 'nullable|string',
        'varos_id'        => 'required|exists:varos,id',
        // Tulajdonos adatok (ha nincs auth)
        'nev'             => 'required|string|max:100',
        'email'           => 'required|email|max:150',
        'telefon'         => 'nullable|string|max:30',
        // Képek: max 10 db, image fájlok, max 5MB/db
        'kepek.*'         => 'image|mimes:jpeg,png,jpg,gif|max:5120',
    ]);

    // 2. Tulajdonos kezelése (firstOrCreate, hogy ne legyen duplikált)
    $tulajdonos = Tulajdonos::firstOrCreate(
        ['email' => $request->email],
        [
            'nev'     => $request->nev,
            'telefon' => $request->telefon,
        ]
    );

    // 3. Új albérlet létrehozása
    $alberlet = Alberlet::create([
        'cim'             => $request->cim,
        'tipus'           => $request->tipus,
        'ar'              => $request->ar,
        'meret'           => $request->meret,
        'szobak_szama'    => $request->szobak_szama,
        'emelet'          => $request->emelet,
        'lift'            => $request->lift ?? 0,
        'butorozott'      => $request->butorozott ?? 0,
        'leiras'          => $request->leiras,
        'hirdetes_datuma' => now(), // vagy $request->datum ha van
        'aktiv'           => 1,     // alapból aktív
        'varos_id'        => $request->varos_id,
        'tulajdonos_id'   => $tulajdonos->id,
    ]);

    // 4. Képek feltöltése és mentése
    if ($request->hasFile('kepek')) {
        foreach ($request->file('kepek') as $kepFile) {
            // Mentés a public disk-re (storage/app/public/Kepek)
            $path = $kepFile->store('Kepek', 'public');

            // Új Kep rekord
            Kep::create([
                'alberlet_id' => $alberlet->id,
                'kep_url'     => '/storage/' . $path, // /storage/Kepek/randomnev.png
            ]);
        }
    }

    // 5. Visszaadás – az új albérlet Resource-ként
    return new AlberletResource($alberlet->load(['kepek', 'varos', 'tulajdonos', 'tipusKapcsolat']));
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
