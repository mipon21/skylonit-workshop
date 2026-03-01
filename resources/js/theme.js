/**
 * Global theme: toggle, persist, and notify.
 * - Logged-in: persist to API (profile/theme) and localStorage.
 * - Guest: localStorage only.
 * - Set document.documentElement.dataset.theme for instant switch (no reload).
 *
 * Charts: listen for 'themechange' to update grid/label/tooltip/dataset colors:
 *   window.addEventListener('themechange', (e) => { const theme = e.detail?.theme; ... });
 */

const THEME_KEY = 'app_theme';
const THEME_ROUTE = '/profile/theme';

function getStoredTheme() {
  try {
    return localStorage.getItem(THEME_KEY);
  } catch (_) {
    return null;
  }
}

function setStoredTheme(theme) {
  try {
    if (theme) localStorage.setItem(THEME_KEY, theme);
    else localStorage.removeItem(THEME_KEY);
  } catch (_) {}
}

/**
 * Resolve initial theme: server preference (from PHP) > localStorage > 'light'.
 * @param {string|null} serverTheme - 'light' | 'dark' | null from backend
 * @returns {'light'|'dark'}
 */
export function getInitialTheme(serverTheme) {
  if (serverTheme === 'light' || serverTheme === 'dark') return serverTheme;
  const stored = getStoredTheme();
  if (stored === 'light' || stored === 'dark') return stored;
  return 'light';
}

/**
 * Apply theme to DOM (instant). Does not persist.
 * @param {'light'|'dark'} theme
 */
export function applyTheme(theme) {
  const html = document.documentElement;
  if (theme === 'light' || theme === 'dark') {
    html.dataset.theme = theme;
  } else {
    html.dataset.theme = 'light';
  }
  window.dispatchEvent(new CustomEvent('themechange', { detail: { theme: html.dataset.theme } }));
}

/**
 * Set theme and persist (localStorage + API if auth).
 * @param {'light'|'dark'} theme
 * @param {{ saveUrl?: string, csrf?: string }} options - saveUrl for PATCH, csrf token
 */
export function setTheme(theme, options = {}) {
  const resolved = theme === 'light' || theme === 'dark' ? theme : 'light';
  applyTheme(resolved);
  setStoredTheme(resolved);

  const { saveUrl, csrf } = options;
  if (saveUrl && csrf) {
    fetch(saveUrl, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
      body: JSON.stringify({ theme: resolved }),
    }).catch(() => {});
  }
}

/**
 * Toggle between light and dark.
 * @param {{ saveUrl?: string, csrf?: string }} options
 * @returns {'light'|'dark'} new theme
 */
export function toggleTheme(options = {}) {
  const current = document.documentElement.dataset.theme || 'light';
  const next = current === 'dark' ? 'light' : 'dark';
  setTheme(next, options);
  return next;
}

/**
 * Current theme from DOM.
 * @returns {'light'|'dark'}
 */
export function currentTheme() {
  const t = document.documentElement.dataset.theme;
  return t === 'light' || t === 'dark' ? t : 'light';
}

// Charts (and other JS) can subscribe to theme changes
window.addEventListener('themechange', (e) => {
  const theme = e.detail?.theme || 'light';
  document.body.classList.toggle('theme-dark', theme === 'dark');
  document.body.classList.toggle('theme-light', theme === 'light');
});
