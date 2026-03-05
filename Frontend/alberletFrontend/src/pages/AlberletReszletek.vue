<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const route = useRoute()
const $q = useQuasar()
const BASE_URL = 'http://127.0.0.1:8000'

const alberlet = ref(null)
const currentSlide = ref(0)
const loading = ref(true)
const error = ref(null)

const id = route.params.id

const fetchAlberlet = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await api.get(`/alberletek/${id}`)
    alberlet.value = response.data.data
  } catch (err) {
    console.error('Hiba:', err)
    error.value = 'Nem sikerült betölteni az adatokat.'
    $q.notify({
      type: 'negative',
      message: 'Hiba az adatok lekérésekor!',
      position: 'top'
    })
  } finally {
    loading.value = false
  }
}

onMounted(fetchAlberlet)
</script>

<template>
  <q-page padding class="bg-grey-1">
    <div class="max-width-container q-mx-auto">
      
      <div class="row items-center q-gutter-sm q-mb-md">
        <q-btn flat round color="primary" icon="arrow_back" @click="$router.back()" />
        <div class="text-h4 text-weight-bold text-grey-9">{{ alberlet?.cim || 'Betöltés...' }}</div>
      </div>

      <div v-if="loading" class="text-center q-pa-xl">
        <q-spinner-cube color="primary" size="4em" />
        <div class="text-h6 q-mt-md">Adatok lekérése...</div>
      </div>

      <q-banner v-else-if="error" class="bg-red-1 text-red-10 rounded-borders q-mb-md shadow-1">
        <template v-slot:avatar><q-icon name="error" /></template>
        {{ error }}
        <template v-slot:action>
          <q-btn flat label="Újra" @click="fetchAlberlet" />
        </template>
      </q-banner>

      <div v-else-if="alberlet" class="row q-col-gutter-lg">
        
        <div class="col-12 col-md-8">
          
          <q-card flat bordered class="rounded-borders overflow-hidden q-mb-lg shadow-2">
            <q-carousel
              v-model="currentSlide"
              v-if="alberlet.kepek?.length > 0"
              animated
              infinite
              arrows
              navigation
              thumbnails
              control-color="blue"
              height="500px"
              class="bg-black"
            >
              <q-carousel-slide
                v-for="(kepUrl, index) in alberlet.kepek"
                :key="index"
                :name="index"
                :img-src="`${BASE_URL}${kepUrl.startsWith('/') ? kepUrl : '/' + kepUrl}`"
              />
            </q-carousel>
            <div v-else class="flex flex-center bg-grey-3 text-grey-6" style="height: 400px">
              <div class="text-center">
                <q-icon name="hide_image" size="5rem" />
                <div class="text-h6">Nincs elérhető fotó</div>
              </div>
            </div>
          </q-card>

          <q-card flat bordered class="rounded-borders shadow-2">
            <q-card-section>
              <div class="text-h5 text-weight-bold q-mb-md">Leírás</div>
              <div class="text-body1 text-grey-8 line-height-relaxed">
                {{ alberlet.leiras }}
              </div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-md-4">
          <div class="sticky-column q-gutter-y-lg">
            
            <q-card flat class="bg-primary text-white shadow-3 rounded-borders">
              <q-card-section>
                <div class="text-h3 text-weight-bolder">
                  {{ alberlet.ar?.toLocaleString() }} <small class="text-h6">Ft/hó</small>
                </div>
                <q-separator dark class="q-my-md" />
                <div class="row q-col-gutter-sm text-center">
                  <div class="col-6">
                    <div class="text-caption opacity-80">Alapterület</div>
                    <div class="text-subtitle1 text-weight-bold">{{ alberlet.meret }} m²</div>
                  </div>
                  <div class="col-6">
                    <div class="text-caption opacity-80">Szobák</div>
                    <div class="text-subtitle1 text-weight-bold">{{ alberlet.szobak_szama }} szoba</div>
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <q-card flat bordered class="shadow-2 rounded-borders bg-white">
              <q-list separator>
                <q-item>
                  <q-item-section avatar><q-icon name="location_city" color="primary" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Város</q-item-label>
                    <q-item-label class="text-weight-medium">{{ alberlet.varos }}</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item>
                  <q-item-section avatar><q-icon name="apartment" color="primary" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Típus</q-item-label>
                    <q-item-label class="text-weight-medium">{{ alberlet.tipus }}</q-item-label>
                  </q-item-section>
                </q-item>
                <q-item>
                  <q-item-section avatar><q-icon name="elevator" color="primary" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Lift / Emelet</q-item-label>
                    <q-item-label class="text-weight-medium">
                      {{ alberlet.lift === 'Igen' ? 'Van lift' : 'Nincs lift' }} • {{ alberlet.emelet || 'Földszint' }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
                <q-item>
                  <q-item-section avatar><q-icon name="chair" color="primary" /></q-item-section>
                  <q-item-section>
                    <q-item-label caption>Bútorozott</q-item-label>
                    <q-item-label class="text-weight-medium">{{ alberlet.butorozott }}</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card>

            <q-card flat bordered class="shadow-2 rounded-borders bg-white">
              <q-card-section class="bg-grey-2">
                <div class="text-subtitle1 text-weight-bold text-center text-grey-9">Kapcsolatfelvétel</div>
              </q-card-section>
              <q-card-section class="text-center q-pa-lg">
                <q-avatar size="80px" font-size="50px" color="blue-1" text-color="primary" icon="account_circle" class="q-mb-md shadow-1" />
                <div class="text-h6 text-grey-9">{{ alberlet.tulajdonos?.nev || 'Magánszemély' }}</div>
                
                <div class="q-mt-lg q-gutter-y-sm">
                  <q-btn 
                    unelevated 
                    rounded 
                    color="primary" 
                    icon="phone" 
                    :label="alberlet.tulajdonos?.telefon || 'Nincs szám'" 
                    class="full-width q-py-sm text-weight-bold" 
                    :type="alberlet.tulajdonos?.telefon ? 'a' : 'button'"
                    :href="alberlet.tulajdonos?.telefon ? `tel:${alberlet.tulajdonos.telefon}` : undefined"
                  />
                  <q-btn 
                    outline 
                    rounded 
                    color="primary" 
                    icon="email" 
                    :label="alberlet.tulajdonos?.email || 'Nincs e-mail'" 
                    class="full-width q-py-sm" 
                    :type="alberlet.tulajdonos?.email ? 'a' : 'button'"
                    :href="alberlet.tulajdonos?.email ? `mailto:${alberlet.tulajdonos.email}` : undefined"
                  />
                </div>
              </q-card-section>
            </q-card>

          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<style scoped>
.max-width-container {
  max-width: 1200px;
}
.sticky-column {
  position: sticky;
  top: 24px;
}
.line-height-relaxed {
  line-height: 1.7;
  white-space: pre-wrap;
}
.rounded-borders {
  border-radius: 12px;
}
.opacity-80 {
  opacity: 0.8;
}
</style>