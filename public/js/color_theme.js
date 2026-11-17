const themeButton = document.querySelector('.theme');
const root = document.documentElement;

if (!localStorage.getItem('theme')) {
    localStorage.setItem('theme', 'light');
}

const theme_palette = {
    light: {
        '--color-scheme': 'light',
        '--bg-color': '#f9f6f0',
        '--card-bg': '#ffffff',
        '--input-bg': '#ffffff',
        '--text-color': '#333333',
        '--text-light': '#555555',
        '--primary-color': '#e67e22',
        '--primary-hover': '#d67118',
        '--primary-text': '#ffffff',
        '--secondary-color': '#2c3e50',
        '--border-color': '#cccccc',
        '--footer-bg': '#2c3e50',
        '--footer-text': '#d1d8e0',
        '--footer-text-muted': '#a4b0be',
        '--error-bg': '#f8d7da',
        '--error-text': '#721c24',
        '--error-border': '#f5c6cb',
        '--success-bg': '#d4edda',
        '--success-text': '#155724',
        '--success-border': '#c3e6cb',
        '--disabled-bg': '#cccccc',
        '--disabled-text': '#666666'
    },
    dark: {
        '--color-scheme': 'dark',
        '--bg-color': '#121824',
        '--card-bg': '#1f2937',
        '--input-bg': '#161e2e',
        '--text-color': '#f3f4f6',
        '--text-light': '#9ca3af',
        '--primary-color': '#e67e22',
        '--primary-hover': '#f39c12',
        '--primary-text': '#ffffff',
        '--secondary-color': '#60a5fa',
        '--border-color': '#374151',
        '--footer-bg': '#0b0f17',
        '--footer-text': '#d1d8e0',
        '--footer-text-muted': '#6b7280',
        '--error-bg': '#3b181c',
        '--error-text': '#fca5a5',
        '--error-border': '#7f1d1d',
        '--success-bg': '#123524',
        '--success-text': '#86efac',
        '--success-border': '#14532d',
        '--disabled-bg': '#374151',
        '--disabled-text': '#9ca3af'
    }
};

function updateColors() {
    const currentTheme = localStorage.getItem('theme');
    const selectedColors = theme_palette[currentTheme];

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