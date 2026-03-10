import { defineStore } from 'pinia';
import { api } from 'boot/axios';
import { Notify } from 'quasar';

export const useAlberletStore = defineStore('alberlet', {
  state: () => ({
    alberletek: [],
    selectedAlberlet: null,
    loading: false,
    totalPages: 1,
    currentPage: 1,
    filters: {
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
      sort: 'legujabb'
    }
  }),

  actions: {
    async fetchAlberletek(page = 1) {
      this.loading = true;
      try {
        const params = { page };
        Object.keys(this.filters).forEach(key => {
          if (this.filters[key] !== null && this.filters[key] !== '') {
            params[key] = this.filters[key];
          }
        });

        const response = await api.get('/alberletek', { params });
        this.alberletek = response.data.data;
        this.totalPages = response.data.meta.last_page;
        this.currentPage = response.data.meta.current_page;
      } catch (err) {
        console.error('Hiba:', err);
        Notify.create({ type: 'negative', message: 'Hiba a lista betöltésekor!' });
      } finally {
        this.loading = false;
      }
    },

    async fetchAlberletById(id) {
      this.loading = true;
      this.selectedAlberlet = null;
      try {
        const response = await api.get(`/alberletek/${id}`);
        this.selectedAlberlet = response.data.data;
        return this.selectedAlberlet;
      } catch (err) {
        console.error('Hiba az egyedi lekérésnél:', err);
        Notify.create({ type: 'negative', message: 'Nem találom ezt az albérletet!' });
        throw err;
      } finally {
        this.loading = false;
      }
    },

    resetFilters() {
      this.filters = {
        varos_id: null, min_szoba: null, max_szoba: null, min_ar: null,
        max_ar: null, min_meret: null, max_meret: null, butorozott: null,
        lift: null, tipus: null, sort: 'legujabb'
      };
      this.fetchAlberletek(1);
    }
  }
});