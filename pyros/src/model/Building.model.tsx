export enum BuildingUsages {
    OFFICE = 'Iroda',
    RESIDENTIAL = 'Lakóépület',
    COMMERCIAL = 'Kereskedelmi',
    EDUCATION = 'Oktatási',
    FACTORY = 'Üzem',
    WAREHOUSE = 'Raktár',
}

export enum WallLayerPreset {
    WALL_A = 'Kisméretű tégla fal, 30 cm',
    WALL_B = 'Kisméretű tégla fal, 38 cm',
    WALL_C = 'Kisméretű tégla fal, 51 cm',
    WALL_D = 'B25 tégla fal, 25 cm',
    WALL_E = 'B30 tégla fal, 30 cm',
    WALL_F = 'B38 tégla fal, 38 cm',
    WALL_G = 'Soklyukú tégla fal, 30 cm',
    WALL_H = 'Soklyukú tégla fal, 38 cm',
    WALL_I = 'Soklyukú tégla fal, 45 cm',
    WALL_J = 'Pórusbeton (Ytong), 30 cm',
    WALL_K = 'Pórusbeton (Ytong), 38 cm',
    WALL_L = 'Panel, vb., 15...20 cm',
    WALL_M = 'Panel, vb., 20+ cm',
    WALL_N = 'PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 8 cm',
    WALL_O = 'PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 10 cm',
    WALL_P = 'PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 12 cm',
    WALL_Q = 'PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 15 cm',
}

export enum CeilingLayerPreset {
    CEIL_A = 'Fafödém zárt légréteggel',
    CEIL_B = 'Téglabetétes, gerendás–bordás vb födém',
    CEIL_C = 'Vb. gerenda béléstesttel',
    CEIL_D = 'Üreges vb födém',
    CEIL_E = 'Acél tartók trapézlemezzel',
    CEIL_F = 'PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 3 cm',
    CEIL_G = 'PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 4 cm',
    CEIL_H = 'PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 5 cm',
    CEIL_I = 'PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 6 cm',
    CEIL_J = 'PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 8 cm',
    CEIL_K = 'PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 10 cm',
    CEIL_L = 'PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 12 cm',
    CEIL_M = 'PUR/PIR/Kőzetgyapot/Üveggyapot szendvicspanel, 15 cm',
}

export enum DoorWindowPreset {
    DW_A = 'Szimpla fa-fém',
    DW_B = 'Kapcsolt gerébtokos fa',
    DW_C = 'Egyesített szárnyú nyíló/bukó fém/fa kerettel, kétrétegű üveggel',
    DW_D = 'Régi műanyagkeret kétrétegű üveggel',
    DW_E = 'Új műanyagkeret kétrégetű üveggel',
    DW_F = 'Új műanyagkeret két vagy háromrétegű üveggel, low-E bevonattal',
}

export enum BuildingRunning {
    CONTINUOUS = 'Folyamatos',
    PARTITIONED = 'Szakaszos',
}

export interface BuildingFormData {
    id: string | null
    name: string
    complex: string | null
    standings: Array<string>
    usage: BuildingUsages
    protected: boolean
    size: number
    stories: number
    height: number
    insideHeat: number
    running: BuildingRunning
    certificate: boolean
    floorSize: number
    doorWindowSize: number
    elevation: number
    wallLayers: WallLayerPreset
    wallInsulationWidth: number
    ceilingLayers: CeilingLayerPreset
    ceilingInsulationWidth: number
    floorInsulation: number
    doorWindowType: DoorWindowPreset
    qf: number | null
    heatLoss: number | null
    imageFile: File | null
}

export const BuildingDefaults: BuildingFormData = {
    id: null,
    name: '',
    complex: null,
    standings: [],
    usage: BuildingUsages.OFFICE,
    protected: false,
    size: 0,
    stories: 0,
    height: 0,
    insideHeat: 0,
    running: BuildingRunning.CONTINUOUS,
    certificate: false,
    floorSize: 0,
    doorWindowSize: 0,
    elevation: 0,
    wallLayers: WallLayerPreset.WALL_A,
    wallInsulationWidth: 0,
    ceilingLayers: CeilingLayerPreset.CEIL_A,
    ceilingInsulationWidth: 0,
    doorWindowType: DoorWindowPreset.DW_A,
    floorInsulation: 0,
    qf: null,
    heatLoss: null,
    imageFile: null,
}

export interface BuildingErrors {
    name: string
    complex: string
    standings: string
    size: string
    stories: string
    height: string
    insideHeat: string
    floorSize: string
    doorWallSize: string
    elevation: string
    qf: string
    heatLoss: string
    imageFile: string
}

export interface BuildingShort {
    id: string
    name: string
}

export interface ServicedBuildingShort {
    buildingId: string
    name: string
    servicedSize: number
}
