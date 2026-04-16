<template>
  <q-page padding class="bg-grey-1 flex flex-center">
    <div class="form-container">
      <q-card flat bordered class="form-card shadow-3">
        <q-card-section class="bg-teal-10 text-white q-pa-lg">
          <div class="text-h5 text-weight-bolder">Hirdetés Feladása</div>
          <div class="text-caption">Pár lépés és kikerül az ingatlanod az oldalra</div>
        </q-card-section>

        <q-stepper
          v-model="step"
          ref="stepper"
          color="teal"
          animated
          header-nav
          class="no-shadow"
        >
          <q-step :name="1" title="Helyszín" icon="place" :done="step > 1" :error="!locationIsValid">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-select
                  outlined v-model="selectedMegye" use-input fill-input input-debounce="0"
                  label="Megye" :options="filteredMegyekOptions" @filter="filterMegyek"
                  @new-value="createValueMegye" dense color="teal" clearable
                />
              </div>
              <div class="col-12 col-md-6">
                <q-select
                  outlined v-model="selectedVaros" use-input fill-input input-debounce="0"
                  label="Város" :options="filteredVarosok" @filter="filterVarosok"
                  @new-value="createValueVaros" :disable="!selectedMegye" dense color="teal" clearable
                />
              </div>
              <div class="col-12">
                <q-input outlined v-model="formData.cim" label="Utca, házszám" dense color="teal" />
              </div>
            </div>
          </q-step>

          <q-step :name="2" title="Ingatlan adatok" icon="home_work" :done="step > 2">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-select outlined v-model="formData.tipus" :options="tipusOpciok" label="Típus" emit-value map-options dense color="teal" />
              </div>
              <div class="col-12 col-md-6">
                <q-input outlined v-model.number="formData.ar" type="number" label="Ár (Ft/hó)" dense color="teal" suffix="Ft" />
              </div>
              <div class="col-6 col-md-4">
                <q-input outlined v-model.number="formData.meret" type="number" label="Méret" dense color="teal" suffix="m²" />
              </div>

              <div class="col-4">
    <q-input outlined v-model.number="formData.emelet" type="number" label="Emelet" dense color="teal" />
  </div>

              <div class="col-6 col-md-4">
                <q-input outlined v-model.number="formData.szobak_szama" step="0.5" type="number" label="Szobák" dense color="teal" />
              </div>
              <div class="col-12 col-md-4 flex items-center justify-around bg-grey-2 rounded-borders">
                <q-checkbox v-model="formData.lift" :true-value="1" :false-value="0" label="Lift" color="teal" />
                <q-checkbox v-model="formData.butorozott" :true-value="1" :false-value="0" label="Bútorozott" color="teal" />
              </div>
            </div>
          </q-step>

          <q-step :name="3" title="Média & Leírás" icon="collections" :done="step > 3">
            <div class="column q-gutter-md">
              <q-file
                outlined v-model="kepek" label="Képek kiválasztása" 
                append use-chips multiple accept=".jpg, .jpeg, .png" color="teal"
                hint="Több képet is kijelölhetsz egyszerre"
              >
                <template v-slot:prepend><q-icon name="cloud_upload" /></template>
              </q-file>
              <q-input outlined v-model="formData.leiras" type="textarea" label="Az ingatlan bemutatása..." color="teal" counter maxlength="1000" />
            </div>
          </q-step>

          <q-step :name="4" title="Kapcsolat" icon="contact_mail">
            <div class="row q-col-gutter-md">
              <div class="col-12">
                <q-input outlined v-model="formData.nev" label="Az Ön neve" dense color="teal" icon="person" />
              </div>
              <div class="col-12 col-md-6">
                <q-input outlined v-model="formData.email" label="E-mail cím" dense color="teal" />
              </div>
              <div class="col-12 col-md-6">
                <q-input outlined v-model="formData.telefon" label="Telefonszám" dense color="teal" mask="+36 ## ### ####" fill-mask />
              </div>
            </div>
          </q-step>

          <template v-slot:navigation>
            <q-stepper-navigation class="flex justify-between q-pa-md">
              <q-btn v-if="step > 1" flat color="teal" @click="$refs.stepper.previous()" label="Vissza" />
              <div v-else></div> <q-btn 
                @click="step === 4 ? onSubmit() : $refs.stepper.next()" 
                color="teal-10" 
                :label="step === 4 ? 'Hirdetés beküldése' : 'Folytatás'" 
                :loading="loading"
                unelevated
                rounded
                class="q-px-lg"
              />
            </q-stepper-navigation>
          </template>
        </q-stepper>
      </q-card>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useAlberletStore } from 'stores/alberletStore';
import { useRouter } from 'vue-router';
import { api } from 'boot/axios';
import { useQuasar } from 'quasar';

const store = useAlberletStore();
const router = useRouter();
const $q = useQuasar();

const step = ref(1);
const loading = ref(false);
const kepek = ref([]);
const selectedMegye = ref(null);
const selectedVaros = ref(null);
const filteredMegyekOptions = ref([]);

const formData = ref({
  cim: '', tipus: 1, ar: 150000, meret: 50, szobak_szama: 1,
  emelet: 0, lift: 0, butorozott: 0, leiras: '',
  varos_id: null, nev: '', email: '', telefon: ''
});

const tipusOpciok = [
  { label: 'Lakás', value: 1 }, { label: 'Ház', value: 0 }, { label: 'Szoba', value: 2 }
];

// Validáció figyelés
const locationIsValid = computed(() => !!selectedMegye.value && !!selectedVaros.value && !!formData.value.cim);

watch(selectedVaros, (val) => {
  formData.value.varos_id = (val && typeof val === 'object') ? val.value : val;
});

const filterMegyek = (val, update) => {
  update(() => {
    const needle = val.toLowerCase();
    filteredMegyekOptions.value = val === '' ? store.megyek : store.megyek.filter(v => v.label.toLowerCase().indexOf(needle) > -1);
  });
};

const filteredVarosok = computed(() => {
  if (!selectedMegye.value || typeof selectedMegye.value !== 'object') return [];
  return store.varosok
    .filter(v => v.megye_id === selectedMegye.value.value)
    .map(v => ({ label: v.label, value: v.value }));
});

const filterVarosok = (val, update) => { update(() => {}); };
const createValueMegye = (val, done) => { if (val.length > 0) done(val, 'add-unique'); };
const createValueVaros = (val, done) => { if (val.length > 0) done(val, 'add-unique'); };

const onSubmit = async () => {
  loading.value = true;
  const data = new FormData();

  const megyeAdat = typeof selectedMegye.value === 'object' ? selectedMegye.value?.value : selectedMegye.value;
  data.append('megye_id_vagy_nev', megyeAdat);

  Object.keys(formData.value).forEach(key => {
    if (formData.value[key] !== null) data.append(key, formData.value[key]);
  });

  if (kepek.value.length > 0) {
    kepek.value.forEach(file => data.append('kepek[]', file));
  }

  try {
    await api.post('/alberletek', data, { headers: { 'Content-Type': 'multipart/form-data' } });
    $q.notify({ type: 'positive', message: 'Munkatársunk megkapta hirdetését és 24-órán belül aktívvá tesszük!!' });
    await store.fetchAlberletek();
    router.push('/search');
  
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
.form-container {
  width: 100%;
  max-width: 900px;
}
.form-card {
  border-radius: 24px;
  overflow: hidden;
}
.no-shadow {
  box-shadow: none !important;
}
</style>