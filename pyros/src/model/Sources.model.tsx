import type { Dayjs } from "dayjs";

export enum EnergySources{
    COAL = "Szén",
    GASOLINE = "Gázolaj",
    PETROL = "Benzin",
    GAS = "Földgáz",
    ELECTRICITY = "Elektromos áram",
    REMOTE = "Távhő",
    PAKURA = "Pakura",
    PB = "PB Gáz",
    PROPANE = "Propán",
    LPG = "LPG",
    WOOD = "Tűzifa",
    SOLAR = "Napenergia"
}

export enum EnergyMeasurements{
    KWH = "kWh",
    MJ = "MJ",
    MCUBE = "m3",
    GJ = "GJ",
    MWH = "MWh"
}

export enum MeasurementTypes{
    MAIN = "Főmérő",
    SUB = "Almérő",
    VIRT = "Virtuális"
}

export interface StandingsFormData{
    measurementType: MeasurementTypes,
    source: EnergySources,
    measurement: EnergyMeasurements,
    dateFrom: Dayjs | null,
    dateTo: Dayjs | null,
    file: File | null
}