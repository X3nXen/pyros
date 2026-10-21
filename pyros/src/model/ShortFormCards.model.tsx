export interface ShortFormData {
    id: string
    name: string
}

export interface ShortFormCollection {
    standing: Array<ShortFormData>
    complex: Array<ShortFormData>
    building: Array<ShortFormData>
    heating_system: Array<ShortFormData>
    hmv_system: Array<ShortFormData>
    hvac_system: Array<ShortFormData>
    lighting_system: Array<ShortFormData>
    technology: Array<ShortFormData>
    vehicle: Array<ShortFormData>
    product: Array<ShortFormData>
    variable: Array<ShortFormData>
}

export enum ShortFormType {
    STANDING,
    COMPLEX,
    BUILDING,
    HEATING_SYSTEM,
    HMV_SYSTEM,
    HVAC_SYSTEM,
    LIGHTING_SYSTEM,
    TECHNOLOGY,
    VEHICLE,
    PRODUCT,
}
