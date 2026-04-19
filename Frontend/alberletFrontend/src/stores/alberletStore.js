import { defineStore } from "pinia";
import { api } from "boot/axios";
import { Notify } from "quasar";

const BASE_URL = "http://127.0.0.1:8000";

export const useAlberletStore = defineStore("alberlet", {
  state: () => ({
    varosok: [],
    megyek: [],
    alberletek: [],
    selectedAlberlet: null,
    loading: false,
    totalPages: 1,
    currentPage: 1,
    filters: {
      megye_id: null,
      varos_id: null,
      min_szoba: null,
      max_szoba: null,
      min_ar: null,
      max_ar: null,
      min_meret: null,
      max_meret: null,
      butorozott: null,
      lift: null,
      tipus: null,
      sort: "legujabb",
    },
  }),

  getters: {
    formatImageUrl: () => (kepek) => {
      if (!kepek || (Array.isArray(kepek) && kepek.length === 0))
        return "https://placehold.co/600x400?text=Nincs+kep";

      const rawPath = Array.isArray(kepek)
        ? kepek[0]?.kep_url || kepek[0]
        : kepek?.kep_url || kepek;

      if (!rawPath) return "https://placehold.co/600x400?text=Nincs+kep";
      const cleanPath = rawPath.startsWith("/") ? rawPath : `/${rawPath}`;
      return `${BASE_URL}${cleanPath}`;
    },

    getCleanFilters(state) {
      const activeFilters = {};
      Object.keys(state.filters).forEach((key) => {
        if (state.filters[key] !== null && state.filters[key] !== "") {
          activeFilters[key] = state.filters[key];
        }
      });
      return activeFilters;
    },
  },

  actions: {
   async fetchMegyek(isFiltered = false) {
  try {
    // Ha isFiltered true, akkor hozzáfüzzük a ?filtered=true részt
    const url = isFiltered ? '/megyek?filtered=true' : '/megyek';
    const response = await api.get(url);
    this.megyek = response.data;
    return response.data;
  } catch (error) {
    console.error("Hiba a megyék lekérésekor:", error);
    this.megyek = [];
  }
},

    async fetchVarosok(isFiltered = false) {
  try {
    const url = isFiltered ? '/varosok?filtered=true' : '/varosok';
    const { data } = await api.get(url);
    this.varosok = data;
  } catch (error) {
    console.error("Nem sikerült betölteni a városokat", error);
  }
},

    async fetchAlberletek(page = 1, adminView = false) {
      this.loading = true;
      try {
        const params = { 
          page, 
          ...this.getCleanFilters, 
          admin_view: adminView ? 'true' : 'false' 
        };
        const { data } = await api.get("/alberletek", { params });

        // Az adatok elmentése
        this.alberletek = data.data;

        // LAPOZÁS JAVÍTÁSA:
        // A Laravel Resource meta részéből kimentjük az utolsó oldal számát
        if (data.meta) {
          this.totalPages = data.meta.last_page;
          this.currentPage = data.meta.current_page;
        }
      } catch (error) {
        console.error("Hiba:", error);
      } finally {
        this.loading = false;
      }
    },

  // ÚJ: Állapot módosítása (Aktiválás / Kikapcsolás)
 async updateStatus(id, status) {
  try {
    const response = await api.put(`/alberletek/${id}`, { aktiv: status });
    // Ha a válasz 200 (OK), akkor sikeres
    if (response.status === 200) {
      const index = this.alberletek.findIndex(a => a.id === id);
      if (index !== -1) {
        this.alberletek[index].aktiv = status;
      }
      return true;
    }
  } catch (error) {
    console.error("Hiba a státusz frissítésekor:", error);
    return false;
  }
},

  // ÚJ: Hirdetés végleges törlése
  async deleteAlberlet(id) {
  // EZT A SORT TÖRÖLD KI: if (!confirm(...)) return;
  
  try {
    await api.delete(`/alberletek/${id}`);
    // Kiszűrjük a helyi listából is, hogy azonnal eltűnjön
    this.alberletek = this.alberletek.filter(a => a.id !== id);
    return true; // Adjunk vissza true-t, hogy a komponens tudja: sikerült!
  } catch (error) {
    console.error("Hiba a törléskor:", error);
    return false;
  }
},

  async fetchAllForAdmin() {
  try {
    // Hozzáadjuk a paramétert az URL-hez
    const { data } = await api.get('/alberletek?admin_view=true');
    
    // Mivel a Laravel Resource-t használsz, az adatok a data.data-ban lesznek
    this.alberletek = data.data; 
    return this.alberletek;
  } catch (error) {
    console.error('Hiba az admin adatok lekérésekor:', error);
  }
},


    async fetchAlberletById(id) {
      this.loading = true;
      this.selectedAlberlet = null;
      try {
        const { data } = await api.get(`/alberletek/${id}`);
        this.selectedAlberlet = data.data;
      } catch {
        this.handleError("Nem található az ingatlan!");
      } finally {
        this.loading = false;
      }
    },

    resetFilters() {
      this.filters = {
        megye_id: null,
        varos_id: null,
        min_szoba: null,
        max_szoba: null,
        min_ar: null,
        max_ar: null,
        min_meret: null,
        max_meret: null,
        butorozott: null,
        lift: null,
        tipus: null,
        sort: "legujabb",
      };
      this.fetchAlberletek(1);
    },

    handleError(msg) {
      Notify.create({ type: "negative", message: msg, position: "top" });
    },
  },
});
