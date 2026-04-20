<template>
  <q-page padding class="bg-grey-1 flex flex-center">
    <div class="urlap-kontener">
      <q-card flat bordered class="urlap-kartya shadow-3">
        <q-card-section class="bg-teal-10 text-white q-pa-lg">
          <div class="text-h5 text-weight-bolder">Hirdetés Feladása</div>
          <div class="text-caption">Pár lépés és kikerül az ingatlanod az oldalra</div>
        </q-card-section>

        <q-stepper
          v-model="lepes"
          ref="stepper"
          color="teal"
          animated
          header-nav
          class="no-shadow"
        >
          <!-- 1. lépés: Helyszín -->
          <q-step :name="1" title="Helyszín" icon="place" :done="lepes > 1">
            <q-form ref="form1" class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-select
  outlined 
  v-model="valasztottMegye"
  use-input 
  hide-selected 
  fill-input
  label="Megye" 
  :options="szurtMegyek"
  @filter="megyeSzures"
  @new-value="ujMegyeLetrehozas"
  @update:model-value="valasztottVaros = null"
  @blur="valasztottMegye = elsoBetuNagy(valasztottMegye)"
  dense color="teal" clearable
  :rules="[szabalyok.kotelezoValasztas]"
/>
              </div>
              <div class="col-12 col-md-6">
                <q-select
  outlined 
  v-model="valasztottVaros"
  use-input 
  hide-selected 
  fill-input
  label="Város" 
  :options="szurtVarosok"
  @filter="varosSzures"
  @new-value="ujVarosLetrehozas"
  @blur="valasztottVaros = elsoBetuNagy(valasztottVaros)"
  :disable="!valasztottMegye"
  dense color="teal" clearable
  :rules="[szabalyok.kotelezoValasztas]"
