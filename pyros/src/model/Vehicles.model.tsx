export interface VehicleFormData {
    id: string | null
    complex: string | null
    name: string
    category: string
    motorSize: number
    hibrid: boolean
    fuel: string
    usageMetric: string
    usageValue: number
    usageValue2: number
    subStanding: string | null
}

export interface VehicleErrors {
    complex: string
    name: string
    usageValue: string
    subStanding: string
    motorSize: string
    usageValue2: string
}

export const VehicleCategories: Array<string> = [
    'Személygépjármű',
    'Áruszállítás 1t-ig',
    'Áruszállítás 1t felett',
    'Személyszállítás 9 főig',
    'Személyszállítás 9 fő felett',
    'Anyagmozgató',
]

export const VehicleFuelCategories: Array<string> = [
    'Benzin',
    'Gázolaj',
    'PB Gáz',
    'Propán',
    'Eletromos áram',
    'LPG',
]

export const VehicleUsageMetricCategories: Array<string> = [
    'km',
    'Üzemóra',
    'tkm',
]
