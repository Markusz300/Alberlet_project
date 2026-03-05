<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from 'boot/axios' // Axios beállítás a boot fájlból
import { useQuasar } from 'quasar'

// Reaktív adatok
const route = useRoute()
const alberlet = ref(null)
const loading = ref(true)
const error = ref(null)
const $q = useQuasar()

// ID lekérése az URL-ből (/alberlet/:id)
const id = route.params.id

// Lekérdezés függvény
const fetchAlberlet = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await api.get(`/alberletek/${id}`)
    alberlet.value = response.data // a show() metódus visszaadja az AlberletResource-t
  } catch (err) {
    console.error('Hiba a részletek lekérdezésnél', err)
    error.value = 'Nem sikerült betölteni az albérletet. Próbáld újra később.'
    $q.notify({
      type: 'negative',
      message: error.value,
    })
  } finally {
    loading.value = false
  }
}

// Oldal betöltéskor hívjuk
onMounted(() => {
  fetchAlberlet()
})
</script>

<template>
  <q-page padding>
    <!-- Vissza gomb -->
    <q-btn
      flat
      round
      dense
      icon="arrow_back"
      label="Vissza a listára"
      @click="$router.back()"
      class="q-mb-md"
    />

    <!-- Loading állapot -->
    <q-inner-loading :showing="loading">
      <q-spinner color="primary" size="3em" />
      <div class="q-mt-sm">Betöltés...</div>
    </q-inner-loading>

    <!-- Hiba üzenet -->
    <q-banner v-if="error" class="bg-negative text-white q-my-md">
      {{ error }}
    </q-banner>

    <!-- Részletek, ha betöltődött -->
    <div v-if="alberlet && !loading && !error">
      <q-banner class="bg-primary text-white q-mb-md">
        <div class="text-h5">{{ alberlet.cim }}</div>
        <div class="text-subtitle1">
          {{ alberlet.ar }} Ft/hó • {{ alberlet.szobak_szama }} szoba • {{ alberlet.meret }} m²
        </div>
      </q-banner>

      <!-- Képek carousel -->
      <q-carousel
        v-if="alberlet.kepek && alberlet.kepek.length > 0"
        animated
        infinite
        thumbnails
        transition-prev="slide-right"
        transition-next="slide-left"
        height="400px"
        class="q-mb-lg"
      >
        <q-carousel-slide v-for="(kepUrl, index) in alberlet.kepek" :key="index">
          <q-img
            :src="kepUrl"
            spinner-color="primary"
            fit="contain"
            style="height: 100%; width: 100%;"
          />
        </q-carousel-slide>
      </q-carousel>

      <q-banner v-else class="bg-grey-3 text-grey-8 q-mb-lg">
        Nincs kép ehhez az albérlethez.
      </q-banner>

      <!-- Részletes infók -->
      <q-card flat bordered class="q-mb-md">
        <q-card-section>
          <div class="text-h6">Részletek</div>
          <div class="q-mt-sm">
            <strong>Város:</strong> {{ alberlet.varos }}<br />
            <strong>Típus:</strong> {{ alberlet.tipus }}<br />
            <strong>Lift:</strong> {{ alberlet.lift }}<br />
            <strong>Bútorozott:</strong> {{ alberlet.butorozott }}<br />
            <strong>Emelet:</strong> {{ alberlet.emelet || 'Nem ismert' }}<br />
            <strong>Hirdetés dátuma:</strong> {{ alberlet.hirdetes_datuma }}<br />
          </div>
        </q-card-section>
      </q-card>

      <!-- Leírás -->
      <q-card flat bordered class="q-mb-md">
        <q-card-section>
          <div class="text-h6">Leírás</div>
          <div class="q-mt-sm text-body1">{{ alberlet.leiras }}</div>
        </q-card-section>
      </q-card>

      <!-- Tulajdonos infó (kontakt) -->
      <q-card flat bordered>
        <q-card-section>
          <div class="text-h6">Kapcsolatfelvétel</div>
          <div class="q-mt-sm">
            <strong>Név:</strong> {{ alberlet.tulajdonos?.nev || 'Nem ismert' }}<br />
            <strong>Email:</strong> {{ alberlet.tulajdonos?.email || 'Nem ismert' }}<br />
            <strong>Telefon:</strong> {{ alberlet.tulajdonos?.telefon || 'Nem ismert' }}<br />
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>
