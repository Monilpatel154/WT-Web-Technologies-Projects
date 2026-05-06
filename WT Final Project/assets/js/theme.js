// SkillSwap theme + shared navigation behavior

document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    if (navToggle && navMenu) {
        const syncNavState = () => {
            const isOpen = navMenu.classList.contains('open');
            navToggle.setAttribute('aria-expanded', String(isOpen));
        };

        navToggle.addEventListener('click', (event) => {
            event.stopPropagation();
            navMenu.classList.toggle('open');
            syncNavState();
        });

        document.addEventListener('click', (event) => {
            if (!navToggle.contains(event.target) && !navMenu.contains(event.target)) {
                navMenu.classList.remove('open');
                syncNavState();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                navMenu.classList.remove('open');
                syncNavState();
            }
        });

        syncNavState();
    }

    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    if (userMenuBtn && userDropdown) {
        const syncUserState = () => {
            const isOpen = userDropdown.classList.contains('open');
            userMenuBtn.setAttribute('aria-expanded', String(isOpen));
        };

        userMenuBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            userDropdown.classList.toggle('open');
            syncUserState();
        });

        document.addEventListener('click', () => {
            userDropdown.classList.remove('open');
            syncUserState();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                userDropdown.classList.remove('open');
                syncUserState();
            }
        });

        syncUserState();
    }

    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;

    const themeIcon = themeToggle.querySelector('.theme-toggle-icon');
    const themeText = themeToggle.querySelector('.theme-toggle-text');

    const syncThemeButton = () => {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        const isLight = currentTheme === 'light';
        themeToggle.setAttribute('aria-pressed', String(isLight));
        if (themeIcon) themeIcon.textContent = isLight ? '◐' : '◑';
        if (themeText) themeText.textContent = isLight ? 'Light' : 'Dark';
    };

    syncThemeButton();

    themeToggle.addEventListener('click', () => {
        const nextTheme = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', nextTheme);
        document.cookie = `skillswap_theme=${nextTheme}; path=/; max-age=31536000; SameSite=Lax`;
        syncThemeButton();
    });
});