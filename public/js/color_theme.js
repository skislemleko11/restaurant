const themeButton = document.querySelector('.theme');
const root = document.documentElement;

if (!localStorage.getItem('theme')) {
    localStorage.setItem('theme', 'light');
}

const theme_pallette = {
    light: {
        '--bg-color': '#f9f6f0',
        '--text-color': '#333333',
        '--text-light': '#555555',
        '--primary-color': '#e67e22',
        '--secondary-color': '#2c3e50',
        '--white': '#ffffff',
        '--footer-text': '#d1d8e0',
        '--footer-text-muted': '#a4b0be',
        '--error-bg': '#f8d7da',
        '--error-text': '#721c24',
        '--success-bg': '#d4edda',
        '--success-text': '#155724'
    },
    dark: {
        '--bg-color': '#121824',
        '--text-color': '#f3f4f6',
        '--text-light': '#9ca3af',
        '--primary-color': '#f39c12',
        '--secondary-color': '#60a5fa',
        '--white': '#1f2937',
        '--footer-text': '#e5e7eb',
        '--footer-text-muted': '#9ca3af',
        '--error-bg': '#7f1d1d',
        '--error-text': '#fecaca',
        '--success-bg': '#064e3b',
        '--success-text': '#a7f3d0'
    }
}

function updateColors() {
    const currentTheme = localStorage.getItem('theme');
    const selectedColors = theme_pallette[currentTheme];

    for ([variable, value] of Object.entries(selectedColors)) {
        root.style.setProperty(variable, value);
    }
}

updateColors();

themeButton.addEventListener('click', () => {
    let newTheme = localStorage.getItem('theme') === 'light' ? 'dark' : 'light';
    localStorage.setItem('theme', newTheme);
    updateColors();
});