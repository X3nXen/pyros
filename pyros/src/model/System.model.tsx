import type { EmitterErrors, EmitterFormData } from './Emitter.model'
import type { HeaterFormData, HeaterFormErrors } from './Heater.model'
import type { PumpErrors, PumpFormData } from './Pump.model'

export enum SystemPurpose {
    HEAT = 'Fűtő',
    COOL = 'Hűtő',
    BOTH = 'Hűtő-fűtő',
}

export enum SystemRegulation {
    NONE = 'Nincs',
    REG_A = 'Hőleadó oldali szelep',
    REG_B = 'Visszatérő csavarzat',
    REG_C = 'Strang és fogyasztó oldali beszabályozás',
    REG_D = 'Nem értelmezhető',
}

export enum SystemRegulationDesc {
    NONE = 'Nincs',
    REGDESC_A = 'Előbeállítás nélküli',
    REGDESC_B = 'Teljesen nyitott',
    REGDESC_C = 'Nincs teljesen nyitva',
    REGDESC_D = 'Nem értelmezhető',
}

export interface HeatingSystemFormData {
    id: string | null
    name: string
    standing: string | null
    systemPurpose: SystemPurpose
    systemRegulation: SystemRegulation
    systemRegulationDesc: SystemRegulationDesc
    heaters: Array<HeaterFormData>
    pumps: Array<PumpFormData>
    emitters: Array<EmitterFormData>
}

export interface HeatingSystemErrors {
    name: string
    standing: string
    heaters: Array<HeaterFormErrors | string>
    pumps: Array<PumpErrors | string>
    emitters: Array<EmitterErrors | string>
}
