import type { ServicedBuildingShort } from './Building.model'
import type { ElectricCalcRefrigerant, HeatCoolStructure } from './Heater.model'
import type { PumpFormData } from './Pump.model'

export enum EmitterHmvRegulation {
    NONE = 'Nincs',
    TEMPERATURE = 'Hőmérsékletre',
    SCHEDULE = 'Időprogramra',
    BOTH = 'Hőmérsékletre és időprogramra',
}

export enum VentilationBase {
    WATER = 'Vizes',
    AIR = 'Levegő',
}

export interface EmitterFormData {
    id: string | null
    name: string
    building: string | null
    servicedBuilding: Array<ServicedBuildingShort>
    type: string
    amount: number
    forwardHeat: number
    backHeat: number
    state: string
    vrvRefrigerant: ElectricCalcRefrigerant
    vrvInsideType: EmitterIndoorUnitPlacement
    insideRoom: boolean
    circulation: boolean
    circulatoryPumps: Array<PumpFormData>
    hmvRegulation: EmitterHmvRegulation
    imageFile: File | null
}

export interface EmitterErrors {
    name: string
    building: string
    servicedBuilding: string
    amount: string
    forwardHeat: string
    backHeat: string
    imageFile: string
}

export const EMITTER_PURPOSE_TO_TYPE: HeatCoolStructure = {
    cool: [
        'Fal-mennyezetfűtés',
        'Fan-Coil',
        'VRV/VRF',
        'Split beltéri',
        'Technológiai hűtés',
        'Egyéb',
    ],
    heat: [
        'Radiátor',
        'Padlófűtés',
        'Fal-mennyezetfűtés',
        'Fan-Coil',
        'VRV/VRF',
        'Split beltéri',
        'HMV',
        'Termoventilátor',
        'Egyéb',
    ],
}

export const EMITTER_TYPE_TO_REGULATION: Record<string, string[]> = {
    Radiátor: [
        'Helyiséghőmérsékletre történő szabályozás nélkül',
        'Régi termosztátfej',
        'Új termosztátfej',
        'Időprogramozással működő termosztátfej',
        'Helyiséghőmérsékletre történő szabályozás központi szabályozással',
    ],
    'Fal-mennyezetfűtés': [
        'Szabályozatlan',
        'Kézi szelep',
        'Helyiségenkénti szabályozás',
        'Helyiségenkénti szabályozás időprogrammal',
    ],
    'Fan-Coil': [
        'Szabályozatlan',
        'Kézi szelep',
        'Helyiségenkénti szabályozás',
        'Helyiségenkénti szabályozás időprogrammal',
    ],
    'VRV/VRF': [
        'Szabályozatlan',
        'Helyiségenkénti szabályozás',
        'Helyiségenkénti szabályozás időprogrammal',
    ],
    'Split beltéri': [
        'Szabályozatlan',
        'Helyiségenkénti szabályozás',
        'Helyiségenkénti szabályozás időprogrammal',
    ],
    Padlófűtés: ['Nem értelmezhető'],
    HMV: ['HMV tartály'],
    Termoventilátor: ['Berendezés szabályozza'],
    'Technológiai hűtés': ['Berendezés szabályozza'],
    Egyéb: ['Nem értelmezhető'],
}

export enum EmitterIndoorUnitPlacement {
    WALL = 'Oldalfali',
    CASSETTE = 'Kazettás',
    DUCT = 'Légcsatornás',
    CEILING = 'Menyezeti',
    OTHER = 'Egyéb',
}

export enum EmitterVentilationType {
    BELT_AC = 'Ékszíjhajtás AC motoros',
    DIRECT_AC = 'Direkthajtás AC motoros',
    DIRECT_VFD_AC = 'Direkthajtás változó frekvenciás AC motoros',
    EC = 'EC motoros',
    OTHER = 'Egyéb',
}