/>
              </div>
              <div class="col-12">
                <q-input
                  outlined v-model="hirdetesAdat.cim"
                  label="Teljes cím"
                  placeholder="8900 Zalaegerszeg, Arany János Út 12."
                  dense color="teal"
                  @blur="cimVeglegesites"
                  :rules="[
                    szabalyok.kotelezo,
                    val => /^\d{4}\s.+,\s.+\s\d+\.?$/.test(val) || 'Minta: 8900 Város, Utca házszám.'
                  ]"
                >
                  <template v-slot:prepend>
                    <q-icon name="map" color="teal" />
                  </template>
                </q-input>
              </div>
            </q-form>
          </q-step>

          <!-- 2. lépés: Ingatlan adatok -->
          <q-step :name="2" title="Ingatlan adatok" icon="home_work" :done="lepes > 2">
            <q-form ref="form2" class="row q-col-gutter-lg">
              <div class="col-12 col-md-6">
                <q-select
                  outlined v-model="hirdetesAdat.tipus"
                  :options="tipusOpciok"
                  label="Ingatlan típusa"
                  emit-value map-options
                  dense color="teal"
                >
                  <template v-slot:prepend>
                    <q-icon name="category" color="teal" />
                  </template>
                </q-select>
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  outlined v-model.number="hirdetesAdat.ar"
                  type="number"
                  label="Bérleti díj"
                  dense color="teal"
                  suffix="Ft / hó"
                  :rules="[val => val >= 10000 || 'Minimum 10.000 Ft']"
                >
                  <template v-slot:prepend>
                    <q-icon name="payments" color="teal" />
                  </template>
                </q-input>
              </div>

              <div class="col-12 col-sm-4">
                <q-input
                  outlined v-model.number="hirdetesAdat.meret"
                  type="number"
                  label="Alapterület"
                  dense color="teal"
                  suffix="m²"
                  :rules="[val => val >= 5 || 'Minimum 5 m²']"
                >
                  <template v-slot:prepend>
                    <q-icon name="straighten" color="teal" />
                  </template>
                </q-input>
              </div>

              <div class="col-12 col-sm-4">
                <q-input
                  outlined v-model.number="hirdetesAdat.emelet"
                  type="number"
                  label="Emelet"
                  dense color="teal"
                >
                  <template v-slot:prepend>
                    <q-icon name="layers" color="teal" />
                  </template>
                </q-input>
              </div>

              <div class="col-12 col-sm-4">
                <q-input
                  v-if="!szobaE"
                  outlined
                  v-model.number="hirdetesAdat.szobak_szama"
                  type="number"
                  label="Szobák száma"
                  dense color="teal"
                  step="0.5"
                >
                  <template v-slot:prepend>
                    <q-icon name="bed" color="teal" />
                  </template>
                </q-input>
                <q-input
                  v-else
                  outlined
                  label="Szobák száma"
                  dense color="teal"
                  readonly
                  placeholder="1 szoba"
                  bg-color="grey-2"
                >
                  <template v-slot:prepend>
                    <q-icon name="meeting_room" color="grey-7" />
                  </template>
                </q-input>
              </div>

              <div class="col-12">
                <div class="row items-center justify-around bg-teal-1 q-pa-md rounded-borders border-teal shadow-1">
                  <div @click="liftUzenetHaTiltott" class="cursor-pointer">
                    <q-checkbox
                      v-model="hirdetesAdat.lift"
                      :true-value="1"
                      :false-value="0"
                      label="Van lift"
                      color="teal"
                      :disable="liftTiltva"
                    >
                      <q-tooltip v-if="liftTiltva">Földszintes háznál nem választható lift!</q-tooltip>
                    </q-checkbox>
                  </div>
                  <q-checkbox
                    v-model="hirdetesAdat.butorozott"
                    :true-value="1"
                    :false-value="0"
                    label="Bútorozott"
                    color="teal"
                  />
                </div>
              </div>
            </q-form>
          </q-step>

          <!-- 3. lépés: Média & Leírás -->
          <q-step :name="3" title="Média & Leírás" icon="collections" :done="lepes > 3">
            <q-form ref="form3" class="column q-gutter-md">
              <q-file
                outlined
                v-model="kepek"
                label="Képek kiválasztása (több is választható)"
                append
                use-chips
                multiple
                accept=".jpg,.jpeg,.png"
                color="teal"
                :rules="[val => (val && val.length > 0) || 'Legalább egy kép szükséges!']"
              >
                <template v-slot:prepend>
                  <q-icon name="cloud_upload" />
                </template>
              </q-file>

              <q-input
                outlined
                v-model="hirdetesAdat.leiras"
                type="textarea"
                label="Az ingatlan részletes bemutatása..."
                color="teal"
                counter
                maxlength="1000"
                :rules="[val => szabalyok.minHossz(val, 20)]"
              />
            </q-form>
          </q-step>

          <!-- 4. lépés: Kapcsolat -->
          <q-step :name="4" title="Kapcsolat" icon="contact_mail">
            <q-form ref="form4" class="row q-col-gutter-lg">
              <div class="col-12 col-md-6">
                <q-input
                  outlined
                  v-model="hirdetesAdat.email"
                  label="E-mail cím"
                  type="email"
                  dense
                  color="teal"
                  @blur="ellenorizFelhasznalot"
                  :rules="[szabalyok.kotelezo, szabalyok.email]"
                >
                  <template v-slot:prepend>
                    <q-icon name="alternate_email" color="teal" />
                  </template>
                </q-input>
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  outlined
                  v-model="hirdetesAdat.nev"
                  label="Teljes név"
                  dense
                  color="teal"
                  :readonly="letezoFelhasznalo"
                  :bg-color="letezoFelhasznalo ? 'grey-2' : ''"
                  :rules="[szabalyok.kotelezo]"
                >
                  <template v-slot:prepend>
                    <q-icon name="person" color="teal" />
                  </template>
                </q-input>
              </div>

              <div class="col-12">
                <q-input
                  outlined
                  v-model="hirdetesAdat.telefon"
                  label="Telefonszám"
                  dense
                  color="teal"
                  mask="+36 ## ### ####"
                  fill-mask
                  :readonly="letezoFelhasznalo"
                  :bg-color="letezoFelhasznalo ? 'grey-2' : ''"
                  :rules="[szabalyok.kotelezo]"
                >
                  <template v-slot:prepend>
                    <q-icon name="phone" color="teal" />
                  </template>
                </q-input>
              </div>
            </q-form>
          </q-step>

          <template v-slot:navigation>
            <q-stepper-navigation class="flex justify-between q-pa-md">
              <q-btn
                v-if="lepes > 1"
                flat
                color="teal"
                @click="$refs.stepper.previous()"
                label="Vissza"
              />
              <div v-else></div>

              <q-btn
                @click="navigacioKovetkezo"
                color="teal-10"
                :label="lepes === 4 ? 'Hirdetés beküldése' : 'Folytatás'"
                :loading="toltes"
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

const stepper = ref(null);
const form1 = ref(null);
const form2 = ref(null);
const form3 = ref(null);
const form4 = ref(null);

const lepes = ref(1);
const toltes = ref(false);
const kepek = ref([]);

