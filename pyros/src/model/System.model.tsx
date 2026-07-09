import type { EmitterFormData } from "./Emitter.model";
import type { HeaterFormData } from "./Heater.model";
import type { PumpFormData } from "./Pump.model";

export enum SystemPurpose {
  HEAT = "Fűtő",
  COOL = "Hűtő",
  BOTH = "Hűtő-fűtő"
}

export enum SystemRegulation {
  NONE = "Nincs",
  REG_A = "Hőleadó oldali szelep",
  REG_B = "Visszatérő csavarzat",
  REG_C = "Strang és fogyasztó oldali beszabályozás"
}

export enum SystemRegulationDesc {
  NONE = "Nincs",
  REGDESC_A = "Előbeállítás nélküli",
  REGDESC_B = "Teljesen nyitott",
  REGDESC_C = "Nincs teljesen nyitva"
}

export interface HeatingSystemFormData {
  id: string | null;
  name: string;
  standing: string | null;
  systemPurpose: SystemPurpose;
  systemRegulation: SystemRegulation;
  systemRegulationDesc: SystemRegulationDesc;
  heaters: Array<HeaterFormData>;
  pumps: Array<PumpFormData>;
  emitters: Array<EmitterFormData>;
}