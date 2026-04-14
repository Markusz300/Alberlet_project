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
            <q-select outlined v-model="selectedMegyeId" :options="store.megyek" label="Megye" 
              emit-value map-options dense color="teal" @update:model-value="formData.varos_id = null" />
          </div>

          <div class="col-12 col-md-6">
            <q-select outlined v-model="formData.varos_id" :options="filteredVarosok" label="Város" 
              emit-value map-options dense color="teal" :disable="!selectedMegyeId"
              :rules="[val => !!val || 'Város választása kötelező']" />
          </div>

          <div class="col-12">
            <q-input outlined v-model="formData.cim" label="Utca, házszám" dense color="teal" :rules="[val => !!val || 'A cím kötelező']" />
          </div>

          <div class="col-12 text-teal-10 text-weight-bold h6 border-bottom q-mt-md">2. Részletek</div>
          
          <div class="col-12 col-md-4">
            <q-select outlined v-model="formData.tipus" :options="tipusOpciok" label="Ingatlan típusa" emit-value map-options dense color="teal" />
          </div>
          <div class="col-6 col-md-4">
            <q-input outlined v-model.number="formData.ar" type="number" label="Ár (Ft/hó)" dense color="teal" suffix="Ft" :rules="[val => val >= 50000 || 'Min. 50.000 Ft']" />
          </div>
          <div class="col-6 col-md-4">
            <q-input outlined v-model.number="formData.meret" type="number" label="Méret (m²)" dense color="teal" suffix="m²" />
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
            <q-input outlined v-model="formData.leiras" type="textarea" label="Részletes leírás (környék, rezsi, kisállat stb.)" color="teal" counter maxlength="1000" />
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
const selectedMegyeId = ref(null);

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

// Csak azokat a városokat mutatja, amik a kiválasztott megyéhez tartoznak
const filteredVarosok = computed(() => {
  if (!selectedMegyeId.value) return [];
  return store.varosok.filter(v => v.megye_id === selectedMegyeId.value);
});

const tipusOpciok = [
  { label: 'Szoba', value: 0 },
  { label: 'Lakás', value: 1 },
  { label: 'Ház', value: 2 }
];

const onSubmit = async () => {
  loading.value = true;
  const data = new FormData();
  
  // Minden adatot átrakunk a FormData-ba
  Object.keys(formData.value).forEach(key => {
    // A null értékeket ne küldjük el, vagy alakítsuk üres stringgé, hogy a Laravel ne dobjon hibát
    const val = formData.value[key] === null ? '' : formData.value[key];
    data.append(key, val);
  });

  if (kepek.value && kepek.value.length > 0) {
    kepek.value.forEach(file => {
      data.append('kepek[]', file);
    });
  }

  try {
    await api.post('/alberletek', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    $q.notify({ color: 'positive', message: 'Sikeres feltöltés!', icon: 'done' });
    store.fetchAlberletek();
    router.push('/search');
  } catch (error) {
    // Itt kiírjuk a konkrét validációs hibákat, ha vannak (pl. túl nagy kép)
    const errorMsg = error.response?.data?.errors 
      ? Object.values(error.response.data.errors).flat().join(', ')
      : 'Hiba a mentés során!';
      
    $q.notify({ color: 'negative', message: errorMsg });
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  if (store.megyek.length === 0) await store.fetchMegyek();
  if (store.varosok.length === 0) await store.fetchVarosok();
});
</script>

<style scoped>
.form-card { width: 100%; max-width: 800px; border-radius: 20px; }
.border-bottom { border-bottom: 1px solid #e0e0e0; padding-bottom: 8px; }
</style>