const valasztottMegye = ref(null);
const valasztottVaros = ref(null);
const szurtMegyek = ref([]);
const letezoFelhasznalo = ref(false);

const hirdetesAdat = ref({
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
  telefon: '',
  aktiv: 0
});

// ====================== USER ELLENŐRZÉS ======================
// ====================== USER ELLENŐRZÉS ======================
const ellenorizFelhasznalot = async () => {
  if (!hirdetesAdat.value.email || !/.+@.+\..+/.test(hirdetesAdat.value.email)) return;

  try {
    const { data } = await api.get(`/users/check?email=${encodeURIComponent(hirdetesAdat.value.email)}`);

    if (data.exists) {
      // Itt a trükk: Ha már létezik, beírjuk a rendszerben lévő adatokat
      hirdetesAdat.value.nev = data.user.nev;
      hirdetesAdat.value.telefon = data.user.telefon;
      letezoFelhasznalo.value = true; // Ez a változó a HTML-ben readonly-vá teszi a mezőket

      $q.notify({
        type: 'info',
        message: 'Ehhez az e-mailhez már tartoznak adatok. Biztonsági okokból ezeket rögzítettük.',
        timeout: 4000
      });
    } else {
      letezoFelhasznalo.value = false;
    }
  } catch (err) {
    console.error("Hiba az ellenőrzéskor", err);
  }
};

// ====================== CÍM JAVÍTÁSA ======================
const cimVeglegesites = () => {
  let v = hirdetesAdat.value.cim.trim();
  if (!v) return;

  // 1. ELLENŐRZÉS: Ha már tökéletes a formátum, ne csináljon semmit!
  // Ez megakadályozza az örökös "újrajavítást"
  const marJoFormatu = /^\d{4}\s.+,\s.+\s\d+\.?$/.test(v);
  if (marJoFormatu) return;

  // 2. HA NEM JÓ, AKKOR FORMÁZZUK:
  let részek = v.split(/\s+/);
  let formazott = részek.map((szo, index) => {
    // Az első rész (irányítószám) marad szám
    if (index === 0 && /^\d+$/.test(szo)) return szo;
    // Minden más Szóeleji Nagybetű
    return szo.charAt(0).toUpperCase() + szo.slice(1).toLowerCase();
  }).join(' ');

  // Irányítószám + város után vessző (ha nincs)
  formazott = formazott.replace(/^(\d{4}\s[^\s,]+)(?!\s*,)/, '$1,');
  
  // Pont a végére (ha nincs)
  if (!formazott.endsWith('.')) formazott += '.';

  hirdetesAdat.value.cim = formazott;
};


// ====================== SZÖVEG FORMÁZÓ SEGÉDFÜGGVÉNY ======================
const elsoBetuNagy = (szoveg) => {
  if (!szoveg || typeof szoveg !== 'string') return szoveg;
  return szoveg
    .split(' ')
    .map(szo => szo.charAt(0).toUpperCase() + szo.slice(1).toLowerCase())
    .join(' ');
};

// ====================== FIGYELŐK A MEGYÉRE ÉS VÁROSRA ======================
watch(valasztottMegye, (ujErtek) => {
  // Ha manuálisan írt be valamit (string), akkor nagybetűsítjük
  if (typeof ujErtek === 'string' && ujErtek.length > 0) {
    valasztottMegye.value = elsoBetuNagy(ujErtek);
  }
});

watch(valasztottVaros, (ujErtek) => {
  // Ha manuálisan írt be valamit (string), akkor nagybetűsítjük
  if (typeof ujErtek === 'string' && ujErtek.length > 0) {
    valasztottVaros.value = elsoBetuNagy(ujErtek);
  }
  // Az eredeti varos_id mentő logika marad:
  hirdetesAdat.value.varos_id = ujErtek?.value || ujErtek;
});

// ====================== KÜLDÉS ======================
const navigacioKovetkezo = async () => {
  const forms = [form1, form2, form3, form4];
  const aktualisForm = forms[lepes.value - 1];

  if (aktualisForm.value) {
    const valid = await aktualisForm.value.validate();
    if (!valid) {
      $q.notify({ type: 'warning', message: 'Kérjük, töltsön ki minden mezőt megfelelően!' });
      return;
    }
  }

  if (lepes.value === 4) {
    hirdetesKuldes();
  } else {
    stepper.value.next();
  }
};

