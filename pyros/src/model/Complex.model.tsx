export interface ComplexFormData {
    id: string | null
    name: string
    address: string
    postal: number
    city: string
    parcelNumber: string
    meterStandings: Array<string>
    working: Array<ComplexWorkingData>
}

export interface ComplexShortData {
    id: string
    name: string
}

export interface ComplexWorkingData {
    workType: string
    workHours: string
}

export const defaultHours: Array<string> = [
    'Általános munkarend (hétköznap, fix 8-16:30/9-17:30)',
    'Rugalmas munkarend (kötött törzsidővel, pl. 10-14 között)',
    'Kötetlen munkarend (saját időbeosztás)',
    'Többműszakos munkarend (váltott délelőtt/délután/éjszaka)',
    'Folyamatos munkarend (hétvégével, éjszakával, leállás nélkül)',
    '12/24–12/48 órás váltásos munkarend',
    '24/48 órás készenléti munkarend',
    'Hibrid munkarend (iroda + Home Office)',
    'Négynapos munkahét (pl. 4x10 óra)',
]

export interface ComplexErrors {
    name: string
    address: string
    postal: string
    parcelNumber: string
    meterStandings: string
}
