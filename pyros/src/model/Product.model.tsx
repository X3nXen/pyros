export interface ProductFormData{
    id: string | null;
    metric: string;
    file: File | null;
}

export const ProductMetric: Array<string> = [
    "Tonna",
    "Darab",
    "Négyzetméter",
    "Ezer darab",
    "Millió darab",
    "Kilogram",
    "Fő",
    "Köbméter",
    "Liter"
]