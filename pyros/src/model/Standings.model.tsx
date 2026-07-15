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

export const StringToType: Record<string, MeasurementTypes> = {
  "Főmérő": MeasurementTypes.MAIN,
  "Almérő": MeasurementTypes.SUB,
  "Virtuális": MeasurementTypes.VIRT
}

export interface StandingsShort{
    id: string;
    name: string;
}

export interface StandingsFormData{
    id: string | null,
    name: string,
    measurementType: MeasurementTypes,
    subTo: string | null,
    source: EnergySources,
    measurement: EnergyMeasurements,
    dateFrom: Dayjs | null,
    dateTo: Dayjs | null,
    file: File | null
}