const hirdetesKuldes = async () => {
  toltes.value = true;

  const kuldendoAdat = new FormData();

  // Megye
  const megyeErtek = valasztottMegye.value?.label || valasztottMegye.value;
  if (megyeErtek) kuldendoAdat.append('megye_id_vagy_nev', megyeErtek);

  // Város ID biztosítása
  let varosId = valasztottVaros.value;
  if (typeof varosId === 'object' && varosId !== null) {
    varosId = varosId.value;
  }
  if (varosId) kuldendoAdat.append('varos_id', varosId);

  // Explicit mezők (hogy ne legyen típus hiba)
  kuldendoAdat.append('cim', hirdetesAdat.value.cim);
  kuldendoAdat.append('tipus', hirdetesAdat.value.tipus);
  kuldendoAdat.append('ar', hirdetesAdat.value.ar);
  kuldendoAdat.append('meret', hirdetesAdat.value.meret);
  kuldendoAdat.append('szobak_szama', hirdetesAdat.value.szobak_szama);
  kuldendoAdat.append('emelet', hirdetesAdat.value.emelet ?? 0);
  kuldendoAdat.append('lift', hirdetesAdat.value.lift ? 1 : 0);
  kuldendoAdat.append('butorozott', hirdetesAdat.value.butorozott ? 1 : 0);
  kuldendoAdat.append('leiras', hirdetesAdat.value.leiras);
  kuldendoAdat.append('nev', hirdetesAdat.value.nev);
  kuldendoAdat.append('email', hirdetesAdat.value.email);
  kuldendoAdat.append('telefon', hirdetesAdat.value.telefon);
  kuldendoAdat.append('aktiv', 0);

  // Képek
  kepek.value.forEach(f => kuldendoAdat.append('kepek[]', f));

  try {
    await api.post('/alberletek', kuldendoAdat, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    $q.notify({
      type: 'positive',
      message: 'Hirdetés sikeresen beküldve! 24 órán belül aktiváljuk.',
      icon: 'check'
    });

    router.push('/search');
  } catch (error) {
    console.error(error.response?.data);
    $q.notify({
      type: 'negative',
      message: error.response?.data?.error_message || 'Hiba a beküldés során!'
    });
  } finally {
    toltes.value = false;
  }
};

// ====================== HELYSZÍN SZŰRŐK ======================
const megyeSzures = (val, update) => {
  update(() => {
    const keresett = val.toLowerCase();
    szurtMegyek.value = val === '' 
      ? store.megyek 
      : store.megyek.filter(v => v.label.toLowerCase().includes(keresett));
  });
};

const szurtVarosok = computed(() => {
  if (!valasztottMegye.value) return [];
  const megyeId = typeof valasztottMegye.value === 'object' 
    ? valasztottMegye.value.value 
    : valasztottMegye.value;
  
  return store.varosok
    .filter(v => v.megye_id === megyeId)
    .map(v => ({ label: v.label, value: v.value }));
});

const varosSzures = (val, update) => { update(() => {}); };

const ujMegyeLetrehozas = (val, done) => val.length > 0 && done(val, 'add-unique');
const ujVarosLetrehozas = (val, done) => val.length > 0 && done(val, 'add-unique');

// ====================== COMPUTED & WATCH ======================
const tipusOpciok = [
  { label: 'Lakás', value: 1 },
  { label: 'Ház', value: 0 },
  { label: 'Szoba', value: 2 }
];

const szobaE = computed(() => hirdetesAdat.value.tipus === 2);
const liftTiltva = computed(() => hirdetesAdat.value.tipus === 0 && hirdetesAdat.value.emelet <= 1);

watch(liftTiltva, (uj) => { if (uj) hirdetesAdat.value.lift = 0; });
watch(() => hirdetesAdat.value.tipus, (uj) => {
  if (uj === 2) hirdetesAdat.value.szobak_szama = 1;
});
watch(valasztottVaros, (uj) => {
  hirdetesAdat.value.varos_id = uj?.value || uj;
});

const szabalyok = {
  kotelezo: val => !!val || 'Kötelező mező',
  kotelezoValasztas: val => !!val || 'Kötelező választani',
  email: val => /.+@.+\..+/.test(val) || 'Érvénytelen email cím',
  minHossz: (val, len) => (val && val.length >= len) || `Legalább ${len} karakter szükséges`
};

onMounted(async () => {
  await Promise.all([
    store.fetchMegyek(false),
    store.fetchVarosok(false)
  ]);
  szurtMegyek.value = store.megyek;
});
</script>

<style scoped>
.urlap-kontener { width: 100%; max-width: 920px; }
.urlap-kartya { border-radius: 24px; overflow: hidden; }
.no-shadow { box-shadow: none !important; }
</style>