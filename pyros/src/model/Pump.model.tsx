import type { ServicedBuildingShort } from "./Building.model";

export interface PumpFormData {
  id: string | null;
  name: string;
  building: string | null;
  servicedBuilding: Array<ServicedBuildingShort>;
  manufacturor: string;
  type: string;
  year: number;
  archetype: PumpTypes;
  archetypeSetting: PumpSetting;
  serialNumber: string;
  powerUsage: number;
  imageIds: Array<string>;
}

export interface PumpErrors {
  name: string;
  building: string;
  servicedBuilding: string;
  manufacturor: string;
  type: string;
  serialNumber: string;
  powerUsage: string;
}

export enum PumpTypes {
  TYPE_A = "Állandó fordulatú",
  TYPE_B = "Változtatható fordulatszámú",
  TYPE_C = "Frekvenciaváltós",
}

export enum PumpSetting {
  SET_A = "Arányos beállítás",
  SET_B = "Állandó térfogatáram",
  SET_C = "Állandó emelőmagasság",
  SET_D = "1.fokozat",
  SET_E = "2.fokozat",
  SET_F = "3.fokozat",
  SET_G = "4.fokozat",
  SET_H = "Egyfokozatú",
  SET_I = "Nincs beállítva",
}
