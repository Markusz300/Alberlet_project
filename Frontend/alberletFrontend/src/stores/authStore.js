// src/stores/authStore.js
import { defineStore } from "pinia";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    isLoggedIn: localStorage.getItem("isAdmin") === "true",
    user: null,
  }),
  actions: {
    login(user, pass) {
      // Ide írd be a te belépési adataidat (vagy amit használsz)
      if (user === "admin" && pass === "adminpass") {
        this.isLoggedIn = true;

        // EZ A KULCS: Elmentjük a böngészőbe is!
        localStorage.setItem("isAdmin", "true");

        return true;
      }
      return false;
    },
    logout() {
      this.isLoggedIn = false;
      // Kijelentkezéskor töröljük!
      localStorage.removeItem("isAdmin");
    },
  },
});
