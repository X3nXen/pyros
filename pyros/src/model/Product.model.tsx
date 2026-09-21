export interface ProductFormData {
    id: string | null
    name: string
    metric: string
    file: File | null
    isPrimary: boolean
}

export const ProductMetric: Array<string> = [
    'Tonna',
    'Darab',
    'Négyzetméter',
    'Ezer darab',
    'Millió darab',
    'Kilogram',
    'Fő',
    'Köbméter',
    'Liter',
]
