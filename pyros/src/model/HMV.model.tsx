export interface HMVData {
    id: string | null
    name: string
    complex: string
    zoneName: string
    zoneUsage: string
    servicedSize: number
    standing: string
    type: string
    amount: number
    regulation: string
    circulation: boolean
    containment: boolean
}

export interface HMVFormErrors {
    name: string
    complex: string
    zoneName: string
    standing: string
    amount: string
    servicedSize: string
}

export const HMVTypes = [
    'Elektromos bojler',
    'Hőszivattyús',
    'Közvetlen gáztüzelésű berendezés',
    'Fűtőművi távfűtés',
]

export const CirculationTypes = [
    'Nincs',
    'Hőmérsékletre',
    'Időprogramra',
    'Hőmérsékletre és időprogramra',
]

export const ZoneUsages = [
    'Iroda',
    'Lakóépület',
    'Kereskedelmi',
    'Oktatási',
    'Üzem',
    'Raktár',
]
