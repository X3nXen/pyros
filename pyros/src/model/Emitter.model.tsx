import type { ServicedBuildingShort } from './Building.model'
import type { PumpFormData } from './Pump.model'

export enum EmitterHmvRegulation {
    NONE = 'Nincs',
    TEMPERATURE = 'Hőmérsékletre',
    SCHEDULE = 'Időprogramra',
    BOTH = 'Hőmérsékletre és időprogramra',
}

export interface EmitterFormData {
    id: string | null
    name: string
    building: string | null
    servicedBuilding: Array<ServicedBuildingShort>
    type: string
    state: string
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
    imageFile: string
}

export const EMITTER_PURPOSE_TO_TYPE = {
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
        'Szabályozatlan',
        'Helyiségenkénti szabályozás víz oldalon',
        'Helyiségenkénti szabályozás lég oldalon',
        'Helyiségenkénti szabályozás időprogrammal víz oldalon',
        'Helyiségenkénti szabályozás időprogrammal lég oldalon',
    ],
    'Fal-mennyezetfűtés': [
        'Szabályozatlan',
        'Helyiségenkénti szabályozás víz oldalon',
        'Helyiségenkénti szabályozás lég oldalon',
        'Helyiségenkénti szabályozás időprogrammal víz oldalon',
        'Helyiségenkénti szabályozás időprogrammal lég oldalon',
    ],
    'Fan-Coil': [
        'Szabályozatlan',
        'Helyiségenkénti szabályozás víz oldalon',
        'Helyiségenkénti szabályozás lég oldalon',
        'Helyiségenkénti szabályozás időprogrammal víz oldalon',
        'Helyiségenkénti szabályozás időprogrammal lég oldalon',
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
    Padlófűtés: [
        'Szabályozatlan',
        'Helyiségenkénti szabályozás víz oldalon',
        'Helyiségenkénti szabályozás lég oldalon',
        'Helyiségenkénti szabályozás időprogrammal víz oldalon',
        'Helyiségenkénti szabályozás időprogrammal lég oldalon',
    ],
    HMV: [
        'Szabályozatlan',
        'Helyiségenkénti szabályozás',
        'Helyiségenkénti szabályozás időprogrammal',
    ],
    Termoventilátor: [
        'Szabályozatlan',
        'Helyiségenkénti szabályozás víz oldalon',
        'Helyiségenkénti szabályozás lég oldalon',
        'Helyiségenkénti szabályozás időprogrammal víz oldalon',
        'Helyiségenkénti szabályozás időprogrammal lég oldalon',
    ],
    'Technológiai hűtés': [
        'Szabályozatlan',
        'Helyiségenkénti szabályozás',
        'Helyiségenkénti szabályozás időprogrammal',
    ],
    Egyéb: [
        'Szabályozatlan',
        'Helyiségenkénti szabályozás',
        'Helyiségenkénti szabályozás időprogrammal',
    ],
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
