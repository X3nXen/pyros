import type { ComplexShortData } from "./Complex.model";
import type { StandingsShort } from "./Standings.model";

export enum BuildingUsages {
  OFFICE = "Iroda",
  RESIDENTIAL = "Lakóépület",
  COMMERCIAL = "Kereskedelmi",
  EDUCATION = "Oktatási",
  FACTORY = "Üzem",
  WAREHOUSE = "Raktár",
}

export enum WallLayerPreset {
  WALL_A = "Kisméretű tégla fal, 30 cm",
  WALL_B = "Kisméretű tégla fal, 38 cm",
  WALL_C = "Kisméretű tégla fal, 51 cm",
  WALL_D = "B25 tégla fal, 25 cm",
  WALL_E = "B30 tégla fal, 30 cm",
  WALL_F = "B38 tégla fal, 38 cm",
  WALL_G = "Soklyukú tégla fal, 30 cm",
  WALL_H = "Soklyukú tégla fal, 38 cm",
  WALL_I = "Soklyukú tégla fal, 45 cm",
  WALL_J = "Pórusbeton (Ytong), 30 cm",
  WALL_K = "Pórusbeton (Ytong), 38 cm",
  WALL_L = "Panel, vb., 15...20 cm",
  WALL_M = "Panel, vb., 20+ cm",
  WALL_N = "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 8 cm",
  WALL_O = "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 10 cm",
  WALL_P = "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 12 cm",
  WALL_Q = "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 15 cm",
}

export enum CeilingLayerPreset {
  CEIL_A = "Fafödém zárt légréteggel",
  CEIL_B = "Téglabetétes, gerendás–bordás vb födém",
  CEIL_C = "Vb. gerenda béléstesttel",
  CEIL_D = "Üreges vb födém",
  CEIL_E = "Acél tartók trapézlemezzel",
  CEIL_F = "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 3 cm",
  CEIL_G = "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 4 cm",
  CEIL_H = "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 5 cm",
  CEIL_I = "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 6 cm",
  CEIL_J = "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 8 cm",
  CEIL_K = "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 10 cm",
  CEIL_L = "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 12 cm",
  CEIL_M = "PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 15 cm",
};

export enum DoorWindowPreset {
  DW_A = "Szimpla fa-fém",
  DW_B = "Kapcsolt gerébtokos fa",
  DW_C = "Egyesített szárnyú nyíló/bukó fém/fa kerettel, kétrétegű üveggel",
  DW_D = "Régi műanyagkeret kétrétegű üveggel",
  DW_E = "Új műanyagkeret kétrégetű üveggel",
  DW_F = "Új műanyagkeret két vagy háromrétegű üveggel, low-E bevonattal",
};

export enum TypicalHeatingTypePreset {
  TH_A = "Elektromos radiátor vagy elektromos hősugárzó",
  TH_B = "Elektromos kazán",
  TH_C = "Elektromos hőtárolós kályha",
  TH_D = "Fűtőművi távfűtés",
  TH_E = "Fatüzelésű cserépkályha",
  TH_F = "Kandalló, kályha (zárt, hagyományos)",
  TH_G = "Kandalló (nyitott, hagyományos)",
  TH_H = "Gázkonvektor, gázüzemű sugárzóernyő",
  TH_I = "Széntüzelésű kazán",
  TH_J = "Pellettüzelésű kazán",
  TH_K = "Faelgázosító kazán",
  TH_L = "Levegő-víz hőszivattyú (magas hőmérséklet)",
  TH_M = "Levegő-víz hőszivattyú (alacsony hőmérséklet)",
  TH_N = "Hagyományos gázkazán fűtött téren belül",
  TH_O = "Kondenzációs gázkazán fűtött téren belül",
  TH_P = "Hagyományos gázkazán fűtött téren kívül",
  TH_Q = "Kondenzációs gázkazán fűtött téren kívül",
};

export enum WarmWaterCreationPreset {
  WWC_A = "Nincs",
  WWC_B = "Elektromos bojler",
  WWC_C = "Közvetlen gáztüzelésű berendezés",
  WWC_D = "Fűtőművi távfűtés",
  WWC_E = "Hőszivattyús",
}

export enum HeatingTypicalRegulationPreset {
  HTR_A = "Szabályozás helyiség szinten",
  HTR_B = "Időjáráskövető központi szabályozás",
  HTR_C = "Egyszerű központi szabályozás",
  HTR_D = "Szabályozatlan hőleadás",
}

export enum BuildingRunning {
    CONTINUOUS = "Folyamatos",
    PARTITIONED = "Szakaszos"
};

export interface BuildingFormData {
  id: string | null;
  name: string;
  complex: ComplexShortData | null;
  standings: Array<StandingsShort>;
  usage: BuildingUsages;
  protected: boolean;
  size: number;
  stories: number;
  height: number;
  insideHeat: number;
  running: BuildingRunning;
  certificate: boolean;
  floorSize: number;
  doorWallSize: number;
  elevation: number;
  wallLayers: WallLayerPreset;
  wallInsulationWidth: number;
  ceilingLayers: CeilingLayerPreset;
  ceilingInsulationWidth: number;
  floorInsulation: number;
  doorWindowType: DoorWindowPreset;
  heatingType: TypicalHeatingTypePreset;
  regulationMode: HeatingTypicalRegulationPreset;
  hmvCreation: WarmWaterCreationPreset;
  hmvContainment: boolean;
  hmvCirculation: boolean;
  imageIds: Array<string>;
};

export const BuildingDefaults: BuildingFormData = {
    id: null,
    name: "",
    complex: null,
    standings: [],
    usage: BuildingUsages.OFFICE,
    protected:false,
    size: 0,
    stories: 0,
    height: 0,
    insideHeat: 0,
    running: BuildingRunning.CONTINUOUS,
    certificate: false,
    floorSize: 0,
    doorWallSize: 0,
    elevation: 0,
    wallLayers: WallLayerPreset.WALL_A,
    wallInsulationWidth: 0,
    ceilingLayers: CeilingLayerPreset.CEIL_A,
    ceilingInsulationWidth: 0,
    doorWindowType: DoorWindowPreset.DW_A,
    floorInsulation: 0,
    heatingType: TypicalHeatingTypePreset.TH_A,
    regulationMode: HeatingTypicalRegulationPreset.HTR_A,
    hmvCreation: WarmWaterCreationPreset.WWC_A,
    hmvContainment: false,
    hmvCirculation: false,
    imageIds: []
  };

export interface BuildingErrors {
  name: string;
  complex: string;
  standings: string;
  size: string;
  stories: string;
  height: string;
  insideHeat: string;
  floorSize: string;
  doorWallSize: string;
  elevation: string;
  imageIds: string;
}

export interface BuildingShort {
  id: string;
  name: string;
}

export interface ServicedBuildingShort {
  buildingId: string;
  name: string;
  servicedSize: number;
}
