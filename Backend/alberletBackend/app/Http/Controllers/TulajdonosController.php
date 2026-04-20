<?php

namespace App\Http\Controllers;

use App\Models\Tulajdonos;
use Illuminate\Http\Request;

class TulajdonosController extends Controller
{
    /**
     * Email alapján ellenőrzi a tulajdonost + visszatér a meglévő adatokkal
     */
    public function check(Request $request)
    {
        $email = $request->query('email');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['exists' => false]);
        }

        $tulajdonos = Tulajdonos::where('email', $email)->first();

        if ($tulajdonos) {
            return response()->json([
                'exists' => true,
                'user' => [
                    'nev'     => $tulajdonos->nev,
                    'telefon' => $tulajdonos->telefon,
                ]
            ]);
        }

        return response()->json(['exists' => false]);
    }
}