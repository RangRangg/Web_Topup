import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Theme manager (Light & Dark Mode)
const getInitialTheme = () => {
    const stored = localStorage.getItem('theme');
    if (stored === 'dark' || stored === 'light') {
        return stored;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'dark';
};

window.setTheme = function (theme) {
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
    if (window.Alpine?.store('theme')) {
        window.Alpine.store('theme').current = theme;
        window.Alpine.store('theme').isDark = (theme === 'dark');
    }
};

window.toggleTheme = function () {
    const isDark = document.documentElement.classList.contains('dark');
    window.setTheme(isDark ? 'light' : 'dark');
};

// Alpine Store for reactive theme states in Blade templates
Alpine.store('theme', {
    current: getInitialTheme(),
    isDark: document.documentElement.classList.contains('dark'),
    toggle() {
        window.toggleTheme();
    }
});

// Sync system theme changes if user hasn't explicitly set localStorage
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
        window.setTheme(e.matches ? 'dark' : 'light');
    }
});

// Global Toast dispatch helper
window.showToast = function (message, type = 'success') {
    window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }));
};

// Clipboard copy helper
window.copyToClipboard = function (text, message = 'Berhasil disalin ke clipboard!') {
    navigator.clipboard.writeText(text).then(() => {
        window.showToast(message, 'info');
    });
};

Alpine.start();
