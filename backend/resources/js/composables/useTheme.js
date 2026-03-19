/**
 * Theme system - maps a theme key to HSL CSS variable overrides.
 * Each theme defines: primary, primary-foreground, ring (and dark variants).
 */

const themes = {
    indigo: {
        label: 'Indigo',
        color: '#6366f1',
        light: { primary: '243 75% 59%', ring: '243 75% 59%' },
        dark: { primary: '243 75% 59%', ring: '243 75% 59%' },
    },
    blue: {
        label: 'Biru',
        color: '#3b82f6',
        light: { primary: '217 91% 60%', ring: '217 91% 60%' },
        dark: { primary: '217 91% 60%', ring: '217 91% 60%' },
    },
    sky: {
        label: 'Langit',
        color: '#0ea5e9',
        light: { primary: '199 89% 48%', ring: '199 89% 48%' },
        dark: { primary: '199 89% 48%', ring: '199 89% 48%' },
    },
    teal: {
        label: 'Teal',
        color: '#14b8a6',
        light: { primary: '173 80% 40%', ring: '173 80% 40%' },
        dark: { primary: '173 80% 40%', ring: '173 80% 40%' },
    },
    emerald: {
        label: 'Hijau',
        color: '#10b981',
        light: { primary: '160 84% 39%', ring: '160 84% 39%' },
        dark: { primary: '160 84% 39%', ring: '160 84% 39%' },
    },
    green: {
        label: 'Green',
        color: '#22c55e',
        light: { primary: '142 71% 45%', ring: '142 71% 45%' },
        dark: { primary: '142 71% 45%', ring: '142 71% 45%' },
    },
    amber: {
        label: 'Amber',
        color: '#f59e0b',
        light: { primary: '38 92% 50%', ring: '38 92% 50%', 'primary-foreground': '0 0% 100%' },
        dark: { primary: '38 92% 50%', ring: '38 92% 50%' },
    },
    orange: {
        label: 'Oranye',
        color: '#f97316',
        light: { primary: '25 95% 53%', ring: '25 95% 53%', 'primary-foreground': '0 0% 100%' },
        dark: { primary: '25 95% 53%', ring: '25 95% 53%' },
    },
    red: {
        label: 'Merah',
        color: '#ef4444',
        light: { primary: '0 84% 60%', ring: '0 84% 60%' },
        dark: { primary: '0 84% 60%', ring: '0 84% 60%' },
    },
    rose: {
        label: 'Rose',
        color: '#f43f5e',
        light: { primary: '347 77% 50%', ring: '347 77% 50%' },
        dark: { primary: '347 77% 50%', ring: '347 77% 50%' },
    },
    pink: {
        label: 'Pink',
        color: '#ec4899',
        light: { primary: '330 81% 60%', ring: '330 81% 60%' },
        dark: { primary: '330 81% 60%', ring: '330 81% 60%' },
    },
    purple: {
        label: 'Ungu',
        color: '#a855f7',
        light: { primary: '271 91% 65%', ring: '271 91% 65%' },
        dark: { primary: '271 91% 65%', ring: '271 91% 65%' },
    },
    violet: {
        label: 'Violet',
        color: '#8b5cf6',
        light: { primary: '258 90% 66%', ring: '258 90% 66%' },
        dark: { primary: '258 90% 66%', ring: '258 90% 66%' },
    },
    slate: {
        label: 'Abu-abu',
        color: '#64748b',
        light: { primary: '215 16% 47%', ring: '215 16% 47%' },
        dark: { primary: '215 20% 65%', ring: '215 20% 65%' },
    },
    zinc: {
        label: 'Zinc',
        color: '#71717a',
        light: { primary: '240 4% 46%', ring: '240 4% 46%' },
        dark: { primary: '240 5% 65%', ring: '240 5% 65%' },
    },
};

export function getThemes() {
    return themes;
}

export function applyTheme(themeKey) {
    const theme = themes[themeKey];
    if (!theme) return;

    const root = document.documentElement;

    // Apply light mode variables
    Object.entries(theme.light).forEach(([prop, val]) => {
        root.style.setProperty(`--${prop}`, val);
    });

    // For dark mode we need a different approach: inject/update a <style> tag
    let darkStyle = document.getElementById('theme-dark-overrides');
    if (!darkStyle) {
        darkStyle = document.createElement('style');
        darkStyle.id = 'theme-dark-overrides';
        document.head.appendChild(darkStyle);
    }

    const darkVars = Object.entries(theme.dark)
        .map(([prop, val]) => `--${prop}: ${val};`)
        .join('\n            ');

    darkStyle.textContent = `.dark { ${darkVars} }`;
}
