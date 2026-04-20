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
                    <q-input v-model="form.cim" borderless dense class="text-weight-medium" @blur="form.cim = elsoBetuNagy(form.cim)" />
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
                  <q-item-section avatar><q-icon name="map" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Megye</q-item-label>
                    <q-select 
                      v-model="form.megye" 
                      :options="szurtMegyek" 
                      use-input 
                      hide-selected 
                      fill-input
                      new-value-mode="add-unique"
                      borderless 
                      dense
                      @filter="megyeSzures"
                      @update:model-value="form.varos = ''"
                    />
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="place" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Település</q-item-label>
                    <q-select 
                      v-model="form.varos" 
                      :options="szurtVarosok" 
                      use-input 
                      hide-selected
                      fill-input
                      new-value-mode="add-unique"
                      :disable="!form.megye"
                      borderless 
                      dense 
                    />
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

                <q-item :class="liftTiltva ? 'bg-grey-2' : ''">
                  <q-item-section avatar><q-icon name="elevator" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Lift {{ liftTiltva ? '(Nincs lift ház esetén)' : '' }}</q-item-label>
                    <q-select v-model="form.lift" :options="['van', 'nincs']" borderless dense :disable="liftTiltva" />
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
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'
import { useAlberletStore } from 'src/stores/alberletStore'

const store = useAlberletStore()
const route = useRoute()
const router = useRouter()
const $q = useQuasar()
const BASE_URL = 'http://127.0.0.1:8000'

const loading = ref(true)
const saving = ref(false)
const currentSlide = ref(0)
const form = ref(null)

const elsoBetuNagy = (s) => s ? s.split(' ').map(x => x.charAt(0).toUpperCase() + x.slice(1).toLowerCase()).join(' ') : s;

const formatImageUrl = (kep) => {
  const path = kep.kep_url || kep
  return path.startsWith('http') ? path : `${BASE_URL}${path.startsWith('/') ? path : '/' + path}`
}

const szurtMegyek = ref([])
const megyeSzures = (val, update) => {
  update(() => {
    const s = val.toLowerCase();
    szurtMegyek.value = val === '' 
      ? store.megyek.map(m => m.label) 
      : store.megyek.filter(m => m.label.toLowerCase().includes(s)).map(m => m.label);
  });
};

const szurtVarosok = computed(() => {
  if (!form.value?.megye) return [];
  const mObj = store.megyek.find(m => m.label === form.value.megye);
  if (!mObj) return [];
  return store.varosok.filter(v => v.megye_id === mObj.value).map(v => v.label);
});

const liftTiltva = computed(() => form.value?.tipus === 'ház' && form.value?.emelet <= 1);
watch(liftTiltva, (v) => { if (v && form.value) form.value.lift = 'nincs'; });

onMounted(async () => {
  try {
    const [, , res] = await Promise.all([
      store.fetchMegyek(false),
      store.fetchVarosok(false),
      api.get(`/alberletek/${route.params.id}`)
    ]);

    const item = res.data.data || res.data;
    if (!item.tulajdonos) item.tulajdonos = { nev: item.nev || '', telefon: item.telefon || '', email: item.email || '' };
    
    // Alaphelyzetbe hozzuk a gombokat a backend értékei alapján
    item.lift = (item.lift == 1 || item.lift === '1') ? 'van' : 'nincs';
    item.butorozott = (item.butorozott == 1 || item.butorozott === '1') ? 'igen' : 'nem';
    
    form.value = { ...item };
  } catch {
    $q.notify({ color: 'negative', message: 'Hiba a betöltéskor!' });
  } finally {
    loading.value = false;
  }
});

const handleUpdate = async () => {
  saving.value = true;
  try {
    // 1. Készítünk egy tiszta másolatot
    const payload = JSON.parse(JSON.stringify(form.value));

    // 2. KIFEJEZETTEN átadjuk a hirdető adatait a fő szintre (Laravelnek)
    payload.nev = form.value.tulajdonos.nev;
    payload.telefon = form.value.tulajdonos.telefon;
    payload.email = form.value.tulajdonos.email;

    // 3. Konvertáljuk a típusokat, mert a backend számokat vár
    payload.lift = payload.lift === 'van' ? 1 : 0;
    payload.butorozott = payload.butorozott === 'igen' ? 1 : 0;
    payload.ar = Number(payload.ar);
    payload.meret = Number(payload.meret);
    payload.emelet = Number(payload.emelet);
    
    delete payload.kepek;
    delete payload.tulajdonos; // Ezt levesszük, mert a fő szintre raktuk a mezőit

    await api.put(`/alberletek/${route.params.id}`, payload);
    
    $q.notify({ color: 'positive', message: 'Sikeresen mentve!', icon: 'check' });
    router.push('/admin');
  } catch (err) {
    console.error("Mentési hiba:", err.response?.data || err);
    $q.notify({ color: 'negative', message: 'Hiba a mentés során!' });
  } finally {
    saving.value = false;
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