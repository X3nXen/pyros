import type { ServicedBuildingShort } from './Building.model'

export interface PumpFormData {
    id: string | null
    name: string
    building: string | null
    servicedBuilding: Array<ServicedBuildingShort>
    archetype: PumpTypes
    archetypeSetting: PumpSetting
    imageFile: File | null
}

export interface PumpErrors {
    name: string
    building: string
    servicedBuilding: string
    imageFile: string
}

export enum PumpTypes {
    TYPE_A = 'Állandó fordulatú',
    TYPE_B = 'Változtatható fordulatszámú',
    TYPE_C = 'Frekvenciaváltós',
}

export enum PumpSetting {
    SET_A = 'Arányos beállítás',
    SET_B = 'Állandó térfogatáram',
    SET_C = 'Állandó emelőmagasság',
    SET_D = 'Nincs beállítva',
    SET_E = 'Fix beállítás',
}
