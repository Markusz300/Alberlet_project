<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlberletResource;
use App\Models\Alberlet;
use App\Models\Kep;
use App\Models\Tulajdonos;
use App\Models\Varos;   // HIÁNYZOTT!
use App\Models\Megye;   // HIÁNYZOTT!
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlberletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
   $query = Alberlet::with(['kepek', 'varos.megye', 'tipusKapcsolat'])
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
        try {
        // 1. Validáció
        $request->validate([
            'cim'             => 'required|string|max:255',
            'tipus'           => 'required|integer|in:0,1,2',
            'ar'              => 'required|integer|min:50000',
            'meret'           => 'nullable|integer|min:10',
            'szobak_szama'    => 'required|numeric|min:0.5|max:10',
            'emelet'          => 'nullable|integer',
            'lift'            => 'nullable',
            'butorozott'      => 'nullable',
            'leiras'          => 'nullable|string',
            'varos_id'        => 'required',
            'nev'             => 'required|string|max:100',
            'email'           => 'required|email|max:150',
            'telefon'         => 'nullable|string|max:30',
            'kepek.*'         => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        return DB::transaction(function () use ($request) {
            $varosId = $request->varos_id;

            // 2. Város és Megye kezelése
            if (!is_numeric($varosId)) {
                $megyeInfo = $request->megye_id_vagy_nev;

                if (!is_numeric($megyeInfo)) {
                    $megye = Megye::firstOrCreate(['nev' => $megyeInfo]);
                    $megyeId = $megye->id;
                } else {
                    $megyeId = $megyeInfo;
                }

                $ujVaros = Varos::firstOrCreate([
                    'nev' => $varosId,
                    'megye_id' => $megyeId
                ]);
                $varosId = $ujVaros->id;
            }

            // 3. Tulajdonos
            $tulajdonos = Tulajdonos::firstOrCreate(
                ['email' => $request->email],
                ['nev' => $request->nev, 'telefon' => $request->telefon]
            );

            // 4. Albérlet létrehozása
            // A lift és butorozott értékeket kényszerítjük 0-ra vagy 1-re
            $alberlet = Alberlet::create([
                'cim'             => $request->cim,
                'tipus'           => $request->tipus,
                'ar'              => $request->ar,
                'meret'           => $request->meret,
                'szobak_szama'    => $request->szobak_szama,
                'emelet'          => $request->emelet ?? 0,
                'lift'            => filter_var($request->lift, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'butorozott'      => filter_var($request->butorozott, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'leiras'          => $request->leiras,
                'hirdetes_datuma' => now()->format('Y-m-d'), // Biztonságos dátum formátum
                'aktiv'           => 1,
                'varos_id'        => $varosId,
                'tulajdonos_id'   => $tulajdonos->id,
            ]);

            // 5. Képek
            if ($request->hasFile('kepek')) {
                foreach ($request->file('kepek') as $kepFile) {
                    $path = $kepFile->store('Kepek', 'public');
                    Kep::create([
                        'alberlet_id' => $alberlet->id,
                        'kep_url'     => '/storage/' . $path,
                    ]);
                }
            }

            return response()->json($alberlet, 201);
        });

    }catch (\Exception $e) {
        // EZ FONTOS: Visszaküldi a konkrét hibaüzenetet a böngészőnek!
        return response()->json([
            'error_message' => $e->getMessage(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine()
        ], 500);
    }}


    /**
     * Display the specified resource.
     */
    public function show(Alberlet $alberlet)
{
    // Előre betöltjük a cuccokat
    $alberlet->load(['kepek', 'varos.megye', 'tulajdonos', 'tipusKapcsolat']);

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

