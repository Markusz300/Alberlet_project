<script setup>
import { ref, onMounted } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const BASE_URL = 'http://127.0.0.1:8000'
const $q = useQuasar()

// Reaktív adatok
const alberletek = ref([])
const loading = ref(true)
const totalPages = ref(1)
const currentPage = ref(1)
const showFilters = ref(false) // Szűrőpanel lenyitása/becsukása

// Szűrő objektum (pontosan a Laravel által várt kulcsokkal)
const filters = ref({
  varos_id: null,
  min_szoba: null,
  max_szoba: null,
  min_ar: null,
  max_ar: null,
  min_meret: null,
  max_meret: null,
  butorozott: null,
  lift: null,
  tipus: null,
  sort: 'legujabb'
})

// Fix opciók a select-ekhez
const tipusOpciok = [
  { label: 'Összes típus', value: null },
  { label: 'Ház', value: 0 },    // 0 = Ház
  { label: 'Lakás', value: 1 },  // 1 = Lakás (ez maradt középen)
  { label: 'Szoba', value: 2 }   // 2 = Szoba
]

const igenNemOpciok = [
  { label: 'Mindegy', value: null },
  { label: 'Igen', value: 1 },
  { label: 'Nem', value: 0 }
]

const rendezesOpciok = [
  { label: 'Legújabb elöl', value: 'legujabb' },
  { label: 'Legolcsóbb elöl', value: 'ar_asc' },
  { label: 'Legdrágább elöl', value: 'ar_desc' }
]

// Kép URL segéd
const getImageUrl = (kepek) => {
  if (!kepek || !Array.isArray(kepek) || kepek.length === 0) return null
  const utvonal = kepek[0]
  const tisztaUtvonal = utvonal.startsWith('/') ? utvonal : `/${utvonal}`
  return `${BASE_URL}${tisztaUtvonal}`
}

// Lekérdezés
const fetchAlberletek = async (page = 1) => {
  loading.value = true
  try {
    // Csak azokat küldjük el, amik nem null értékűek
    const params = { page }
    Object.keys(filters.value).forEach(key => {
      if (filters.value[key] !== null && filters.value[key] !== '') {
        params[key] = filters.value[key]
      }
    })

    const response = await api.get('/alberletek', { params })
    alberletek.value = response.data.data
    totalPages.value = response.data.meta.last_page
    currentPage.value = response.data.meta.current_page
  } catch (err) {
    console.error('Hiba:', err)
    $q.notify({ type: 'negative', message: 'Hiba a betöltéskor!' })
  } finally {
    loading.value = false
  }
}

// Szűrők törlése
const resetFilters = () => {
  filters.value = {
    varos_id: null,
    min_szoba: null,
    max_szoba: null,
    min_ar: null,
    max_ar: null,
    min_meret: null,
    max_meret: null,
    butorozott: null,
    lift: null,
    tipus: null,
    sort: 'legujabb'
  }
  fetchAlberletek(1)
}

onMounted(() => fetchAlberletek())
</script>

