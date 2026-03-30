import { ref, watch } from 'vue'

type Theme = 'dark' | 'light'

const STORAGE_KEY = 'app-theme'

const stored = localStorage.getItem(STORAGE_KEY) as Theme | null
const theme  = ref<Theme>(stored ?? 'dark')

function applyTheme(value: Theme): void {
    document.documentElement.setAttribute('data-theme', value)
}

// Apply on init
applyTheme(theme.value)

// Persist and apply on change
watch(theme, (val: Theme) => {
    localStorage.setItem(STORAGE_KEY, val)
    applyTheme(val)
})

export function useTheme() {
    function toggleTheme(): void {
        theme.value = theme.value === 'dark' ? 'light' : 'dark'
    }

    return { theme, toggleTheme }
}
