<template>
  <q-page padding class="bg-grey-1 flex flex-center">
    <q-card flat bordered class="form-card shadow-2">
      <q-card-section class="bg-teal text-white">
        <div class="text-h5 text-weight-bold text-center">Új hirdetés feladása</div>
      </q-card-section>

      <q-form @submit="onSubmit" class="q-gutter-md q-pa-lg">
        <div class="row q-col-gutter-md">
          
          <div class="col-12 text-teal-10 text-weight-bold h6 border-bottom">1. Ingatlan adatai</div>
          <div class="col-12">
            <q-input outlined v-model="formData.cim" label="Pontos cím" dense color="teal" :rules="[val => !!val || 'A cím kötelező']" />
          </div>
          <div class="col-12 col-md-6">
            <q-select outlined v-model="formData.varos_id" :options="store.varosok" label="Város" emit-value map-options dense color="teal" />
          </div>
          <div class="col-12 col-md-6">
            <q-select outlined v-model="formData.tipus" :options="tipusOpciok" label="Ingatlan típusa" emit-value map-options dense color="teal" />
          </div>
          <div class="col-6 col-md-4">
            <q-input outlined v-model.number="formData.ar" type="number" label="Ár (Ft)" dense color="teal" suffix="Ft" />
          </div>
          <div class="col-6 col-md-4">
            <q-input outlined v-model.number="formData.meret" type="number" label="Méret (m²)" dense color="teal" suffix="m²" />
          </div>
          <div class="col-12 col-md-4">
            <q-input outlined v-model.number="formData.szobak_szama" step="0.5" type="number" label="Szobák" dense color="teal" />
          </div>

          <div class="col-12 text-teal-10 text-weight-bold h6 q-mt-md">2. Kapcsolattartó adatai</div>
          <div class="col-12 col-md-4">
            <q-input outlined v-model="formData.nev" label="Teljes név" dense color="teal" :rules="[val => !!val || 'Név kötelező']" />
          </div>
          <div class="col-12 col-md-4">
            <q-input outlined v-model="formData.email" label="E-mail" dense color="teal" :rules="[val => /.+@.+\..+/.test(val) || 'Érvénytelen e-mail']" />
          </div>
          <div class="col-12 col-md-4">
            <q-input outlined v-model="formData.telefon" label="Telefonszám" dense color="teal" mask="+36 ## ### ####" unmasked-value />
          </div>

          <div class="col-12 text-teal-10 text-weight-bold h6 q-mt-md">3. Fotók</div>
          <div class="col-12">
            <q-file outlined v-model="kepek" label="Képek kiválasztása (max 5MB/db)" append use-chips multiple accept=".jpg, .jpeg, .png" color="teal">
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
import { ref, onMounted } from 'vue';
import { useAlberletStore } from 'stores/alberletStore';
import { useRouter } from 'vue-router';
import { api } from 'boot/axios';
import { useQuasar } from 'quasar';

const store = useAlberletStore();
const router = useRouter();
const $q = useQuasar();
const loading = ref(false);

const kepek = ref([]); // Itt tároljuk a fájlokat, amíg el nem küldjük

const formData = ref({
  cim: '',
  tipus: 1, // Alapértelmezett: Lakás
  ar: 100000,
  meret: 40,
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
  { label: 'Szoba', value: 0 },
  { label: 'Lakás', value: 1 },
  { label: 'Ház', value: 2 }
];

const onSubmit = async () => {
  loading.value = true;
  
  // LOGIKA: Létrehozunk egy "dobozt" (FormData), amibe mindent belepakolunk
  const data = new FormData();
  
  // 1. Szöveges adatok hozzáadása
  Object.keys(formData.value).forEach(key => {
    data.append(key, formData.value[key]);
  });

  // 2. Képek hozzáadása (tömbként, ahogy a Laravel 'kepek.*' várja)
  if (kepek.value && kepek.value.length > 0) {
    kepek.value.forEach(file => {
      data.append('kepek[]', file);
    });
  }

  try {
    // 3. Küldés multipart/form-data fejléccel
    await api.post('/alberletek', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    $q.notify({ color: 'positive', message: 'Hirdetés sikeresen feladva!', icon: 'check', position: 'top' });
    
    // Frissítjük a listát a store-ban, hogy az új hirdetés is ott legyen
    await store.fetchAlberletek();
    router.push('/search');
  } catch (error) {
    console.error("Hiba történt:", error.response?.data);
    $q.notify({ 
      color: 'negative', 
      message: error.response?.data?.message || 'Szerver hiba történt a mentéskor!' 
    });
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  if (store.varosok.length === 0) store.fetchVarosok();
});
</script>

<style scoped>
.form-card { width: 100%; max-width: 800px; border-radius: 20px; }
.border-bottom { border-bottom: 1px solid #e0e0e0; padding-bottom: 8px; }
</style>