export enum HeaterCarrier {
  NATURAL_GAS = "Földgáz",
  PB_GAS = "Pbgáz",
  BIO_GAS = "Biogáz",
  ELECTRICITY = "Elektromos áram",
  OFF_PEAK_ELECTRICITY = "Csúcson kívüli elektromos áram",
  HEAT_PUMP_ELECTRICITY = "H hőszivattyús elektromos áram",
  DISTRICT_HEATING = "Távfűtés",
  FUEL_OIL = "Tüzelőolaj",
  COAL = "Szén",
  FIREWOOD = "Tűzifa",
  PELLET = "Pellet",
  BIOMASS = "Biomassza",
  OTHER = "Egyéb"
}

export interface HeatCoolStructure {
  heat: string[];
  cool: string[];
}

export type HeaterCarrierToTypeMap = Record<HeaterCarrier, HeatCoolStructure>;

const gasStructure: HeatCoolStructure = {
  heat: [
    "Állandó hőmérsékletű gázkazán",
    "Alacsony hőmérsékletű gázkazán",
    "Kondenzációs gázkazán",
    "Gázégő",
    "Egyedi gázkonvektor",
    "Sugárzóernyő",
    "Hőszivattyú",
    "Termoventilátor",
    "Rooftop",
    "Egyéb"
  ],
  cool: [
    "Hőszivattyú", 
    "Technológiai hűtés (hőszivattyú)", 
    "Technológiai hűtés (folyadékhűtő)", 
    "Folyadékhűtő", 
    "Egyéb"
  ]
};

const electricityStructure: HeatCoolStructure = {
  heat: [
    "Elektromos üzemű hőszivattyú levegő hőforrással (vizes)",
    "Elektromos üzemű hőszivattyú levegő hőforrással (hűtőgázos)",
    "Elektromos üzemű hőszivattyú talajhő hőforrással",
    "Elektromos üzemű hőszivattyú víz hőforrással",
    "VRV/VRF",
    "Egyedi elektromos fűtés",
    "Elektromos üzemű kazán",
    "Split klíma",
    "Termoventilátor",
    "Rooftop",
    "Egyéb"
  ],
  cool: [
    "Elektromos üzemű hőszivattyú levegő hőforrással (vizes)",
    "Elektromos üzemű hőszivattyú levegő hőforrással (hűtőgázos)",
    "Elektromos üzemű hőszivattyú talajhő hőforrással",
    "Elektromos üzemű hőszivattyú víz hőforrással",
    "VRV/VRF",
    "Split klíma",
    "Technológiai hűtés",
    "Folyadékhűtő",
    "Egyéb"
  ]
};

const pelletStructure: HeatCoolStructure = {
  heat: ["Kazán", "Kandalló"],
  cool: []
};

export const HEATER_CARRIER_TO_TYPE: HeaterCarrierToTypeMap = {
  [HeaterCarrier.NATURAL_GAS]: gasStructure,
  [HeaterCarrier.PB_GAS]: gasStructure,
  [HeaterCarrier.BIO_GAS]: gasStructure,
  
  [HeaterCarrier.ELECTRICITY]: electricityStructure,
  [HeaterCarrier.OFF_PEAK_ELECTRICITY]: electricityStructure,
  [HeaterCarrier.HEAT_PUMP_ELECTRICITY]: electricityStructure,
  
  [HeaterCarrier.DISTRICT_HEATING]: {
    heat: [
      "Lemezes hőcserélős leválasztás",
      "Csőköteges hőcserélős leválasztás",
      "Keverőszelepes leválasztás, 3 járatú",
      "Keverőszelepes leválasztás, 4 járatú",
      "Keverőszelepes leválasztás, kézi",
      "Hidraulikus váltós leválasztás"
    ],
    cool: []
  },
  
  [HeaterCarrier.FUEL_OIL]: {
    heat: ["Olajégő", "Olajkályha", "Olajkazán"],
    cool: []
  },
  
  [HeaterCarrier.COAL]: {
    heat: ["Kazán", "Kályha"],
    cool: []
  },
  
  [HeaterCarrier.FIREWOOD]: {
    heat: ["Faelgázosító kazán", "Fatüzelésű kazán", "Kályha", "Kandalló"],
    cool: []
  },
  
  [HeaterCarrier.PELLET]: pelletStructure,
  [HeaterCarrier.BIOMASS]: pelletStructure,
  
  [HeaterCarrier.OTHER]: {
    heat: ["Egyéb"],
    cool: []
  }
};

export enum HeaterDescriptions {
    NEW = "Újszerű",
    SERVICED = "Karbantartott",
    UNRELIABLE = "Bizonytalan üzemű",
    OOO = "Nem üzemel"
}

export enum ElectricCalcMode {
    UNKNOWN = "Ismeretlen",
    ONOFF = "On/Off működés, 1 hűtőkör",
    MULTIPLE = "Többfokozatú működés, hűtőkörönként több kompresszor",
    INVERTER = "Inverteres/fordulatszám szabályzott kompresszorok"
}

export enum ElectricCalcInstallation {
    UNKNOWN = "Ismeretlen",
    GOOD = "Gyári előírások betartásával, jól szellőző helyen",
    PARTIALLY = "Részben zavart légárammal",
    BAD = "Rosszul szellőző, zugos helyen"
}

