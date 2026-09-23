export interface HMVData {
    id: string | null
    name: string
    complex: string
    building: string
    standing: string
    type: string
    amount: number
    heating: string
    regulation: string
    circulation: boolean
    containment: boolean
}

export interface HMVFormErrors {
    name: string
    complex: string
    building: string
    standing: string
    amount: string
    heating: string
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
