<template>
  <q-page padding class="bg-grey-1 flex flex-center">
    <q-card flat bordered class="form-card shadow-2 q-mb-xl">
      <q-card-section class="bg-teal text-white">
        <div class="text-h5 text-weight-bold text-center">Új hirdetés feladása</div>
      </q-card-section>

      <q-form @submit="onSubmit" class="q-gutter-md q-pa-lg">
        <div class="row q-col-gutter-md">

          <div class="col-12 text-teal-10 text-weight-bold h6 border-bottom">1. Ingatlan helye</div>

          <div class="col-12 col-md-6">
            <q-select
              outlined
              v-model="selectedMegye"
              use-input
              input-debounce="0"
              label="Megye (választható vagy írható)"
              :options="filteredMegyekOptions"
              @filter="filterMegyek"
              @new-value="createValueMegye"
              dense
              color="teal"
              clearable
            />
          </div>

          <div class="col-12 col-md-6">
            <q-select
              outlined
              v-model="selectedVaros"
              use-input
              input-debounce="0"
              label="Város (választható vagy írható)"
              :options="filteredVarosok"
              @filter="filterVarosok"
              @new-value="createValueVaros"
              :disable="!selectedMegye"
              dense
              color="teal"
              clearable
            />
          </div>

          <div class="col-12">
            <q-input
              outlined
              v-model="formData.cim"
              label="Utca, házszám"
              dense
              color="teal"
              :rules="[val => !!val || 'A cím kötelező']"
            />
          </div>

          <div class="col-12 text-teal-10 text-weight-bold h6 border-bottom q-mt-md">2. Részletek</div>

          <div class="col-12 col-md-4">
            <q-select
              outlined
              v-model="formData.tipus"
              :options="tipusOpciok"
              label="Ingatlan típusa"
              emit-value
              map-options
              dense
              color="teal"
            />
          </div>
          <div class="col-6 col-md-4">
            <q-input
              outlined
              v-model.number="formData.ar"
              type="number"
              label="Ár (Ft/hó)"
              dense
              color="teal"
              suffix="Ft"
              :rules="[val => val >= 50000 || 'Min. 50.000 Ft']"
            />
          </div>
          <div class="col-6 col-md-4">
            <q-input
              outlined
              v-model.number="formData.meret"
              type="number"
              label="Méret (m²)"
              dense
              color="teal"
              suffix="m²"
            />
          </div>

          <div class="col-4 col-md-4">
            <q-input outlined v-model.number="formData.szobak_szama" step="0.5" type="number" label="Szobák" dense color="teal" />
          </div>
          <div class="col-4 col-md-4">
            <q-input outlined v-model.number="formData.emelet" type="number" label="Emelet" dense color="teal" />
          </div>

          <div class="col-12 col-md-4 flex items-center justify-around">
            <q-checkbox v-model="formData.lift" :true-value="1" :false-value="0" label="Lift van" color="teal" />
            <q-checkbox v-model="formData.butorozott" :true-value="1" :false-value="0" label="Bútorozott" color="teal" />
          </div>

          <div class="col-12 text-teal-10 text-weight-bold h6 q-mt-md border-bottom">3. Kapcsolattartó adatai</div>
          <div class="col-12 col-md-4">
            <q-input outlined v-model="formData.nev" label="Teljes név" dense color="teal" :rules="[val => !!val || 'Név kötelező']" />
          </div>
          <div class="col-12 col-md-4">
            <q-input outlined v-model="formData.email" label="E-mail" dense color="teal" :rules="[val => /.+@.+\..+/.test(val) || 'Érvénytelen e-mail']" />
          </div>
          <div class="col-12 col-md-4">
            <q-input outlined v-model="formData.telefon" label="Telefonszám" dense color="teal" mask="+36 ## ### ####" unmasked-value />
          </div>

          <div class="col-12 text-teal-10 text-weight-bold h6 q-mt-md border-bottom">4. Fotók és Leírás</div>
          <div class="col-12">
            <q-file outlined v-model="kepek" label="Képek feltöltése (több is lehet)" append use-chips multiple accept=".jpg, .jpeg, .png" color="teal">
              <template v-slot:prepend><q-icon name="cloud_upload" /></template>
            </q-file>
          </div>

          <div class="col-12">
            <q-input outlined v-model="formData.leiras" type="textarea" label="Részletes leírás" color="teal" counter maxlength="1000" />
          </div>

          <div class="col-12 flex justify-between q-mt-lg">
            <q-btn flat color="grey-7" label="Mégse" @click="$router.push('/')" />
            <q-btn unelevated color="teal" label="Hirdetés közzététele" type="submit" :loading="loading" class="q-px-xl" />
          </div>
        </div>
      </q-form>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAlberletStore } from 'stores/alberletStore';
