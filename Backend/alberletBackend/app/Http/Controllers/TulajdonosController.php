<?php

namespace App\Http\Controllers;

use App\Models\Tulajdonos;
use Illuminate\Http\Request;

class TulajdonosController extends Controller
{
    /**
     * Email alapján ellenőrzi, létezik-e már tulajdonos
     */
    public function check(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            return response()->json(['exists' => false], 400);
        }

        $tulajdonos = Tulajdonos::where('email', $email)->first();

        if ($tulajdonos) {
            return response()->json([
                'exists' => true,
                'user' => [
                    'nev' => $tulajdonos->nev,
                    'telefon' => $tulajdonos->telefon,
                ]
            ]);
        }

        return response()->json(['exists' => false]);
    }
}