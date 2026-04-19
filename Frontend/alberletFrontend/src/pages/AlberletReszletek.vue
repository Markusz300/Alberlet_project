<template>
  <q-page padding class="bg-grey-1">
    <div class="max-width-container q-mx-auto">

      <div class="row items-center q-gutter-sm q-mb-md">
        <q-btn flat round color="teal" icon="arrow_back" @click="goBack" class="shadow-1 bg-white" />
        <div class="text-h4 text-weight-bold text-grey-9">
          {{ store.selectedAlberlet?.cim || (store.loading ? 'Betöltés...' : 'Részletek') }}
        </div>
      </div>

      <div v-if="store.loading" class="text-center q-pa-xl">
        <q-spinner-cube color="teal" size="4em" />
        <div class="text-h6 q-mt-md text-teal">Adatok szinkronizálása...</div>
      </div>

      <div v-else-if="store.selectedAlberlet" class="row q-col-gutter-lg">

        <div class="col-12 col-md-8">
          <q-card flat bordered class="rounded-borders overflow-hidden q-mb-lg shadow-2">
            <q-carousel v-model="currentSlide"
              v-if="store.selectedAlberlet.kepek && store.selectedAlberlet.kepek.length > 0" animated infinite arrows
              navigation thumbnails control-color="white" height="500px" class="bg-black">
              <q-carousel-slide v-for="(kep, index) in store.selectedAlberlet.kepek" :key="index" :name="index"
                :img-src="formatImageUrl(kep)" />
            </q-carousel>
            <div v-else class="flex flex-center bg-grey-3 text-grey-6" style="height: 400px">
              <div class="text-center">
                <q-icon name="hide_image" size="5rem" />
                <div class="text-h6">Nincs elérhető fotó az ingatlanról</div>
              </div>
            </div>
          </q-card>

          <q-card flat bordered class="rounded-borders shadow-2 bg-white">
            <q-card-section>
              <div class="text-h5 text-weight-bold q-mb-md text-teal-10">
                <q-icon name="description" color="teal" class="q-mr-sm" />Ingatlan leírása
              </div>
              <q-separator class="q-mb-md" />
              <div class="text-body1 text-grey-8 line-height-relaxed">
                {{ store.selectedAlberlet.leiras }}
              </div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-md-4">
          <div class="sticky-column q-gutter-y-lg">

            <q-card flat class="bg-teal text-white shadow-3 rounded-borders">
              <q-card-section class="text-center">
                <div class="text-h6 opacity-80 text-uppercase">Havi bérleti díj</div>
                <div class="text-h3 text-weight-bolder">
                  {{ store.selectedAlberlet.ar?.toLocaleString() }} <small class="text-h6">Ft</small>
                </div>
              </q-card-section>
            </q-card>

            <q-card flat bordered class="shadow-2 rounded-borders bg-white">
              <q-list separator>

                <q-item>
                  <q-item-section avatar><q-icon name="location_city" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Település / Megye</q-item-label>
                    <q-item-label class="text-weight-medium">
                      {{ store.selectedAlberlet.varos }}
                      <span v-if="store.selectedAlberlet.megye" class="text-grey-7 text-weight-regular">
                        , {{ store.selectedAlberlet.megye }} 
                      </span>
                    </q-item-label>
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="bed" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Szobák száma</q-item-label>
                    <q-item-label class="text-weight-medium">
                      {{ store.selectedAlberlet.szobak_szama }} szoba
                    </q-item-label>
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="straighten" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Alapterület</q-item-label>
                    <q-item-label class="text-weight-medium">{{ store.selectedAlberlet.meret }} m²</q-item-label>
                  </q-item-section>
                </q-item>

                <q-item>
  <q-item-section avatar>
    <q-icon :name="isHaz ? 'home' : 'elevator'" color="teal" />
  </q-item-section>

  <q-item-section>
    <q-item-label caption>
      {{ isHaz ? 'Ingatlan szintjei' : 'Emelet / Lift' }}
    </q-item-label>
    
    <q-item-label class="text-weight-medium">
      <div class="row items-center q-gutter-x-sm">
        
        <div>
          {{ isHaz 
            ? (store.selectedAlberlet.emelet > 1 ? store.selectedAlberlet.emelet + ' szintes ház' : 'Földszintes') 
            : (store.selectedAlberlet.emelet > 0 ? store.selectedAlberlet.emelet + '. emelet' : 'Földszint') 
          }}
        </div>

        <div class="text-grey-4" v-if="isHaz ? store.selectedAlberlet.emelet > 1 : true">•</div>

        <div class="row items-center" :class="isLiftes ? 'text-teal text-weight-bold' : 'text-grey-7'">
          <q-icon :name="isLiftes ? 'elevator' : 'block'" size="18px" class="q-mr-xs" />
          {{ isLiftes ? 'Van lift' : 'Nincs lift' }}
        </div>

      </div>
    </q-item-label>
  </q-item-section>
