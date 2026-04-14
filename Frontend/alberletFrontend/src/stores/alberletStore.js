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
    async fetchMegyek() {
      try {
        const { data } = await api.get('/megyek');
        this.megyek = data.map(m => ({ label: m.nev, value: m.id }));
      } catch {
        console.error("Hiba a megyék betöltésekor");
      }
    },

    async fetchVarosok() {
      try {
        const { data } = await api.get('/varosok');
        this.varosok = data;
      } catch {
        console.error("Nem sikerült betölteni a városokat");
      }
    },

    async fetchAlberletek(page = 1) {
      this.loading = true;
      try {
        const params = { page, ...this.getCleanFilters };
        const { data } = await api.get("/alberletek", { params });

        this.alberletek = data.data || [];
        this.totalPages = data.meta?.last_page || 1;
        this.currentPage = data.meta?.current_page || 1;
      } catch {
        this.handleError("Hiba a lista betöltésekor!");
      } finally {
        this.loading = false;
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