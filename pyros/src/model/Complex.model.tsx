export interface ComplexFormData {
    id: string | null;
    name: string;
    address: string;
    postal: number;
    city: string;
    parcelNumber: string;
    meterStandings: Array<string>
}

export interface ComplexShortData {
    id: string;
    name: string;
}

export interface ComplexErrors {
    name: string;
    address: string;
    postal: string;
    parcelNumber: string;
    meterStandings: string
}