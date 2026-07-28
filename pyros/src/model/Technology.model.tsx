export interface CompressedFormData {
    id: string | null
    name: string
    pressure: number
    compressors: Array<CompressorData>
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
    mode: string
    standing: string | null
    hours: number
    compressorType: string
    amount: number
    nominalOutput: number
    couldWasteUse: boolean
    wasteUse: string
}

export interface SteamFormData {
    id: string | null
    name: string
    pressure: number
    heaterMode: string
    steamUse: string
    machines: Array<SteamMachineData>
}

export interface SteamMachineData {
    standing: string | null
    mode: string
    type: string
    nominalOutput: number
    couldSmokeUse: boolean
    smokeUse: string
}

export interface SteamErrors {
    name: string
    pressure: string
    machines: Array<SteamMachineErrors | string>
}

export interface SteamMachineErrors {
    standing: string
    type: string
    nominalOutput: string
}

export interface CoolingFormData {
    id: string | null
    name: string
    coolerMode: string
    machines: Array<CoolingMachineData>
}

export interface CoolingMachineData {
    mode: string
    standing: string | null
    type: string
    nominalOutput: number
    couldWasteUse: boolean
    wasteUse: string
}

export interface CoolingErrors {
    name: string
    machines: Array<CoolingMachineErrors | string>
}

export interface CoolingMachineErrors {
    type: string
    nominalOutput: string
    standing: string
}

export interface OtherFormData {
    id: string | null
    name: string
    machines: Array<OtherDeviceData>
}

export interface OtherDeviceData {
    mode: string
    standing: string | null
    type: string
    amount: number
    nominalOutput: number
    hours: number
    couldWasteUse: boolean
    wasteUse: string
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
    'Nincs',
    'Van, fűtés',
    'Van, HMV',
    'Van, fűtés és HMV',
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
    COMPRESSED_AIR,
    STEAM,
    COOLING,
    OTHER,
}
