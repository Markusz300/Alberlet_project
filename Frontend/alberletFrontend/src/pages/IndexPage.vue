<template>
  <q-page padding class="bg-grey-1">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <h4 class="text-weight-bolder q-ma-none text-teal-10">Albérlet Kereső</h4>
        <div class="text-subtitle2 text-grey-7">Találd meg álmaid otthonát</div>
      </div>
      <q-btn :color="showFilters ? 'negative' : 'teal'" :icon="showFilters ? 'close' : 'tune'"
        :label="showFilters ? 'Bezárás' : 'Összes szűrő'" @click="showFilters = !showFilters" unelevated rounded />
    </div>

    <q-slide-transition>
      <div v-show="showFilters" class="q-mb-xl">
        <q-card flat bordered class="rounded-borders shadow-sm bg-white">
          <q-card-section>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-sm-6 col-md-3">
                <div class="text-weight-bold q-mb-xs"><q-icon name="payments" /> Ár (Ft/hó)</div>
                <div class="row q-col-gutter-sm">
                  <q-input dense outlined v-model.number="store.filters.min_ar" label="Min" class="col-6" type="number"
                    min="0" :rules="[val => val >= 0 || 'Nem lehet negatív']"  hide-bottom-space color="teal" />
                  <q-input dense outlined v-model.number="store.filters.max_ar" label="Max" class="col-6" type="number"
                    min="0" :rules="[val => val >= 0 || 'Nem lehet negatív']"  hide-bottom-space color="teal" />
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <div class="text-weight-bold q-mb-xs"><q-icon name="bed" /> Szobák</div>
                <div class="row q-col-gutter-sm">
                  <q-input dense outlined v-model.number="store.filters.min_szoba" label="Min" class="col-6"
                    type="number" step="0.5" min="0" :rules="[val => val >= 0 || 'Nem lehet negatív']" hide-bottom-space
                    color="teal" />
                  <q-input dense outlined v-model.number="store.filters.max_szoba" label="Max" class="col-6"
                    type="number" step="0.5" min="0" :rules="[val => val >= 0 || 'Nem lehet negatív']" hide-bottom-space
                    color="teal" />
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <div class="text-weight-bold q-mb-xs"><q-icon name="straighten" /> Méret (m²)</div>
                <div class="row q-col-gutter-sm">
                  <q-input dense outlined v-model.number="store.filters.min_meret" label="Min" class="col-6"
                    type="number" min="0" :rules="[val => val >= 0 || 'Nem lehet negatív']" hide-bottom-space
                    color="teal" />
                  <q-input dense outlined v-model.number="store.filters.max_meret" label="Max" class="col-6"
                    type="number" min="0" hide-bottom-space color="teal" />
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <div class="text-weight-bold q-mb-xs"><q-icon name="category" /> Ingatlan típusa</div>
                <q-select dense outlined v-model="store.filters.tipus" :options="tipusOpciok" emit-value map-options
                  color="teal" />
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <div class="text-weight-bold q-mb-xs"><q-icon name="location_on" /> Város</div>
                <q-select dense outlined v-model="store.filters.varos_id" :options="filteredVarosok" use-input
                  fill-input hide-selected input-debounce="0" label="Város választása" color="teal" emit-value
                  map-options clearable @filter="filterFn"
                  @input-value="val => { if (val === '') store.filters.varos_id = null }"
                  @clear="store.filters.varos_id = null">
                  <template v-slot:no-option>
                    <q-item>
                      <q-item-section class="text-grey italic text-caption">
                        Sajnos még nincs itt albérletünk :(...
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <div class="text-weight-bold q-mb-xs">Kényelem</div>
                <div class="row q-col-gutter-sm">
                  <q-select dense outlined v-model="store.filters.butorozott" :options="igenNemOpciok" label="Bútor?"
                    class="col-6" emit-value map-options color="teal" />
                  <q-select dense outlined v-model="store.filters.lift" :options="igenNemOpciok" label="Lift?"
                    class="col-6" emit-value map-options color="teal" />
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-3">
                <div class="text-weight-bold q-mb-xs">Rendezés</div>
                <q-select dense outlined v-model="store.filters.sort" :options="rendezesOpciok" emit-value map-options
                  color="teal" />
              </div>

              <div class="col-12 flex justify-end q-gutter-sm q-pt-md">
                <q-btn unelevated color="teal" label="Keresés indítása" icon="search" class="q-px-xl"
                  @click="store.fetchAlberletek(1)" />
                <q-btn flat color="grey-7" label="Szűrők törlése" icon="restart_alt" @click="store.resetFilters" />

              </div>

            </div>
          </q-card-section>
        </q-card>
      </div>
    </q-slide-transition>

    <div v-if="store.alberletek && store.alberletek.length > 0" class="row q-col-gutter-xl">
      <div v-for="alb in store.alberletek" :key="alb.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
        <q-card flat class="property-card overflow-hidden shadow-1">
          <q-img :src="store.formatImageUrl(alb.kepek)" :ratio="4 / 3">
            <div class="absolute-top-left q-ma-sm bg-transparent">
              <q-badge color="teal" class="q-pa-sm text-weight-bold shadow-2">
                {{ alb.tipus }}
              </q-badge>
            </div>
            <div class="absolute-bottom-right bg-black text-white q-pa-sm q-px-md text-weight-bolder"
              style="border-top-left-radius: 12px">
              {{ alb.ar?.toLocaleString() }} Ft
            </div>
          </q-img>

          <q-card-section>
            <div class="text-subtitle1 text-weight-bolder text-teal-10 ellipsis">{{ alb.cim }}</div>
            <div class="text-caption text-grey-8 flex items-center">
              <q-icon name="location_on" color="red" class="q-mr-xs" /> {{ alb.varos }}
            </div>

            <q-separator class="q-my-md" />

            <div class="row justify-between text-grey-9">
              <div class="col-4 text-center">
                <q-icon name="meeting_room" size="18px" color="teal" />
                <div class="text-caption text-weight-bold">{{ alb.szobak_szama }} szoba</div>
              </div>
              <div class="col-4 text-center">
                <q-icon name="aspect_ratio" size="18px" color="teal" />
                <div class="text-caption text-weight-bold">{{ alb.meret }} m²</div>
              </div>
              <div class="col-4 text-center">
                <q-icon name="layers" size="18px" color="teal" />
                <div class="text-caption text-weight-bold">{{ alb.emelet != null ? alb.emelet + '. em' : 'Földszint' }}
                </div>
              </div>
            </div>
          </q-card-section>

          <q-card-actions align="right" class="q-pb-md q-px-md">
            <q-btn flat color="teal" label="Részletek" icon-right="chevron_right" :to="`/alberlet/${alb.id}`" />
          </q-card-actions>
        </q-card>
      </div>
    </div>

    <div v-else-if="!store.loading" class="text-center q-pa-xl bg-white rounded-borders shadow-1 q-mt-xl">
      <q-icon name="search_off" size="100px" color="grey-4" />
      <div class="text-h5 text-grey-6 q-mt-md">Nincs a szűrésnek megfelelő találat</div>
      <q-btn outline color="teal" label="Összes hirdetés mutatása" class="q-mt-lg" @click="store.resetFilters" />
    </div>

    <div class="flex flex-center q-py-xl">
      <q-pagination v-model="store.currentPage" :max="store.totalPages" :max-pages="6" boundary-numbers direction-links
        color="teal" active-color="teal-10" size="md" @update:model-value="(val) => store.fetchAlberletek(val)" />
    </div>

    <q-inner-loading :showing="store.loading">
      <q-spinner-cube color="teal" size="5em" />
      <div class="text-teal q-mt-md text-weight-bold">Adatok betöltése...</div>
    </q-inner-loading>




  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAlberletStore } from 'stores/alberletStore';

