import { reactive, watch } from 'vue';

// Check if there's a saved theme in localStorage
const savedTheme = localStorage.getItem('mango-theme') || 'light';

export const themeState = reactive({
  isDark: savedTheme === 'dark',
  toggleTheme() {
    this.isDark = !this.isDark;
    localStorage.setItem('mango-theme', this.isDark ? 'dark' : 'light');
    this.updateBodyClass();
  },
  updateBodyClass() {
    if (this.isDark) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }
});

// Initialize
themeState.updateBodyClass();