</q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="chair" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Bútorozottság</q-item-label>
                    <q-item-label class="text-weight-medium">
                      {{ store.selectedAlberlet.butorozott === 'igen' ? 'Bútorozott' : 'Üres' }}
                    </q-item-label>
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section avatar><q-icon name="event" color="teal" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Hirdetés feladva</q-item-label>
                    <q-item-label class="text-weight-medium">
                      {{ formatDate(store.selectedAlberlet.hirdetes_datuma) }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card>

            <q-card flat bordered class="shadow-2 rounded-borders bg-white">
              <q-card-section class="bg-grey-2">
                <div class="text-subtitle1 text-weight-bold text-center text-grey-9">Kapcsolatfelvétel</div>
              </q-card-section>
              <q-card-section class="text-center q-pa-lg">
                <q-avatar size="80px" font-size="50px" color="teal-1" text-color="teal" icon="account_circle"
                  class="q-mb-md shadow-1" />
                <div class="text-h6 text-weight-bold text-grey-9 q-mb-none">
                  {{ store.selectedAlberlet.tulajdonos?.nev || 'Hirdető' }}
                </div>
                <div class="text-caption text-grey-6 q-mb-md">Magánszemély</div>
                <div class="q-gutter-y-sm">
                  <q-btn unelevated rounded color="teal" icon="phone"
                    :label="store.selectedAlberlet.tulajdonos?.telefon || 'Nincs telefonszám'"
                    class="full-width q-py-sm text-weight-bold"
                    :type="store.selectedAlberlet.tulajdonos?.telefon ? 'a' : 'button'"
                    :href="store.selectedAlberlet.tulajdonos?.telefon ? `tel:${store.selectedAlberlet.tulajdonos.telefon}` : undefined" />
                  <q-btn outline rounded color="teal" icon="email"
                    :label="store.selectedAlberlet.tulajdonos?.email || 'Nincs e-mail'" class="full-width q-py-sm"
                    :type="store.selectedAlberlet.tulajdonos?.email ? 'a' : 'button'"
                    :href="store.selectedAlberlet.tulajdonos?.email ? `mailto:${store.selectedAlberlet.tulajdonos.email}` : undefined" />
                </div>
              </q-card-section>
            </q-card>

          </div>
        </div>
      </div>

      <div v-else class="text-center q-pa-xl">
        <q-icon name="error_outline" size="5em" color="grey-4" />
        <div class="text-h5 text-grey-6 q-mt-md">Hoppá! Nem találjuk ezt a hirdetést.</div>
        <q-btn outline color="teal" label="Vissza a keresőhöz" icon="home" class="q-mt-lg" @click="router.push('/')" />
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAlberletStore } from 'stores/alberletStore'

const route = useRoute()
const router = useRouter()
const store = useAlberletStore()
const BASE_URL = 'http://127.0.0.1:8000'

const currentSlide = ref(0)

// Számított tulajdonságok a tisztább sablonért
const isHaz = computed(() => store.selectedAlberlet?.tipus?.toLowerCase() === 'ház')
const isLiftes = computed(() => {
  const lift = store.selectedAlberlet?.lift
  return lift === 'van' || lift === 1 || lift === true
})

const goBack = () => {
  if (window.history.length > 1) router.back()
  else router.push('/')
}

const formatImageUrl = (kep) => {
  const path = kep.kep_url || kep
  if (!path) return 'https://placehold.co/600x400?text=Nincs+Kep'
  return `${BASE_URL}${path.startsWith('/') ? path : '/' + path}`
}

const loadData = async () => {
  const id = route.params.id
  if (id) await store.fetchAlberletById(id)
}

onMounted(loadData)

watch(() => route.params.id, (newId) => {
  if (newId) loadData()
})

const formatDate = (dateString) => {
  if (!dateString) return 'Nincs adat'
  return new Date(dateString).toLocaleDateString('hu-HU', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
}
</script>

<style scoped>

:deep(.q-carousel__control .q-btn) {
  text-shadow: 0px 0px 5px rgba(0, 0, 0, 0.8);
}

.max-width-container {
  max-width: 1200px;
}

.sticky-column {
  position: sticky;
  top: 24px;
}

.line-height-relaxed {
  line-height: 1.8;
  white-space: pre-wrap;
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
