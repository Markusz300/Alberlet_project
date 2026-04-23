<template>
  <q-page padding class="bg-grey-1">
    <div class="max-width-container q-mx-auto">

      <div class="row items-center justify-between q-mb-md">
        <div class="row items-center q-gutter-sm">
          <q-btn flat round color="teal" icon="arrow_back" @click="router.back()" class="shadow-1 bg-white" />
          <div class="text-h4 text-weight-bold text-grey-9">Hirdetés szerkesztése</div>
        </div>
        <q-btn color="positive" icon="save" label="Minden módosítás mentése" size="lg" @click="handleUpdate"
          :loading="saving" />
      </div>

      <div v-if="loading" class="text-center q-pa-xl">
        <q-spinner-cube color="teal" size="4em" />
        <div class="text-h6 q-mt-md text-teal">Adatok betöltése...</div>
      </div>

      <div v-else-if="form" class="row q-col-gutter-lg">

        <div class="col-12 col-md-8">
          <q-card flat bordered class="rounded-borders overflow-hidden q-mb-lg shadow-2">
            <q-carousel v-model="currentSlide" v-if="form.kepek?.length" animated infinite arrows navigation thumbnails
              height="500px" class="bg-black">
              <q-carousel-slide v-for="(kep, index) in form.kepek" :key="index" :name="index"
                :img-src="formatImageUrl(kep)" />
            </q-carousel>
          </q-card>

          <q-card flat bordered class="rounded-borders shadow-2 bg-white q-mb-lg">
            <q-card-section>
              <div class="text-h5 text-weight-bold q-mb-md text-teal-10">
                <q-icon name="edit_note" color="teal" class="q-mr-sm" />Ingatlan leírása
              </div>
              <q-separator class="q-mb-md" />
              <q-input v-model="form.leiras" type="textarea" filled autogrow label="Leírás" class="text-body1" :rules="[
                val => !!val || 'A leírás nem maradhat üresen',
                val => val.length >= 20 || 'Legalább 20 karakter hosszan fejtsd ki az ingatlan leírását'
              ]" />
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
    <div class="text-h3 text-weight-bolder q-my-sm">
      {{ formatPrice(form.ar) }} <span class="text-h5">Ft</span>
    </div>
    <q-input
      v-model.number="form.ar"
      type="number"
      dark
      borderless
      input-class="text-center"
      dense
      placeholder="Összeg beírása..."
      :rules="[val => val >= 10000 || 'Minimum 10 000 Ft']"
    >
      <template v-slot:append>
        <q-icon name="edit" size="xs" color="white" class="opacity-50" />
      </template>
    </q-input>
  </q-card-section>
</q-card>

            <q-card flat bordered class="shadow-2 rounded-borders bg-white">
              <q-list separator>
                <q-item>
                  <q-item-section avatar><q-icon name="title" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Hirdetés címe</q-item-label>
                    <q-input v-model="form.cim" :rules="[val => !!val && val.trim().length > 5 || 'Kérlek adj meg egy érvényes címet (min. 5 karakter)!']" borderless dense class="text-weight-medium"
                      placeholder="9400 Sopron, Fő utca 1." hint="Példa: 9400 Sopron, Lackner Kristóf utca 1."
                      @blur="form.cim = formazottCim(form.cim)" />
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
                    <q-select v-model="form.megye" :options="szurtMegyek" use-input fill-input hide-selected
                      new-value-mode="add-unique" @new-value="(val, done) => { onNewValue(val, 'megye'); done(); }"
                      borderless dense @filter="megyeSzures" @update:model-value="form.varos = ''" />
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="place" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Település</q-item-label>
                    <q-select v-model="form.varos" :options="szurtVarosok" use-input fill-input hide-selected
                      new-value-mode="add-unique" @new-value="(val, done) => { onNewValue(val, 'varos'); done(); }"
                      :disable="!form.megye" borderless dense />
                  </q-item-section>
                </q-item>

                <q-item :class="szobaszamTiltva ? 'bg-grey-2' : ''">
                  <q-item-section avatar><q-icon name="bed" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Szobák száma {{ szobaszamTiltva ? '(Szoba esetén fix 1)' : ''
                      }}</q-item-label>
                    <q-input
  v-model.number="form.szobak_szama"
  type="number"
  borderless
  dense
  :disable="szobaszamTiltva"
  :rules="[val => val > 0 || 'Legalább 1 szoba kell']"
/>
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="straighten" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Alapterület (m²)</q-item-label>
                    <q-input
  v-model.number="form.meret"
  type="number"
  borderless
  dense
  :rules="[val => val > 0 || 'Az alapterület nem lehet 0 vagy negatív']"
/>
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="layers" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Emelet</q-item-label>
                   <q-input
  v-model.number="form.emelet"
  type="number"
  borderless
  dense
  :rules="[val => val >= 0 || 'Az emelet nem lehet negatív']"
/>
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

// --- KÉP FORMÁZÁS ---
const formatImageUrl = (kep) => {
  const path = kep.kep_url || kep
  return path.startsWith('http') ? path : `${BASE_URL}${path.startsWith('/') ? path : '/' + path}`
}

// --- MEGYE / VÁROS LOGIKA ---
const szurtMegyek = ref([])

const megyeSzures = (val, update) => {
  update(() => {
    // Ha üres a store, próbáljuk meg feltölteni (biztonsági játék)
    if (store.megyek.length === 0) {
        store.fetchMegyek();
    }
    const s = val.toLowerCase();
    szurtMegyek.value = val === ''
      ? store.megyek.map(m => m.label)
      : store.megyek.filter(m => m.label.toLowerCase().includes(s)).map(m => m.label);
  });
};

