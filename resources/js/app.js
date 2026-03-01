import './bootstrap';
import * as theme from './theme';

// Expose theme for navbar toggle and chart observer
window.theme = theme;
// Sync body class and fire themechange once so charts/observers can run
theme.applyTheme(theme.currentTheme());

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('clientNotificationPopups', function (config) {
  return {
    list: [],
    current: null,
    autoDismissTimer: null,
    config,

    init() {
      this.list = Array.isArray(this.config.initialList) ? this.config.initialList : [];
      this.showNext();
      this.fetchUnread();
    },

    async fetchUnread() {
      try {
        const res = await fetch(this.config.unreadUrl, {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });
        const data = await res.json();
        const fromApi = data.notifications || [];
        if (fromApi.length > 0) {
          this.list = fromApi;
          if (!this.current) this.showNext();
        }
      } catch (_) {
        // keep existing list from initialList
      }
    },

    showNext() {
      if (this.autoDismissTimer) {
        clearTimeout(this.autoDismissTimer);
        this.autoDismissTimer = null;
      }
      this.current = this.list.length ? this.list[0] : null;
      if (this.current && this.current.type === 'normal') {
        this.autoDismissTimer = setTimeout(() => this.dismissCurrent(), 6000);
      }
    },

    markReadUrl(id) {
      const pattern = this.config.markReadUrlPattern || '';
      return typeof pattern === 'string' ? pattern.replace('__ID__', id) : '';
    },

    async dismissCurrent() {
      if (this.autoDismissTimer) {
        clearTimeout(this.autoDismissTimer);
        this.autoDismissTimer = null;
      }
      const id = this.current?.id;
      if (id) {
        try {
          await fetch(this.markReadUrl(id), {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': this.config.csrf,
              'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
          });
        } catch (_) {}
      }
      this.list = this.list.filter((n) => n.id !== id);
      this.current = null;
      this.showNext();
    },
  };
});

Alpine.data('headerClock', function () {
  return {
    time: '--:--:--',
    ampm: '',
    date: '',
    init() {
      const pad = (n) => String(n).padStart(2, '0');
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      const update = () => {
        const d = new Date();
        const h = d.getHours();
        const m = d.getMinutes();
        const s = d.getSeconds();
        const h12 = h % 12 || 12;
        this.time = pad(h12) + ':' + pad(m) + ':' + pad(s);
        this.ampm = h >= 12 ? 'PM' : 'AM';
        this.date = months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
      };
      update();
      setInterval(update, 1000);
    },
  };
});

// Start Alpine after DOM is ready so client notification popup container exists
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => Alpine.start());
} else {
  Alpine.start();
}
