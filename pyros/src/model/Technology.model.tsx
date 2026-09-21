export interface CompressedFormData {
    id: string | null
    name: string
    complex: string
    pressure: number
    machines: Array<CompressorData>
    pressureReduction: boolean
    systemOptimalization: boolean
    wasteUse: string
}

export interface CompressorFormErrors {
    name: string
    pressure: string
    compressor: Array<CompressorErrors | string>
}

export interface CompressorErrors {
    compressorType: string
    standing: string
    hours: string
    nominalOutput: string
    amount: string
}

export interface CompressorData {
    id: string | null
    mode: string
    standing: string | null
    hours: number
    compressorType: string
    amount: number
    nominalOutput: number
}

export interface SteamFormData {
    id: string | null
    name: string
    complex: string
    pressure: number
    heaterMode: string
    steamUse: string
    machines: Array<SteamMachineData>
    smokeUse: string
}

export interface SteamMachineData {
    id: string | null
    standing: string | null
    mode: string
    amount: number
    type: string
    nominalOutput: number
}

export interface SteamErrors {
    name: string
    pressure: string
    machines: Array<SteamMachineErrors | string>
}

export interface SteamMachineErrors {
    standing: string
    type: string
    amount: string
    nominalOutput: string
}

export interface CoolingFormData {
    id: string | null
    name: string
    complex: string
    coolerMode: string
    machines: Array<CoolingMachineData>
    wasteUse: string
}

export interface CoolingMachineData {
    id: string | null
    mode: string
    standing: string | null
    type: string
    nominalOutput: number
    amount: number
}

export interface CoolingErrors {
    name: string
    machines: Array<CoolingMachineErrors | string>
}

export interface CoolingMachineErrors {
    type: string
    nominalOutput: string
    standing: string
    amount: string
}

export interface OtherFormData {
    id: string | null
    name: string
    complex: string
    machines: Array<OtherDeviceData>
    wasteUse: string
}

export interface OtherDeviceData {
    id: string | null
    mode: string
    standing: string | null
    type: string
    amount: number
    nominalOutput: number
    hours: number
}

export interface OtherErrors {
    name: string
    machines: Array<OtherDeviceErrors | string>
}

export interface OtherDeviceErrors {
    type: string
    nominalOutput: string
    standing: string
    hours: string
    amount: string
}

export const CompressorModes: Array<string> = ['On/off', 'Frekvenciaváltós']

export const WasteUseModes: Array<string> = [
    'Nincs, lehetőség sincs',
    'Nincs, van rá lehetőség',
    'Van, fűtés',
    'Van, HMV',
    'Van, fűtés+HMV',
]

export const HeaterModes: Array<string> = [
    'Csak technológia',
    'Épületfűtés és technológia',
]

export const SteamUseModes: Array<string> = [
    'Állandó elvétel',
    'Szakaszos üzem',
]

export const SteamMachineModes: Array<string> = [
    'Nagy vízterű',
    'Gyorsgőzfejlesztő',
]

export const CoolerModes: Array<string> = [
    'Csak technológia',
    'Épülethűtés és technológia',
]

export const CoolingMachineModes: Array<string> = [
    'Direkt elpárolgás',
    'Adiabatikus működés',
    'Szabadhűtés',
]

export const OtherMachineModes: Array<string> = ['On-off', 'Szabályozott']

export enum TechnologyType {
    COMPRESSED_AIR = 'COMPRESSED_AIR',
    STEAM = 'STEAM',
    COOLING = 'COOLING',
    OTHER = 'OTHER',
}