const szurtVarosok = computed(() => {
  if (!form.value?.megye || store.megyek.length === 0) return [];

  // Megkeressük a megye objektumot a label alapján
  const mObj = store.megyek.find(m => m.label === form.value.megye);
  if (!mObj) return [];

  // A store-ban az azonosító lehet .value vagy .id, ellenőrizzük mindkettőt
  const megyeId = mObj.value || mObj.id;

  return store.varosok
    .filter(v => v.megye_id === megyeId)
    .map(v => v.label);
});

// Szöveg szépítése
const szepitSzoveg = (szoveg) => {
  if (!szoveg) return szoveg;
  return szoveg.split(' ').map(szo => szo.charAt(0).toUpperCase() + szo.slice(1).toLowerCase()).join(' ');
};

const onNewValue = (val, modelRef) => {
  const formalt = szepitSzoveg(val);
  if (form.value) {
    form.value[modelRef] = formalt;
  }
};

// --- TILTÁSOK ÉS VALIDÁLÁS ---
const liftTiltva = computed(() => form.value?.tipus === 'ház' && form.value?.emelet <= 1);
const szobaszamTiltva = computed(() => form.value?.tipus === 'szoba');

watch(liftTiltva, (v) => { if (v && form.value) form.value.lift = 'nincs'; });
watch(() => form.value?.tipus, (uj) => { if (uj === 'szoba' && form.value) form.value.szobak_szama = "1"; });

// --- ADATBETÖLTÉS ---
onMounted(async () => {
  try {
    // KRITIKUS: Előbb töltsük be a megyéket/városokat, különben a computed hibázik!
    await Promise.all([
        store.fetchMegyek(),
        store.fetchVarosok()
    ]);

    const res = await api.get(`/alberletek/${route.params.id}`);
    const item = res.data.data;

    if (!item.tulajdonos) {
      item.tulajdonos = {
        nev: item.tulajdonos_neve || '',
        telefon: item.tulajdonos_tel || '',
        email: item.tulajdonos_email || ''
      };
    }
    form.value = item;

    // Kezdeti megye lista beállítása
    szurtMegyek.value = store.megyek.map(m => m.label);

  } catch (err) {
    console.error("Betöltési hiba:", err);
  } finally {
    loading.value = false;
  }
});


// Szám formázása ezres csoportosítással (pl. 150 000)
const formatPrice = (val) => {
  if (!val) return '0';
  return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
};

// --- CÍM FORMÁZÓ ---
const formazottCim = (val) => {
  if (!val) return val;
  let s = val.trim().replace(/\s+/g, ' ').replace(/[.,]+$/, '');
  let szavak = s.split(' ').map(szo => {
    if (/^\d/.test(szo)) return szo;
    return szo.charAt(0).toUpperCase() + szo.slice(1).toLowerCase();
  });
  let kesz = szavak.join(' ');
  kesz = kesz.replace(/^(\d{4})\s+([A-ZÁÉÍÓÖŐÚÜŰ][a-záéíóöőúüű]+)\s*,?\s*/, '$1 $2, ');
  return kesz.trim() + '.';
};

// --- MENTÉS ---
const handleUpdate = async () => {
  if (!form.value) return;


  // ÚJ: Cím validáció
  if (!form.value.cim || form.value.cim.trim().length < 5) {
    $q.notify({
      color: 'negative',
      message: 'A hirdetés címe nem maradhat üresen!',
      icon: 'warning'
    });
    return;
  }


  if (!form.value.ar || form.value.ar < 10000) {
  $q.notify({
    color: 'negative',
    message: 'A bérleti díj nem lehet kevesebb 10 000 Ft-nál!',
    icon: 'warning'
  });
  return;
}

// ÚJ: Numerikus validációk
  if (form.value.ar <= 0 || form.value.meret <= 0 || form.value.szobak_szama <= 0) {
    $q.notify({ color: 'negative', message: 'Az ár, méret és szobaszám csak pozitív szám lehet!', icon: 'warning' });
    return;
  }

  if (form.value.emelet < 0) {
    $q.notify({ color: 'negative', message: 'Az emelet nem lehet negatív!', icon: 'warning' });
    return;
  }


  if (!form.value.leiras || form.value.leiras.length < 20) {
    $q.notify({ color: 'negative', message: 'A leírásnak legalább 20 karakternek kell lennie!', icon: 'warning' });
    return;
  }

  saving.value = true;
  try {
    const payload = {
      cim: form.value.cim,
      ar: Number(form.value.ar),
      meret: Number(form.value.meret),
      szobak_szama: form.value.szobak_szama,
      emelet: Number(form.value.emelet),
      leiras: form.value.leiras,
      tipus: form.value.tipus,
      aktiv: form.value.aktiv,
      varos: form.value.varos,
      megye: form.value.megye,
      lift: form.value.lift,
      butorozott: form.value.butorozott,
      tulajdonos_neve: form.value.tulajdonos?.nev,
      tulajdonos_tel: form.value.tulajdonos?.telefon,
      tulajdonos_email: form.value.tulajdonos?.email
    };

    await api.put(`/alberletek/${route.params.id}`, payload);
    $q.notify({ color: 'positive', message: 'Minden módosítás sikeresen elmentve!', icon: 'check' });
    router.push('/admin');
  } catch (err) {
    const errorMsg = err.response?.data?.error || 'Hiba történt a mentés során!';
    $q.notify({ color: 'negative', message: errorMsg, icon: 'report_problem', position: 'top' });
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.max-width-container {
  max-width: 1200px;
}

.sticky-column {
  position: sticky;
  top: 24px;
}

.rounded-borders {
  border-radius: 16px;
}

.shadow-2 {
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
}

.shadow-3 {
  box-shadow: 0 8px 25px rgba(0, 128, 128, 0.2) !important;
}
</style>