<template>
  <q-page padding class="bg-grey-2">
    
    <q-banner class="bg-primary text-white q-mb-md shadow-2 rounded-borders">
      <div class="row items-center justify-between">
        <div class="text-h5">Kiadó albérletek</div>
        <q-btn 
          flat 
          icon="tune" 
          :label="showFilters ? 'Szűrők elrejtése' : 'Szűrés és Rendezés'" 
          @click="showFilters = !showFilters" 
        />
      </div>
    </q-banner>

    <q-expansion-item
      v-model="showFilters"
      class="bg-white shadow-1 rounded-borders q-mb-lg"
      header-class="hidden"
    >
      <q-card>
        <q-card-section class="row q-col-gutter-md">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="text-caption text-weight-bold">Ár (Ft/hó)</div>
            <div class="row q-col-gutter-xs">
              <q-input dense outlined v-model.number="filters.min_ar" placeholder="Min" class="col-6" type="number" />
              <q-input dense outlined v-model.number="filters.max_ar" placeholder="Max" class="col-6" type="number" />
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="text-caption text-weight-bold">Szobák száma</div>
            <div class="row q-col-gutter-xs">
              <q-input dense outlined v-model.number="filters.min_szoba" placeholder="Min" class="col-6" type="number" step="0.5" />
              <q-input dense outlined v-model.number="filters.max_szoba" placeholder="Max" class="col-6" type="number" step="0.5" />
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="text-caption text-weight-bold">Méret (m²)</div>
            <div class="row q-col-gutter-xs">
              <q-input dense outlined v-model.number="filters.min_meret" placeholder="Min" class="col-6" type="number" />
              <q-input dense outlined v-model.number="filters.max_meret" placeholder="Max" class="col-6" type="number" />
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="text-caption text-weight-bold">Ingatlan típusa</div>
            <q-select dense outlined v-model="filters.tipus" :options="tipusOpciok" emit-value map-options />
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-select dense outlined v-model="filters.butorozott" :options="igenNemOpciok" label="Bútorozott?" emit-value map-options />
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <q-select dense outlined v-model="filters.lift" :options="igenNemOpciok" label="Lift?" emit-value map-options />
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-select dense outlined v-model="filters.sort" :options="rendezesOpciok" label="Rendezés" emit-value map-options />
          </div>

          <div class="col-12 col-md-3 flex items-end q-gutter-sm">
            <q-btn color="primary" label="Keresés" icon="search" class="col" @click="fetchAlberletek(1)" />
            <q-btn color="grey-7" flat label="Alaphelyzet" icon="refresh" @click="resetFilters" />
          </div>
        </q-card-section>
      </q-card>
    </q-expansion-item>

    <div v-if="!loading && alberletek.length === 0" class="text-center q-mt-xl bg-white q-pa-xl rounded-borders shadow-1">
      <q-icon name="sentiment_dissatisfied" size="4rem" color="grey-5" />
      <div class="text-h6 text-grey-7">Sajnos nincs ilyen hirdetésünk.</div>
      <q-btn flat color="primary" label="Összes hirdetés mutatása" @click="resetFilters" class="q-mt-md" />
    </div>

    <div class="row q-col-gutter-lg">
      <div v-for="alb in alberletek" :key="alb.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
        <q-card flat bordered class="my-card cursor-pointer" @click="$router.push(`/alberlet/${alb.id}`)">
          <q-img :src="getImageUrl(alb.kepek) || 'https://placehold.co/400x300?text=Nincs+Kep'" :ratio="4/3">
            <div class="absolute-bottom-right bg-primary text-white q-pa-xs q-px-md text-weight-bold" style="border-top-left-radius: 8px;">
              {{ alb.ar?.toLocaleString() }} Ft
            </div>
          </q-img>

          <q-card-section>
            <div class="text-subtitle1 text-weight-bold ellipsis">{{ alb.cim }}</div>
            <div class="text-caption text-grey-7">
              <q-icon name="location_on" color="red" /> {{ alb.varos }}
            </div>
            <q-separator class="q-my-sm" />
            <div class="row justify-between text-grey-9 text-caption text-weight-medium">
              <span><q-icon name="bed" /> {{ alb.szobak_szama }} szob.</span>
              <span><q-icon name="straighten" /> {{ alb.meret }} m²</span>
              <span><q-icon name="apartment" /> {{ alb.emelet || 'F' }}. em</span>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-inner-loading :showing="loading">
      <q-spinner-dots color="primary" size="3em" />
    </q-inner-loading>

    <div class="flex flex-center q-mt-xl q-mb-xl">
      <q-pagination
        v-model="currentPage"
        :max="totalPages"
        :max-pages="6"
        boundary-numbers
        direction-links
        color="primary"
        @update:model-value="fetchAlberletek"
      />
    </div>
  </q-page>
</template>

<style scoped>
.my-card {
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}
.my-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 20px rgba(0,0,0,0.15) !important;
}
.rounded-borders {
  border-radius: 12px;
}
</style>