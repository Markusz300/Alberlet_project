<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlberletResource;
use App\Models\Alberlet;
use App\Models\Kep;
use App\Models\Megye;   // HIÁNYZOTT!
use App\Models\Tulajdonos;
use App\Models\Varos;   // HIÁNYZOTT!
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlberletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
   $query = Alberlet::with(['kepek', 'varos.megye', 'tipusKapcsolat']);

   if ($request->query('admin_view') !== 'true') {
        $query->where('aktiv', 1);
    }


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

   if ($request->query('admin_view') === 'true') {
        $alberletek = $query->get(); // Összes rekord lekérése
        return AlberletResource::collection($alberletek);
    }

    // Alapértelmezett (searchPage): marad a 12 rekord/oldal
    $alberletek = $query->paginate(12);
    return AlberletResource::collection($alberletek);
}

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // 1. Validáció
            $request->validate([
                'cim'           => ['required', 'string', 'max:255'],
                'tipus'         => 'required|integer|in:0,1,2',
                'ar'            => 'required|integer|min:10000',
                'meret'         => 'required|integer|min:5',
                'szobak_szama'  => 'required|numeric|min:0.5',
                'emelet'        => 'nullable|integer|min:0',
                'leiras'        => 'required|string|min:20',
                'varos_id'      => 'required',
                'nev'           => 'required|string|min:3|max:100',
                'email'         => 'required|email|max:150',
                'telefon'       => 'required|string|min:10',
                'kepek'         => 'required',
                'kepek.*'       => 'image|mimes:jpeg,png,jpg|max:5120',
                'megye_id_vagy_nev' => 'nullable',
            ]);

            return DB::transaction(function () use ($request) {

                $varosId = $request->varos_id;

                // 2. Város és Megye kezelése (ha újat írt be)
                if (!is_numeric($varosId)) {
                    $megyeInfo = $request->megye_id_vagy_nev;

                    if (!is_numeric($megyeInfo)) {
                        $megye = Megye::firstOrCreate([
                            'nev' => mb_convert_case($megyeInfo, MB_CASE_TITLE, "UTF-8")
                        ]);
                        $megyeId = $megye->id;
                    } else {
                        $megyeId = $megyeInfo;
                    }

                    $ujVaros = Varos::firstOrCreate([
                        'nev'      => mb_convert_case($varosId, MB_CASE_TITLE, "UTF-8"),
                        'megye_id' => $megyeId
                    ]);

                    $varosId = $ujVaros->id;
                }

                // 3. Tulajdonos kezelése – javított logika
                $tulajdonos = Tulajdonos::where('email', $request->email)
                    ->orWhere('telefon', $request->telefon)
                    ->first();

                if ($tulajdonos) {
                    // Már létezik → frissítjük az adatokat (biztonságos)
                    $tulajdonos->update([
                        'nev'     => $request->nev,
                        'email'   => $request->email,
                        'telefon' => $request->telefon,
                    ]);
                } else {
                    // Teljesen új tulajdonos
                    $tulajdonos = Tulajdonos::create([
                        'nev'     => $request->nev,
                        'email'   => $request->email,
                        'telefon' => $request->telefon,
                    ]);
                }

                // 4. CÍM FORMÁZÁSA – sokkal stabilabb verzió
                $nyersCim = trim($request->input('cim'));

                // Szavakra bontás
                $szavak = preg_split('/\s+/', $nyersCim);

                // Minden szót nagy kezdőbetűsre (kivéve ha csak szám)
                $formazottSzavak = array_map(function ($szo) {
                    $szo = trim($szo, '.,');
                    if (is_numeric($szo) || empty($szo)) {
                        return $szo;
                    }
                    return mb_convert_case($szo, MB_CASE_TITLE, "UTF-8");
                }, $szavak);

                $formazottCim = implode(' ', $formazottSzavak);

                // Irányítószám + város után vessző beszúrása ha nincs
                $formazottCim = preg_replace('/^(\d{4})\s+([^\s,]+)(\s|$)/u', '$1 $2, ', $formazottCim);

                // Tisztítás: dupla vessző, dupla szóköz eltávolítása
                $formazottCim = preg_replace('/\s*,\s*,+/', ',', $formazottCim);
                $formazottCim = preg_replace('/\s+/', ' ', $formazottCim);
                $formazottCim = trim($formazottCim, ' ,');

                // Pont a végére ha nincs
                if (!str_ends_with($formazottCim, '.')) {
                    $formazottCim .= '.';
                }

                // 5. Albérlet létrehozása
                $alberlet = Alberlet::create([
                    'cim'            => $formazottCim,
                    'tipus'          => $request->tipus,
                    'ar'             => $request->ar,
                    'meret'          => $request->meret,
                    'szobak_szama'   => ($request->tipus == 2) ? 1 : $request->szobak_szama,
                    'emelet'         => $request->emelet ?? 0,
                    'lift'           => filter_var($request->lift ?? 0, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                    'butorozott'     => filter_var($request->butorozott ?? 0, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                    'leiras'         => strip_tags($request->leiras),
                    'hirdetes_datuma'=> now(),
                    'aktiv'          => $request->aktiv ?? 0,
                    'varos_id'       => $varosId,
                    'tulajdonos_id'  => $tulajdonos->id,
                ]);

                // 6. Képek mentése
                if ($request->hasFile('kepek')) {
                    foreach ($request->file('kepek') as $kepFile) {
                        $path = $kepFile->store('Kepek', 'public');
                        Kep::create([
                            'alberlet_id' => $alberlet->id,
                            'kep_url'     => '/storage/' . $path,
                        ]);
                    }
                }

                return response()->json([
                    'message' => 'Hirdetés sikeresen létrehozva!',
                    'alberlet_id' => $alberlet->id
                ], 201);

            });

        } catch (\Exception $e) {
            return response()->json([
                'error_message' => $e->getMessage(),
                'error_line'    => $e->getLine()
            ], 500);
        }
    }

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
  public function update(Request $request, $id)
{
    try {
        $alberlet = Alberlet::findOrFail($id);
        
        // Összeszedjük a mezőket, amiket a Vue-ból küldtünk
        $adatok = $request->only([
            'cim', 'ar', 'meret', 'szobak_szama', 
            'emelet', 'lift', 'butorozott', 'leiras', 
            'tipus', 'varos', 'megye', 'aktiv'
        ]);

        // 1. TÍPUS FORDÍTÁSA (Szövegből számmá)
        if (isset($adatok['tipus'])) {
            $tipusMap = [
                'ház'   => 0,
                'lakás' => 1,
                'szoba' => 2
            ];
            // Ha a kapott érték benne van a táblázatunkban, átváltjuk a számra
            if (array_key_exists($adatok['tipus'], $tipusMap)) {
                $adatok['tipus'] = $tipusMap[$adatok['tipus']];
            }
        }

        // 2. LIFT FORDÍTÁSA
        if (isset($adatok['lift'])) {
            $adatok['lift'] = ($adatok['lift'] === 'van') ? 1 : 0;
        }

        // 3. BÚTOROZOTT FORDÍTÁSA
        if (isset($adatok['butorozott'])) {
            $adatok['butorozott'] = ($adatok['butorozott'] === 'igen') ? 1 : 0;
        }

        // 4. AKTÍV FORDÍTÁSA (Boolean kezelés)
        if (isset($adatok['aktiv'])) {
            $adatok['aktiv'] = filter_var($adatok['aktiv'], FILTER_VALIDATE_BOOLEAN);
        }

        // Mentés az adatbázisba
        $alberlet->update($adatok);

        return response()->json([
            'status' => 'success',
            'message' => 'Sikeresen módosítva!'
        ]);

    } catch (\Exception $e) {
        // Ha valami hiba van, a Quasar konzolban látni fogod
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
public function destroy($id)
{
    $alberlet = Alberlet::find($id);

    if (!$alberlet) {
        return response()->json(['message' => 'Hirdetés nem található'], 404);
    }

    // 1. Opcionális: Képek törlése a Storage-ból (hogy ne szemeteljünk)
    foreach ($alberlet->kepek as $kep) {
        Storage::disk('public')->delete($kep->kep_url);
        $kep->delete(); // A kép rekordot törölheted, az csak ehhez az albérlethez tartozott
    }

    // 2. Csak a hirdetést töröljük
    $alberlet->delete();

    return response()->json(['message' => 'Sikeres törlés']);
}
}

