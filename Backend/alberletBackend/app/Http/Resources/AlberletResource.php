<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlberletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cim' => $this->cim,
            'tipus' => $this->tipusKapcsolat?->nev ?? 'ismeretlen',
            'ar' => $this->ar,
            'meret' => $this->meret,
            'szobak_szama' => $this->szobak_szama,
            'emelet' => $this->emelet,
            'lift' => $this->lift ? 'van' : 'nincs',
            'butorozott' => $this->butorozott ? 'igen' : 'nem',
            'leiras' => $this->leiras,
            'hirdetes_datuma' => $this->hirdetes_datuma instanceof \DateTime ? $this->hirdetes_datuma->format('Y-m-d') : $this->hirdetes_datuma,
            'aktiv' => $this->aktiv,

            // VÁROS: Ha be van töltve a város kapcsolat
            'varos' => $this->whenLoaded('varos', fn() => $this->varos->nev),

            // MEGYE: A városon keresztül érjük el
            'megye' => $this->whenLoaded('varos', function () {
                return $this->varos->megye->nev ?? null;
            }),

            'tulajdonos' => $this->whenLoaded('tulajdonos', fn() => [
                'nev' => $this->tulajdonos->nev,
                'email' => $this->tulajdonos->email,
                'telefon' => $this->tulajdonos->telefon,
            ]),

            'kepek' => $this->whenLoaded('kepek', fn() => $this->kepek->pluck('kep_url')->toArray()),
        ];
    }
}
