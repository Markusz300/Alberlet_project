<?php

namespace Tests\Feature;

use App\Models\Alberlet;
use App\Models\Megye;
use App\Models\Tulajdonos;
use App\Models\Varos;
use Tests\TestCase;

class AlberletApiTest extends TestCase
{
    /**
     * 1. TESZT: Alap listázás és JSON struktúra
     */
    public function test_alberletek_index_json_struktura()
    {
        $response = $this->getJson('/api/alberletek');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'cim', 'ar', 'tipus', 'varos', 'kepek']
                ],
                'links',
                'meta'
            ]);
    }

    /**
     * 2. TESZT: Ár szűrés működése
     */
    public function test_ar_szures_mukodik()
    {
        $response = $this->getJson('/api/alberletek?min_ar=9999999');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    /**
     * 3. TESZT: Tulajdonos ellenőrző végpont (Check)
     */
    public function test_tulajdonos_ellenorzes_letezo_es_nem_letezo_emailre()
    {
        $response = $this->getJson('/api/users/check?email=kamu@email.hu');
        $response->assertStatus(200)->assertJson(['exists' => false]);

        $response2 = $this->getJson('/api/users/check?email=nem-email-formatum');
        $response2->assertStatus(200)->assertJson(['exists' => false]);
    }

    /**
     * 4. TESZT: Validáció - Túl rövid leírás elutasítása
     */
    public function test_store_elutasitja_a_tul_rovid_leirast()
    {
        $response = $this->postJson('/api/alberletek', [
            'cim' => 'Teszt utca 1',
            'ar' => 100000,
            'leiras' => 'Rövid',  // A szabályod min:20
            'email' => 'teszt@teszt.hu'
        ]);

        $response->assertStatus(422);
    }

    /**
     * 5. TESZT: Városok listája (filtered)
     */
    public function test_varosok_listazasa()
    {
        $response = $this->getJson('/api/varosok?filtered=true');

        $response->assertStatus(200);

        if (count($response->json()) > 0) {
            $response->assertJsonStructure([
                '*' => ['value', 'label', 'megye_id']
            ]);
        }
    }

    /**
     * 6. TESZT: Megye lista ellenőrzése
     */
    public function test_megyek_listazasa_mukodik()
    {
        $response = $this->getJson('/api/megyek');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            '*' => ['value', 'label']
        ]);
    }

    /**
     * 7. TESZT: Hirdetés törlése (Destroy)
     */
    public function test_alberlet_torlese_mukodik()
    {
        $alberlet = \App\Models\Alberlet::first();

        if ($alberlet) {
            $response = $this->deleteJson("/api/alberletek/{$alberlet->id}");

            $response->assertStatus(200);

            $this->assertDatabaseMissing('alberlet', ['id' => $alberlet->id]);
        }
    }
}
