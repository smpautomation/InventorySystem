import { ref } from 'vue'

// ── Types ──────────────────────────────────────────────────────
interface AreaData {
    [area: string]: number[]
}

interface PlantOutputResponse {
    plant: string
    year:  number
    month: number
    data:  AreaData
}

interface CacheEntry {
    timestamp: number
    payload:   PlantOutputResponse
}

interface MonthTarget {
    target:       number
    working_days: number
}

// ── Constants ──────────────────────────────────────────────────
const CACHE_PREFIX:   string = 'plant_output:'
const TTL_CURRENT_MS: number = 60 * 60 * 1000 // 1 hour
const TTL_PAST_MS:    number = 7 * 24 * 60 * 60 * 1000  // 1 week

// ── Helpers ────────────────────────────────────────────────────
function getCacheKey(plant: string, year: number, month: number): string {
    return `${CACHE_PREFIX}${plant}:${year}:${month}`
}

function getTargetCacheKey(plant: string, year: number): string {
    return `plant_targets:${plant}:${year}` //
}

function isCurrent(year: number, month: number): boolean {
    const now = new Date()
    return year === now.getFullYear() && month === now.getMonth() + 1
}

function readCache<T>(key: string, ttlMs: number): T | null {
    try {
        const raw = localStorage.getItem(key)
        if (!raw) return null

        const { timestamp, payload } = JSON.parse(raw)

        if (Date.now() - timestamp > ttlMs) {
            localStorage.removeItem(key)
            return null
        }

        return payload as T
    } catch {
        return null
    }
}

function writeCache<T>(key: string, payload: T): void {
    try {
        localStorage.setItem(key, JSON.stringify({ timestamp: Date.now(), payload }))
    } catch {
        // localStorage full — skip
    }
}

export function usePlantOutput() {
    const loading = ref<boolean>(false)
    const error   = ref<string | null>(null)

    async function fetchMonth(
        plant: string,
        year:  number,
        month: number
    ): Promise<PlantOutputResponse | null> {
        const key   = getCacheKey(plant, year, month)
        const ttlMs = isCurrent(year, month) ? TTL_CURRENT_MS : TTL_PAST_MS

        const cached = readCache<PlantOutputResponse>(key, ttlMs)
        if (cached) return cached

        loading.value = true
        error.value   = null

        try {
            const res = await fetch(`/api/plant-output/${plant}?year=${year}&month=${month}`)
            if (!res.ok) throw new Error(`HTTP ${res.status}`)

            const json: PlantOutputResponse = await res.json()
            writeCache(key, json)
            return json
        } catch (e) {
            console.error('fetch error:', e)
            error.value = e instanceof Error ? e.message : 'Unknown error'
            return null
        } finally {
            loading.value = false
        }
    }

    async function fetchYear(
        plant: string,
        year:  number
    ): Promise<(PlantOutputResponse | null)[]> {
        const now        = new Date()
        const monthCount = year === now.getFullYear() ? now.getMonth() + 1 : 12
        const requests   = Array.from({ length: monthCount }, (_, i) =>
            fetchMonth(plant, year, i + 1)
        )
        return Promise.all(requests)
    }

    async function fetchTargets(
        plant: string,
        year:  number = new Date().getFullYear()
    ): Promise<Record<string, MonthTarget>> {
        const key       = getTargetCacheKey(plant, year)
        const TTL_24HRS = 24 * 60 * 60 * 1000

        const cached = readCache<Record<string, MonthTarget>>(key, TTL_24HRS)
        if (cached) return cached

        try {
            const res = await fetch(`/api/plant-target/${plant}?year=${year}`)
            if (!res.ok) throw new Error(`HTTP ${res.status}`)

            const data: Record<string, MonthTarget> = await res.json()
            writeCache(key, data)
            return data
        } catch (e) {
            console.error('fetchTargets error:', e)
            return {}
        }
    }

    function invalidate(plant: string, year: number, month: number): void {
        localStorage.removeItem(getCacheKey(plant, year, month))
    }

    function invalidateTargets(plant: string, year: number): void {
        localStorage.removeItem(getTargetCacheKey(plant, year))
    }

    return { fetchMonth, fetchYear, fetchTargets, invalidate, invalidateTargets, loading, error }
}
