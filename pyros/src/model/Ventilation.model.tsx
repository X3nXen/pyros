import type { ServicedBuildingShort } from "./Building.model";

export enum VentilationTypes {
  TYPE_A = "Ékszíjhajtás AC motoros",
  TYPE_B = "Direkthajtás AC motoros",
  TYPE_C = "Direkthajtás változó frekvenciás AC motoros",
  TYPE_D = "EC motoros",
  OTHER = "Egyéb",
}

export enum VentilationHeatRetrievers {
  TYPE_A = "Keresztáramú",
  TYPE_B = "Forgódobos",
  TYPE_C = "Közvetítő közeges",
  TYPE_D = "Hőcsöves",
  TYPE_E = "Keverőkamra",
  OTHER = "Egyéb",
  NONE = "Nincs",
}

export enum VentilationInsulationMaterial {
  TYPE_A = "Üveg/kőzetgyapot",
  TYPE_B = "Kaucsuk",
  TYPE_C = "Filc",
  TYPE_D = "Polifoam",
  TYPE_E = "PUR/PIR",
  NONE = "Nincs",
}

export enum VentilationStateTypes {
  TYPE_A = "Szabályozatlan",
  TYPE_B = "Műszakilag rossz állapotú",
  TYPE_C = "Karbantartott, elavult",
  TYPE_D = "Új, karbantartott, energiahatékony",
}

export enum VentilationRegulation {
  TYPE_A = "Befújt léghőmérséklet",
  TYPE_B = "Elszívott/terem hőmérséklet",
  TYPE_C = "Levegőminőség",
}

export enum VentilationBase {
  WATER = "Víz",
  AIR = "Levegő",
}

export enum VentilationRunning {
  TYPE_A = "Napi maximum 10 óra, napközben, munkanapokon",
  TYPE_B = "Napi maximum 16 óra, munkanapokon",
  TYPE_C = "Napi maximum 10 óra, napközben, minden nap",
  TYPE_D = "Napi maximum 16 óra, minden nap",
  TYPE_E = "0-24, évi 250 nap",
  TYPE_F = "0-24, évi 300 nap",
  TYPE_G = "0-24, évi 365 nap",
  TYPE_H = "Alkalmanként, évente kevesebb mint 2000 óra",
}

export interface VentilationFormData {
  id: string | null;
  name: string;
  building: string | null;
  servicedBuilding: Array<ServicedBuildingShort>;
  type: VentilationBase;
  forwardHeat: number;
  backHeat: number;
  state: VentilationStateTypes;
  ventilatorType: VentilationTypes;
  ventilationOther: string;
  suckRatio: number;
  blowRatio: number;
  suckPower: number;
  blowPower: number;
  retriever: VentilationHeatRetrievers;
  retrieverYear: number;
  insulationWidth: number;
  insulationMaterial: VentilationInsulationMaterial;
  regulation: VentilationRegulation;
  running: VentilationRunning;
  imageIds: Array<string>;
}

export interface VentilationFormErrors {
    name: string;
    building: string;
    servicedBuilding: string;
    servicedSizes: Record<string, string> | null;
    forwardHeat: string;
    backHeat: string;
    ventilationOther: string;
    suckRatio: string;
    suckPower: string;
    blowRatio: string;
    blowPower: string;
    retrieverYear: string;
    insulationWidth: string;
    imageIds: string;
}