import { useRouter } from 'vue-router';
import { api } from 'boot/axios';
import { useQuasar } from 'quasar';

const store = useAlberletStore();
const router = useRouter();
const $q = useQuasar();
const loading = ref(false);

const kepek = ref([]);
const selectedMegye = ref(null);
const selectedVaros = ref(null);
const filteredMegyekOptions = ref([]);

const formData = ref({
  cim: '',
  tipus: 1,
  ar: 150000,
  meret: 50,
  szobak_szama: 1,
  emelet: 0,
  lift: 0,
  butorozott: 0,
  leiras: '',
  varos_id: null,
  nev: '',
  email: '',
  telefon: ''
});

const tipusOpciok = [
  { label: 'Szoba', value: 2 },
  { label: 'Lakás', value: 1 },
  { label: 'Ház', value: 0 }
];

// --- JAVÍTOTT VÁROS SZŰRÉS ---
const filteredVarosok = computed(() => {
  if (!selectedMegye.value) return [];

  // 1. Ha meglévő megyét választottunk (objektum, aminek van value-ja)
  if (typeof selectedMegye.value === 'object' && selectedMegye.value?.value) {
    return store.varosok
      .filter(v => v.megye_id === selectedMegye.value.value)
      .map(v => ({ label: v.nev, value: v.id }));
  }

  // 2. Ha új megyét írtunk be (string)
  // Ilyenkor üres listát adunk, de a select engedi az új város beírását
  return [];
});

const filterVarosok = (val, update) => {
  update(() => {
    // A computed property automatikusan frissül
  });
};

const filterMegyek = (val, update) => {
  update(() => {
    const needle = val.toLowerCase();
    if (val === '') {
      filteredMegyekOptions.value = store.megyek;
    } else {
      filteredMegyekOptions.value = store.megyek.filter(
        v => v.label.toLowerCase().indexOf(needle) > -1
      );
    }
  });
};

const createValueMegye = (val, done) => {
  if (val.length > 0) done(val, 'add-unique');
};

const createValueVaros = (val, done) => {
  if (val.length > 0) done(val, 'add-unique');
};

const onSubmit = async () => {
  loading.value = true;
  const data = new FormData();

  // Alapadatok hozzáadása
  Object.keys(formData.value).forEach(key => {
    data.append(key, formData.value[key]);
  });

  // Megye kezelése: ID vagy beírt név
  const megyeAdat = selectedMegye.value?.value || selectedMegye.value;
  data.append('megye_id_vagy_nev', megyeAdat);

  // Város kezelése: ID vagy beírt név
  const varosAdat = selectedVaros.value?.value || selectedVaros.value;
  data.append('varos_id', varosAdat);

  // Képek hozzáadása
  if (kepek.value && kepek.value.length > 0) {
    kepek.value.forEach(file => data.append('kepek[]', file));
  }

  try {
    await api.post('/alberletek', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    $q.notify({ color: 'positive', message: 'Sikeres feltöltés!', icon: 'done' });

    // --- FONTOS: Frissítjük a store-t, hogy az új város/megye is benne legyen ---
    await store.fetchMegyek();
    await store.fetchVarosok();
    await store.fetchAlberletek();

    router.push('/search');
  } catch (error) {
    console.error(error);
    const errorMsg = error.response?.data?.error || 'Hiba a mentés során!';
    $q.notify({ color: 'negative', message: errorMsg });
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  if (store.megyek.length === 0) await store.fetchMegyek();
  if (store.varosok.length === 0) await store.fetchVarosok();
  filteredMegyekOptions.value = store.megyek;
});
</script>

<style scoped>
.form-card {
  width: 100%;
  max-width: 800px;
  border-radius: 20px;
}
.border-bottom {
  border-bottom: 1px solid #e0e0e0;
  padding-bottom: 8px;
}
</style>