export enum ElectricCalcSource {
    UNKNOWN = "Ismeretlen",
    AIR = "Levegő",
    SPRINKLED = "Nedvesített levegő",
    GROUND = "Talajszonda"
}

export enum ElectricCalcMedium {
    UNKNOWN = "Ismeretlen",
    AIR = "Levegő",
    WATER_NORMAL = "Víz (normál üzemi tartomány)",
    WATER_HIGH = "Víz (magas hőmérsékletű üzemi tartomány)"
}

export enum ElectricCalcRefrigerant {
    UNKNOWN = "Ismeretlen",
    R410A = "R410A",
    R32 = "R32",
    R454B = "R454B",
    R407C = "R407C",
    R22 = "R22",
    R134A_CONST = "R134A (állandó sebesség)",
    R134A_VSD = "R134a (VSD/centrifugás)",
    R1234ze = "R1234ze",
    R290 = "R290"
}

export interface HeaterFormData {
  id: string | null;
  name: string;
  standing: string | null;
  building: string | null;
  servicedBuilding: Array<string>;
  serial: string;
  manufacturor: string;
  year: number;
  type: string;
  carrier: HeaterCarrier;
  heatingType: string;
  state: HeaterDescriptions;
  forwardHeat: number;
  backHeat: number;
  maxPower: number;
  baseType:ElectricCalcMode;
  placementType: ElectricCalcInstallation;
  ambientMedium: ElectricCalcMedium;
  heatTransfer: ElectricCalcSource;
  refrigerant: ElectricCalcRefrigerant;
  heatLoss: boolean;
  couldHeatLoss: boolean;
  oversized: boolean;
  oversizeRatio: number;
  imageIds: Array<string>;
}

export interface HeaterFormErrors {
  name: string;
  standing: string;
  building: string;
  servicedBuilding: string;
  serial: string;
  manufacturor: string;
  type: string;
  heatingType: string;
  forwardHeat: string;
  backHeat: string;
  maxPower: string;
  oversizeRatio: string;
  imageIds: Array<string>;
}

export enum HeaterFeature {
  SYSTEM_HEAT = "systemHeat",
  ELECTRIC_EFFICIENCY = "electricEfficiency",
  REMOTE = "remote"
}

const condensationGasFeatures: HeaterFeature[] = [HeaterFeature.SYSTEM_HEAT];
const radiantFeatures: HeaterFeature[] = [HeaterFeature.ELECTRIC_EFFICIENCY];
const heatPumpFeatures: HeaterFeature[] = [HeaterFeature.ELECTRIC_EFFICIENCY];
const electricHeatPumpFeatures: HeaterFeature[] = [HeaterFeature.SYSTEM_HEAT, HeaterFeature.ELECTRIC_EFFICIENCY];
const electricBoilerFeatures: HeaterFeature[] = [HeaterFeature.SYSTEM_HEAT, HeaterFeature.ELECTRIC_EFFICIENCY];
const districtHeatingFeatures: HeaterFeature[] = [HeaterFeature.SYSTEM_HEAT, HeaterFeature.ELECTRIC_EFFICIENCY, HeaterFeature.REMOTE];

export const DEVICE_FEATURES: Record<string, HeaterFeature[]> = {
  "Kondenzációs gázkazán": condensationGasFeatures,
  "Alacsony hőmérsékletű gázkazán": condensationGasFeatures,
  "Állandó hőmérsékletű gázkazán": condensationGasFeatures,
  "Sugárzóernyő": radiantFeatures,
  "Hőszivattyú": heatPumpFeatures,
  "Termoventilátor": radiantFeatures,
  "Elektromos üzemű hőszivattyú levegő hőforrással (vizes)": electricHeatPumpFeatures,
  "Elektromos üzemű hőszivattyú levegő hőforrással (hűtőgázos)": heatPumpFeatures,
  "Elektromos üzemű hőszivattyú talajhő hőforrással": electricHeatPumpFeatures,
  "Elektromos üzemű hőszivattyú víz hőforrással": electricHeatPumpFeatures,
  "VRV/VRF": heatPumpFeatures,
  "Egyedi elektromos fűtés": radiantFeatures,
  "Elektromos üzemű kazán": electricBoilerFeatures,
  "Split klíma": heatPumpFeatures,
  "Olajkazán": electricBoilerFeatures,
  "Kazán": electricBoilerFeatures,
  "Faelgázosító kazán": condensationGasFeatures,
  "Fatüzelésű kazán": condensationGasFeatures,
  "Kandalló": [],
  "Lemezes hőcserélős leválasztás": districtHeatingFeatures,
  "Csőköteges hőcserélős leválasztás": districtHeatingFeatures,
  "Keverőszelepes leválasztás, 3 járatú": districtHeatingFeatures,
  "Keverőszelepes leválasztás, 4 járatú": districtHeatingFeatures,
  "Keverőszelepes leválasztás, kézi": districtHeatingFeatures,
  "Hidraulikus váltós leválasztás": districtHeatingFeatures,
  "Folyadékhűtő": electricHeatPumpFeatures,
  "Technológiai hűtés (hőszivattyú)": heatPumpFeatures,
  "Technológiai hűtés (folyadékhűtő)": heatPumpFeatures,
  "Egyéb": []
};