<template>
  <q-page padding class="bg-grey-1">
    <div class="max-width-container q-mx-auto">

      <div class="row items-center justify-between q-mb-md">
        <div class="row items-center q-gutter-sm">
          <q-btn flat round color="teal" icon="arrow_back" @click="router.back()" class="shadow-1 bg-white" />
          <div class="text-h4 text-weight-bold text-grey-9">Hirdetés szerkesztése</div>
        </div>
        <q-btn color="positive" icon="save" label="Minden módosítás mentése" size="lg" @click="handleUpdate" :loading="saving" />
      </div>

      <div v-if="loading" class="text-center q-pa-xl">
        <q-spinner-cube color="teal" size="4em" />
        <div class="text-h6 q-mt-md text-teal">Adatok betöltése...</div>
      </div>

      <div v-else-if="form" class="row q-col-gutter-lg">

        <div class="col-12 col-md-8">
          <q-card flat bordered class="rounded-borders overflow-hidden q-mb-lg shadow-2">
            <q-carousel v-model="currentSlide" v-if="form.kepek?.length" animated infinite arrows navigation thumbnails height="500px" class="bg-black">
              <q-carousel-slide v-for="(kep, index) in form.kepek" :key="index" :name="index" :img-src="formatImageUrl(kep)" />
            </q-carousel>
          </q-card>

          <q-card flat bordered class="rounded-borders shadow-2 bg-white q-mb-lg">
            <q-card-section>
              <div class="text-h5 text-weight-bold q-mb-md text-teal-10">
                <q-icon name="edit_note" color="teal" class="q-mr-sm" />Ingatlan leírása
              </div>
              <q-separator class="q-mb-md" />
              <q-input v-model="form.leiras" type="textarea" filled autogrow label="Leírás" class="text-body1" />
            </q-card-section>
          </q-card>

          <q-card flat bordered class="rounded-borders shadow-2 bg-white">
            <q-card-section>
              <div class="text-h5 text-weight-bold q-mb-md text-teal-10">
                <q-icon name="person" color="teal" class="q-mr-sm" />Hirdető adatai
              </div>
              <q-separator class="q-mb-md" />
              <div class="row q-col-gutter-md">
                <div class="col-12 col-sm-4">
                  <q-input v-model="form.tulajdonos.nev" label="Név" filled />
                </div>
                <div class="col-12 col-sm-4">
                  <q-input v-model="form.tulajdonos.telefon" label="Telefon" filled />
                </div>
                <div class="col-12 col-sm-4">
                  <q-input v-model="form.tulajdonos.email" label="E-mail" filled />
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-md-4">
          <div class="sticky-column q-gutter-y-lg">

            <q-card flat class="bg-teal text-white shadow-3 rounded-borders">
              <q-card-section class="text-center">
                <div class="text-h6 opacity-80 text-uppercase">Havi bérleti díj</div>
                <q-input v-model.number="form.ar" type="number" dark borderless input-class="text-h3 text-weight-bolder text-center" suffix="Ft" />
              </q-card-section>
            </q-card>

            <q-card flat bordered class="shadow-2 rounded-borders bg-white">
              <q-list separator>
                <q-item>
                  <q-item-section avatar><q-icon name="title" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Hirdetés címe</q-item-label>
                    <q-input v-model="form.cim" borderless dense class="text-weight-medium" />
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="home_work" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Ingatlan típusa</q-item-label>
                    <q-select v-model="form.tipus" :options="['lakás', 'ház', 'szoba']" borderless dense />
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="place" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Település</q-item-label>
                    <q-input v-model="form.varos" borderless dense />
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="map" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Megye</q-item-label>
                    <q-input v-model="form.megye" borderless dense />
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="bed" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Szobák száma</q-item-label>
                    <q-input v-model="form.szobak_szama" borderless dense />
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="straighten" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Alapterület (m²)</q-item-label>
                    <q-input v-model.number="form.meret" type="number" borderless dense />
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="layers" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Emelet</q-item-label>
                    <q-input v-model.number="form.emelet" type="number" borderless dense />
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="elevator" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Lift</q-item-label>
                    <q-select v-model="form.lift" :options="['van', 'nincs']" borderless dense />
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="chair" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Bútorozottság</q-item-label>
                    <q-select v-model="form.butorozott" :options="['igen', 'nem']" borderless dense />
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'

const route = useRoute()
const router = useRouter()
const $q = useQuasar()
const BASE_URL = 'http://127.0.0.1:8000'

const loading = ref(true)
const saving = ref(false)
const currentSlide = ref(0)
const form = ref(null)

const formatImageUrl = (kep) => {
  const path = kep.kep_url || kep
  return path.startsWith('http') ? path : `${BASE_URL}${path.startsWith('/') ? path : '/' + path}`
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/alberletek/${route.params.id}`)
    const item = data.data || data
    
    // Alapértelmezett tulajdonos objektum, ha az API-ból nem jönne meg
    if (!item.tulajdonos) {
      item.tulajdonos = { nev: '', telefon: '', email: '' }
    }
    
    form.value = { ...item }
  } catch (err) {
  console.error(err) // <--- Ezzel már használva van!
  $q.notify({ color: 'negative', message: 'Hiba az adatok betöltésekor!' })
} finally {
    loading.value = false
  }
})

const handleUpdate = async () => {
  saving.value = true
  try {
    const payload = { ...form.value }

    // Konverziók (maradnak)
    payload.szobak_szama = String(payload.szobak_szama)
    payload.ar = Number(payload.ar)
    payload.meret = Number(payload.meret)
    payload.emelet = Number(payload.emelet)
    payload.aktiv = payload.aktiv === true || payload.aktiv === 1

    // EZEKET TARTTSUK MEG, ne töröljük! 
    // Így elküldi a 'tipus', 'varos', 'megye' mezőket is.
    
    // Csak a képeket és a tulajdonos objektumot töröljük, 
    // ha a Laravel sima mezőket vár az alberletek táblába.
    delete payload.kepek 
    // delete payload.tulajdonos // Csak akkor vedd ki a kommentet, ha a tulajdonost nem itt mented

    await api.put(`/alberletek/${route.params.id}`, payload)
    
    $q.notify({ color: 'positive', message: 'Minden módosítás elmentve!', icon: 'check' })
    router.push('/admin')
  } catch (err) {
  console.error(err) // <--- Ezzel már használva van!
  $q.notify({ color: 'negative', message: 'Hiba az adatok betöltésekor!' })
} finally {
    saving.value = false
  }
}
</script>

<style scoped>
.max-width-container { max-width: 1200px; }
.sticky-column { position: sticky; top: 24px; }
.rounded-borders { border-radius: 16px; }
.shadow-2 { box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important; }
.shadow-3 { box-shadow: 0 8px 25px rgba(0, 128, 128, 0.2) !important; }
</style>