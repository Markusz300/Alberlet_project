<script setup>
import { ref, onMounted } from 'vue'
import { api } from 'boot/axios' // ha boot/axios.js-ben beállítottad az Axios-t
import { useQuasar } from 'quasar'

// Reaktív adatok
const alberletek = ref([]) // lista
const loading = ref(true)
const totalPages = ref(1)
const currentPage = ref(1)
const filters = ref({
  varos_id: null,
  min_szoba: null,
  max_szoba: null,
  min_ar: null,
  max_ar: null,
  butorozott: null,
  // később bővíthető
})

// Quasar notifikáció (hibákhoz)
const $q = useQuasar()

// Lekérdezés függvény
const fetchAlberletek = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      // Szűrők hozzáadása, ha vannak értékek
      ...(filters.value.varos_id && { varos_id: filters.value.varos_id }),
      ...(filters.value.min_szoba && { min_szoba: filters.value.min_szoba }),
      ...(filters.value.max_szoba && { max_szoba: filters.value.max_szoba }),
      ...(filters.value.min_ar && { min_ar: filters.value.min_ar }),
      ...(filters.value.max_ar && { max_ar: filters.value.max_ar }),
      ...(filters.value.butorozott !== null && { butorozott: filters.value.butorozott }),
    }

    const response = await api.get('/alberletek', { params })

    alberletek.value = response.data.data // Laravel paginate data
    totalPages.value = response.data.meta.last_page
    currentPage.value = response.data.meta.current_page
  } catch (err) {
    console.error('Hiba a lista lekérdezésnél', err)
    $q.notify({
      type: 'negative',
      message: 'Hiba történt a hirdetések betöltésekor',
    })
  } finally {
    loading.value = false
  }
}

// Oldal betöltéskor hívjuk
onMounted(() => {
  fetchAlberletek()
})
</script>

<template>
  <q-page padding>
    <q-banner class="bg-primary text-white">
      <div class="text-h5">Kiadó albérletek keresése</div>
    </q-banner>

    <!-- Szűrő panel (később részletesen) -->
    <q-card flat bordered class="q-my-md">
      <q-card-section>
        <div class="text-subtitle1">Szűrők</div>
        <!-- Itt jönnek majd a QSelect, QRange stb. -->
      </q-card-section>
    </q-card>

    <!-- Lista – loading állapot -->
    <q-inner-loading :showing="loading">
      <q-spinner color="primary" size="3em" />
    </q-inner-loading>

    <!-- Albérletek kártyák vagy tábla -->
    <div v-if="!loading && alberletek.length === 0" class="text-center q-mt-xl">
      Nincs találat a szűrők alapján.
    </div>

    <div class="row q-col-gutter-md">
      <div v-for="alb in alberletek" :key="alb.id" class="col-12 col-sm-6 col-md-4">
        <q-card flat bordered class="cursor-pointer" @click="$router.push(`/alberlet/${alb.id}`)">
          <!-- Első kép preview -->
          <q-img
            v-if="alb.kepek && alb.kepek.length > 0"
            :src="alb.kepek[0]"
            ratio="4/3"
            spinner-color="primary"
          />
          <q-img v-else placeholder ratio="4/3" />

          <q-card-section>
            <div class="text-h6">{{ alb.cim }}</div>
            <div class="text-subtitle2 text-positive">
              {{ alb.ar }} Ft/hó
            </div>
            <div class="text-body2">
              {{ alb.szobak_szama }} szoba • {{ alb.meret }} m² • {{ alb.varos }}
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Pagináció -->
    <q-pagination
      v-model="currentPage"
      :max="totalPages"
      :max-pages="6"
      boundary-numbers
      @update:model-value="fetchAlberletek"
      class="q-mt-lg"
    />
  </q-page>
</template>