const store = useAlberletStore();
const showFilters = ref(false);

// Ez tárolja a legördülő menü aktuális (szűrt) tartalmát
const filteredVarosok = ref([]);

// A gépelés közbeni szűrés logikája
const filterFn = (val, update) => {
  update(() => {
    // Ha nincs beírt szöveg, az összes várost mutatjuk
    if (!val) {
      filteredVarosok.value = store.varosok;
    } else {
      const needle = val.toLowerCase();
      filteredVarosok.value = store.varosok.filter(
        v => v.label.toLowerCase().indexOf(needle) > -1
      );
    }
  });
};




// Opciók a választómezőkhöz (Backendhez igazítva)
const tipusOpciok = [
  { label: 'Összes típus', value: null },
  { label: 'Szoba', value: 2 },
  { label: 'Lakás', value: 1 },
  { label: 'Ház', value: 0 }
];

const igenNemOpciok = [
  { label: 'Mindegy', value: null },
  { label: 'Igen', value: 1 },
  { label: 'Nem', value: 0 }
];

const rendezesOpciok = [
  { label: 'Legújabb elöl', value: 'legujabb' },
  { label: 'Legolcsóbb elöl', value: 'ar_asc' },
  { label: 'Legdrágább elöl', value: 'ar_desc' }
];

onMounted(async () => {
  await store.fetchVarosok();
  filteredVarosok.value = store.varosok;
  store.fetchAlberletek();
});

</script>

<style scoped>
.property-card {
  border-radius: 16px;
  background: white;
  transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.property-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12) !important;
}

.rounded-borders {
  border-radius: 16px;
}

.shadow-sm {
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
}
</style>
