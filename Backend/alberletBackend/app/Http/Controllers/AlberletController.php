<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlberletResource;
use App\Models\Alberlet;
use App\Models\Kep;
use App\Models\Megye;
use App\Models\Tulajdonos;
use App\Models\Varos;
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
            $query->whereHas('varos', function ($q) use ($request) {
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
            $alberletek = $query->get();  // Összes rekord lekérése
            return AlberletResource::collection($alberletek);
        }

        // Alapértelmezett (searchPage): marad a 12 rekord/oldal
        $alberletek = $query->paginate(12);
        return AlberletResource::collection($alberletek);
    }

    /** Store a newly created resource in storage. */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'cim' => ['required', 'string', 'max:255'],
                'tipus' => 'required|integer|in:0,1,2',
                'ar' => 'required|integer|min:10000',
                'meret' => 'required|integer|min:5',
                'szobak_szama' => 'required|numeric|min:0.5',
                'emelet' => 'nullable|integer|min:0',
                'leiras' => 'required|string|min:20',
                'varos_id' => 'required',
                'nev' => 'required|string|min:3|max:100',
                'email' => 'required|email|max:150',
                'telefon' => 'required|string|min:10',
                'kepek' => 'required',
                'kepek.*' => 'image|mimes:jpeg,png,jpg|max:5120',
                'megye_id_vagy_nev' => 'nullable',
            ]);

            return DB::transaction(function () use ($request) {
                $varosId = $request->varos_id;

                // Város + Megye kezelése (új város/megye létrehozása ha kell)
                if (!is_numeric($varosId)) {
                    $megyeInfo = $request->megye_id_vagy_nev;

                    if (!is_numeric($megyeInfo)) {
                        $megye = Megye::firstOrCreate(['nev' => mb_convert_case($megyeInfo, MB_CASE_TITLE, 'UTF-8')]);
                        $megyeId = $megye->id;
                    } else {
                        $megyeId = $megyeInfo;
                    }

                    $ujVaros = Varos::firstOrCreate([
                        'nev' => mb_convert_case($varosId, MB_CASE_TITLE, 'UTF-8'),
                        'megye_id' => $megyeId
                    ]);
                    $varosId = $ujVaros->id;
                }

                // === JAVÍTOTT TULAJDONOS LOGIKA ===
                $tulajdonos = Tulajdonos::where('email', $request->email)->first();

                if ($tulajdonos) {
                    // Tisztítsuk meg a telefonszámokat az összehasonlításhoz (csak számok maradjanak)
                    $bekuldottTel = preg_replace('/[^0-9]/', '', $request->telefon);
                    $taroltTel = preg_replace('/[^0-9]/', '', $tulajdonos->telefon);

                    // Ha az e-mail létezik, de a név VAGY a telefon nem egyezik a tárolttal -> STOP
                    if ($tulajdonos->nev !== $request->nev || $taroltTel !== $bekuldottTel) {
                        return response()->json([
                            'error_message' => 'Hiba: Ez az e-mail cím már regisztrálva van, de a megadott név vagy telefonszám nem egyezik a rendszerben lévővel!'
                        ], 422);
                    }
                    // Ha idáig eljut a kód, az adatok egyeznek, mehet tovább a hirdetés mentése
                } else {
                    // Új e-mail esetén még megnézzük, hogy a telefon nem foglalt-e máshol
                    $telFoglalt = Tulajdonos::where('telefon', $request->telefon)->exists();
                    if ($telFoglalt) {
                        return response()->json([
                            'error_message' => 'Ez a telefonszám már egy másik e-mail címhez tartozik!'
                        ], 422);
                    }

                    // Ha minden tiszta, létrehozzuk az új tulajdonost
                    $tulajdonos = Tulajdonos::create([
                        'nev' => $request->nev,
                        'email' => $request->email,
                        'telefon' => $request->telefon,
                    ]);
                }

                // === CÍM SZÉPÍTÉSE (stabilabb verzió) ===
                $nyersCim = trim($request->input('cim'));
                $szavak = preg_split('/\s+/', $nyersCim);

                $formazottSzavak = array_map(function ($szo) {
                    $szo = trim($szo, '.,');
                    return is_numeric($szo) ? $szo : mb_convert_case($szo, MB_CASE_TITLE, 'UTF-8');
                }, $szavak);

                $formazottCim = implode(' ', $formazottSzavak);
                $formazottCim = preg_replace('/^(\d{4})\s+([^\s,]+)(\s|$)/u', '$1 $2, ', $formazottCim);
                $formazottCim = preg_replace('/\s*,\s*,+/', ',', $formazottCim);
                $formazottCim = preg_replace('/\s+/', ' ', $formazottCim);
                $formazottCim = trim($formazottCim, ' ,');

                if (!str_ends_with($formazottCim, '.')) {
                    $formazottCim .= '.';
                }

                // Albérlet létrehozása
                $alberlet = Alberlet::create([
                    'cim' => $formazottCim,
                    'tipus' => $request->tipus,
                    'ar' => $request->ar,
                    'meret' => $request->meret,
                    'szobak_szama' => ($request->tipus == 2) ? 1 : $request->szobak_szama,
                    'emelet' => $request->emelet ?? 0,
                    'lift' => $request->boolean('lift') ? 1 : 0,
                    'butorozott' => $request->boolean('butorozott') ? 1 : 0,
                    'leiras' => strip_tags($request->leiras),
                    'hirdetes_datuma' => now(),
                    'aktiv' => 0,
                    'varos_id' => $varosId,
                    'tulajdonos_id' => $tulajdonos->id,
                ]);

                // Képek mentése
                if ($request->hasFile('kepek')) {
                    foreach ($request->file('kepek') as $kepFile) {
                        $path = $kepFile->store('Kepek', 'public');
                        Kep::create([
                            'alberlet_id' => $alberlet->id,
                            'kep_url' => '/storage/' . $path,
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
                'error_line' => $e->getLine()
            ], 422);  // 422 jobb validációs hibákhoz
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
        // Validáció hozzáadása
        $request->validate([
            'leiras' => 'required|min:20',
            // Itt a többit is validálhatod, pl:
            'ar' => 'required|numeric|min:0',
        ], [
            'leiras.required' => 'A leírás kitöltése kötelező!',
            'leiras.min' => 'A leírásnak legalább :min karakternek kell lennie!',
        ]);

        try {
            $alberlet = Alberlet::findOrFail($id);

            return DB::transaction(function () use ($request, $alberlet) {
                // --- 1. TULAJDONOS ADATAINAK SZIGORÚ ELLENŐRZÉSE ---
                if ($request->has('tulajdonos_neve')) {
                    $ujNev = $request->input('tulajdonos_neve');
                    $ujTel = $request->input('tulajdonos_tel');
                    $ujEmail = $request->input('tulajdonos_email');
                    $jelenlegiTulaj = $alberlet->tulajdonos;

                    // Megnézzük, létezik-e MÁR a rendszerben ez az e-mail cím (más hirdetőnél)
                    $letezoTulajMasikEmaillel = \App\Models\Tulajdonos::where('email', $ujEmail)
                        ->where('id', '!=', $jelenlegiTulaj->id)
                        ->first();

                    if ($letezoTulajMasikEmaillel) {
                        // Ha az e-mail létezik, a névnek és telefonnak is stimmelnie kell
                        if ($letezoTulajMasikEmaillel->telefon !== $ujTel) {
                            throw new \Exception("Ehhez a felhasználóhoz ({$ujEmail}) nem ez a telefonszám tartozik az adatbázisban!");
                        }
                        if ($letezoTulajMasikEmaillel->nev !== $ujNev) {
                            throw new \Exception('Ehhez az e-mail címhez más név tartozik az adatbázisban!');
                        }

                        // Ha minden stimmel, hozzárendeljük az albérletet a már létező tulajhoz
                        $alberlet->tulajdonos_id = $letezoTulajMasikEmaillel->id;
                        $alberlet->save();
                    } else {
                        // Ha az e-mail nem létezik másnál, ellenőrizzük, hogy a telefon foglalt-e
                        $telFoglalt = \App\Models\Tulajdonos::where('telefon', $ujTel)
                            ->where('id', '!=', $jelenlegiTulaj->id)
                            ->exists();

                        if ($telFoglalt) {
                            throw new \Exception('Ez a telefonszám már egy másik hirdetőhöz van rendelve!');
                        }

                        // Ha teljesen új adatok vagy a sajátjai, frissítjük a jelenlegi rekordot
                        $jelenlegiTulaj->update([
                            'nev' => $ujNev,
                            'telefon' => $ujTel,
                            'email' => $ujEmail
                        ]);
                    }
                }

                // --- 2. VÁROS ÉS MEGYE KEZELÉSE ---
                $varosId = $request->varos;
                if ($varosId && !is_numeric($varosId)) {
                    $megyeInfo = $request->megye;

                    $megyeId = is_numeric($megyeInfo)
                        ? $megyeInfo
                        : Megye::firstOrCreate(['nev' => mb_convert_case($megyeInfo, MB_CASE_TITLE, 'UTF-8')])->id;

                    $ujVaros = Varos::firstOrCreate([
                        'nev' => mb_convert_case($varosId, MB_CASE_TITLE, 'UTF-8'),
                        'megye_id' => $megyeId
                    ]);
                    $alberlet->varos_id = $ujVaros->id;
                } elseif (is_numeric($varosId)) {
                    $alberlet->varos_id = $varosId;
                }

                // --- 3. ALBERLET ADATOK FRISSÍTÉSE ---
                $adatok = $request->only(['cim', 'ar', 'meret', 'szobak_szama', 'emelet', 'leiras']);

                if ($request->has('aktiv')) {
                    $adatok['aktiv'] = filter_var($request->aktiv, FILTER_VALIDATE_BOOLEAN);
                }

                if ($request->has('lift')) {
                    $adatok['lift'] = ($request->lift === 'van' || $request->lift === true || $request->lift == 1) ? 1 : 0;
                }

                if ($request->has('butorozott')) {
                    $adatok['butorozott'] = ($request->butorozott === 'igen' || $request->butorozott === true || $request->butorozott == 1) ? 1 : 0;
                }

                if ($request->has('tipus')) {
                    $tipusMap = ['ház' => 0, 'lakás' => 1, 'szoba' => 2];
                    if (isset($tipusMap[$request->tipus])) {
                        $adatok['tipus'] = $tipusMap[$request->tipus];
                    }
                }

                $alberlet->update($adatok);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Sikeres módosítás!'
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        $alberlet = Alberlet::find($id);

        if (!$alberlet) {
            return response()->json(['message' => 'Hirdetés nem található'], 404);
        }

        foreach ($alberlet->kepek as $kep) {
            Storage::disk('public')->delete($kep->kep_url);
            $kep->delete();
        }

        $alberlet->delete();

        return response()->json(['message' => 'Sikeres törlés']);
    }
}
