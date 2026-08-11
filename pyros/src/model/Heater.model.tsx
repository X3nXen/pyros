import { SystemPurpose } from './System.model'

export enum HeaterCarrier {
    NATURAL_GAS = 'Földgáz',
    PB_GAS = 'Pbgáz',
    BIO_GAS = 'Biogáz',
    ELECTRICITY = 'Elektromos áram',
    OFF_PEAK_ELECTRICITY = 'Csúcson kívüli elektromos áram',
    HEAT_PUMP_ELECTRICITY = 'H hőszivattyús elektromos áram',
    DISTRICT_HEATING = 'Távfűtés',
    FUEL_OIL = 'Tüzelőolaj',
    COAL = 'Szén',
    FIREWOOD = 'Tűzifa',
    PELLET = 'Pellet',
    BIOMASS = 'Biomassza',
    OTHER = 'Egyéb',
}

export const PurposeToCarrier: Record<SystemPurpose, Array<HeaterCarrier>> = {
    [SystemPurpose.HEAT]: [
        HeaterCarrier.NATURAL_GAS,
        HeaterCarrier.PB_GAS,
        HeaterCarrier.BIO_GAS,
        HeaterCarrier.ELECTRICITY,
        HeaterCarrier.OFF_PEAK_ELECTRICITY,
        HeaterCarrier.HEAT_PUMP_ELECTRICITY,
        HeaterCarrier.DISTRICT_HEATING,
        HeaterCarrier.FUEL_OIL,
        HeaterCarrier.COAL,
        HeaterCarrier.FIREWOOD,
        HeaterCarrier.PELLET,
        HeaterCarrier.BIOMASS,
        HeaterCarrier.OTHER,
    ],

    [SystemPurpose.COOL]: [
        HeaterCarrier.NATURAL_GAS,
        HeaterCarrier.PB_GAS,
        HeaterCarrier.BIO_GAS,
        HeaterCarrier.ELECTRICITY,
        HeaterCarrier.HEAT_PUMP_ELECTRICITY,
        HeaterCarrier.OTHER,
    ],

    [SystemPurpose.BOTH]: [
        HeaterCarrier.NATURAL_GAS,
        HeaterCarrier.PB_GAS,
        HeaterCarrier.BIO_GAS,
        HeaterCarrier.ELECTRICITY,
        HeaterCarrier.OFF_PEAK_ELECTRICITY,
        HeaterCarrier.HEAT_PUMP_ELECTRICITY,
        HeaterCarrier.DISTRICT_HEATING,
        HeaterCarrier.FUEL_OIL,
        HeaterCarrier.COAL,
        HeaterCarrier.FIREWOOD,
        HeaterCarrier.PELLET,
        HeaterCarrier.BIOMASS,
        HeaterCarrier.OTHER,
    ],
}

export enum HeaterType {
    CONSTANT_TEMP_GAS_BOILER = 'Állandó hőmérsékletű gázkazán',
    LOW_TEMP_GAS_BOILER = 'Alacsony hőmérsékletű gázkazán',
    CONDENSING_GAS_BOILER = 'Kondenzációs gázkazán',
    GAS_BURNER = 'Gázégő',
    INDIVIDUAL_GAS_HEATER = 'Egyedi gázkonvektor',
    RADIANT_HEATER = 'Sugárzóernyő',

    HEAT_PUMP_AIR_WATER = 'Elektromos üzemű hőszivattyú levegő hőforrással (vizes)',
    HEAT_PUMP_AIR_GAS = 'Elektromos üzemű hőszivattyú levegő hőforrással (hűtőgázos)',
    HEAT_PUMP_GROUND = 'Elektromos üzemű hőszivattyú talajhő hőforrással',
    HEAT_PUMP_WATER = 'Elektromos üzemű hőszivattyú víz hőforrással',
    VRV_VRF = 'VRV/VRF',
    SPLIT_AC = 'Split klíma',
    ELECTRIC_BOILER = 'Elektromos üzemű kazán',
    INDIVIDUAL_ELECTRIC_HEATER = 'Egyedi elektromos fűtés',
    HEAT_PUMP_GENERIC = 'Hőszivattyú',

    THERMO_VENTILATOR = 'Termoventilátor',
    ROOFTOP = 'Rooftop',
    TECH_COOLING_HP = 'Technológiai hűtés (hőszivattyú)',
    TECH_COOLING_CHILLER = 'Technológiai hűtés (folyadékhűtő)',
    TECH_COOLING = 'Technológiai hűtés',
    CHILLER = 'Folyadékhűtő',

    DISTRICT_PLATE_HEX = 'Lemezes hőcserélős leválasztás',
    DISTRICT_TUBE_HEX = 'Csőköteges hőcserélős leválasztás',
    DISTRICT_MIXING_3WAY = 'Keverőszelepes leválasztás, 3 járatú',
    DISTRICT_MIXING_4WAY = 'Keverőszelepes leválasztás, 4 járatú',
    DISTRICT_MIXING_MANUAL = 'Keverőszelepes leválasztás, kézi',
    DISTRICT_HYDRAULIC_SWITCH = 'Hidraulikus váltós leválasztás',

    OIL_BURNER = 'Olajégő',
    OIL_STOVE = 'Olajkályha',
    OIL_BOILER = 'Olajkazán',
    COAL_BOILER = 'Szénkazán',
    STOVE = 'Kályha',
    FIREWOOD_GASIFIER_BOILER = 'Faelgázosító kazán',
    FIREWOOD_BOILER = 'Fatüzelésű kazán',
    FIREPLACE = 'Kandalló',
    BOILER = 'Kazán',

    OTHER = 'Egyéb',
}

export const HEAT_CARRIER_TO_TYPE: Record<HeaterCarrier, HeaterType[]> = {
    [HeaterCarrier.NATURAL_GAS]: [
        HeaterType.CONSTANT_TEMP_GAS_BOILER,
        HeaterType.LOW_TEMP_GAS_BOILER,
        HeaterType.CONDENSING_GAS_BOILER,
        HeaterType.GAS_BURNER,
        HeaterType.INDIVIDUAL_GAS_HEATER,
        HeaterType.RADIANT_HEATER,
        HeaterType.HEAT_PUMP_GENERIC,
        HeaterType.THERMO_VENTILATOR,
        HeaterType.ROOFTOP,
        HeaterType.OTHER,
    ],
    [HeaterCarrier.PB_GAS]: [
        HeaterType.CONSTANT_TEMP_GAS_BOILER,
        HeaterType.LOW_TEMP_GAS_BOILER,
        HeaterType.CONDENSING_GAS_BOILER,
        HeaterType.GAS_BURNER,
        HeaterType.INDIVIDUAL_GAS_HEATER,
        HeaterType.RADIANT_HEATER,
        HeaterType.HEAT_PUMP_GENERIC,
        HeaterType.THERMO_VENTILATOR,
        HeaterType.ROOFTOP,
        HeaterType.OTHER,
    ],
    [HeaterCarrier.BIO_GAS]: [
        HeaterType.CONSTANT_TEMP_GAS_BOILER,
        HeaterType.LOW_TEMP_GAS_BOILER,
        HeaterType.CONDENSING_GAS_BOILER,
        HeaterType.GAS_BURNER,
        HeaterType.INDIVIDUAL_GAS_HEATER,
        HeaterType.RADIANT_HEATER,
        HeaterType.HEAT_PUMP_GENERIC,
        HeaterType.THERMO_VENTILATOR,
        HeaterType.ROOFTOP,
        HeaterType.OTHER,
    ],

    [HeaterCarrier.ELECTRICITY]: [
        HeaterType.HEAT_PUMP_AIR_WATER,
        HeaterType.HEAT_PUMP_AIR_GAS,
        HeaterType.HEAT_PUMP_GROUND,
        HeaterType.HEAT_PUMP_WATER,
        HeaterType.VRV_VRF,
        HeaterType.INDIVIDUAL_ELECTRIC_HEATER,
        HeaterType.ELECTRIC_BOILER,
        HeaterType.SPLIT_AC,
        HeaterType.THERMO_VENTILATOR,
        HeaterType.ROOFTOP,
        HeaterType.OTHER,
    ],
    [HeaterCarrier.OFF_PEAK_ELECTRICITY]: [
        HeaterType.HEAT_PUMP_AIR_WATER,
        HeaterType.HEAT_PUMP_AIR_GAS,
        HeaterType.HEAT_PUMP_GROUND,
        HeaterType.HEAT_PUMP_WATER,
        HeaterType.VRV_VRF,
        HeaterType.INDIVIDUAL_ELECTRIC_HEATER,
        HeaterType.ELECTRIC_BOILER,
        HeaterType.SPLIT_AC,
        HeaterType.THERMO_VENTILATOR,
        HeaterType.ROOFTOP,
        HeaterType.OTHER,
    ],
    [HeaterCarrier.HEAT_PUMP_ELECTRICITY]: [
        HeaterType.HEAT_PUMP_AIR_WATER,
        HeaterType.HEAT_PUMP_AIR_GAS,
        HeaterType.HEAT_PUMP_GROUND,
        HeaterType.HEAT_PUMP_WATER,
        HeaterType.VRV_VRF,
        HeaterType.INDIVIDUAL_ELECTRIC_HEATER,
        HeaterType.ELECTRIC_BOILER,
        HeaterType.SPLIT_AC,
        HeaterType.THERMO_VENTILATOR,
        HeaterType.ROOFTOP,
        HeaterType.OTHER,
    ],

    [HeaterCarrier.DISTRICT_HEATING]: [
        HeaterType.DISTRICT_PLATE_HEX,
        HeaterType.DISTRICT_TUBE_HEX,
        HeaterType.DISTRICT_MIXING_3WAY,
        HeaterType.DISTRICT_MIXING_4WAY,
        HeaterType.DISTRICT_MIXING_MANUAL,
        HeaterType.DISTRICT_HYDRAULIC_SWITCH,
    ],

    [HeaterCarrier.FUEL_OIL]: [
        HeaterType.OIL_BURNER,
        HeaterType.OIL_STOVE,
        HeaterType.OIL_BOILER,
    ],
    [HeaterCarrier.COAL]: [HeaterType.BOILER, HeaterType.STOVE],
    [HeaterCarrier.FIREWOOD]: [
        HeaterType.FIREWOOD_GASIFIER_BOILER,
        HeaterType.FIREWOOD_BOILER,
        HeaterType.STOVE,
        HeaterType.FIREPLACE,
    ],
    [HeaterCarrier.PELLET]: [HeaterType.BOILER, HeaterType.FIREPLACE],
    [HeaterCarrier.BIOMASS]: [HeaterType.BOILER, HeaterType.FIREPLACE],
    [HeaterCarrier.OTHER]: [HeaterType.OTHER],
}

export const COOL_CARRIER_TO_TYPE: Partial<
    Record<HeaterCarrier, HeaterType[]>
> = {
    [HeaterCarrier.NATURAL_GAS]: [
        HeaterType.HEAT_PUMP_GENERIC,
        HeaterType.TECH_COOLING_HP,
        HeaterType.TECH_COOLING_CHILLER,
        HeaterType.CHILLER,
        HeaterType.OTHER,
    ],
    [HeaterCarrier.PB_GAS]: [
        HeaterType.HEAT_PUMP_GENERIC,
        HeaterType.TECH_COOLING_HP,
        HeaterType.TECH_COOLING_CHILLER,
        HeaterType.CHILLER,
        HeaterType.OTHER,
    ],
    [HeaterCarrier.BIO_GAS]: [
        HeaterType.HEAT_PUMP_GENERIC,
        HeaterType.TECH_COOLING_HP,
        HeaterType.TECH_COOLING_CHILLER,
        HeaterType.CHILLER,
        HeaterType.OTHER,
    ],

    [HeaterCarrier.ELECTRICITY]: [
        HeaterType.HEAT_PUMP_AIR_WATER,
        HeaterType.HEAT_PUMP_AIR_GAS,
        HeaterType.HEAT_PUMP_GROUND,
        HeaterType.HEAT_PUMP_WATER,
        HeaterType.VRV_VRF,
        HeaterType.SPLIT_AC,
        HeaterType.TECH_COOLING,
        HeaterType.CHILLER,
        HeaterType.OTHER,
    ],
    [HeaterCarrier.HEAT_PUMP_ELECTRICITY]: [
        HeaterType.HEAT_PUMP_AIR_WATER,
        HeaterType.HEAT_PUMP_AIR_GAS,
        HeaterType.HEAT_PUMP_GROUND,
        HeaterType.HEAT_PUMP_WATER,
        HeaterType.VRV_VRF,
        HeaterType.SPLIT_AC,
        HeaterType.TECH_COOLING,
        HeaterType.CHILLER,
        HeaterType.OTHER,
    ],
    [HeaterCarrier.OTHER]: [HeaterType.OTHER],
}

export enum HeaterDescriptions {
    NEW = 'Újszerű',
    SERVICED = 'Karbantartott',
    UNRELIABLE = 'Bizonytalan üzemű',
    OOO = 'Nem üzemel',
}

export enum ElectricCalcMode {
    UNKNOWN = 'Ismeretlen',
    ONOFF = 'On/Off működés, 1 hűtőkör',
    MULTIPLE = 'Többfokozatú működés, hűtőkörönként több kompresszor',
    INVERTER = 'Inverteres/fordulatszám szabályzott kompresszorok',
}

export enum ElectricCalcInstallation {
    UNKNOWN = 'Ismeretlen',
    GOOD = 'Gyári előírások betartásával, jól szellőző helyen',
    PARTIALLY = 'Részben zavart légárammal',
    BAD = 'Rosszul szellőző, zugos helyen',
}

export enum ElectricCalcSource {
    UNKNOWN = 'Ismeretlen',
    AIR = 'Levegő',
    SPRINKLED = 'Nedvesített levegő',
    GROUND = 'Talajszonda',
}

export enum ElectricCalcMedium {
    UNKNOWN = 'Ismeretlen',
    AIR = 'Levegő',
    WATER_NORMAL = 'Víz (normál üzemi tartomány)',
    WATER_HIGH = 'Víz (magas hőmérsékletű üzemi tartomány)',
}

export enum ElectricCalcRefrigerant {
    UNKNOWN = 'Ismeretlen',
    R410A = 'R410A',
    R32 = 'R32',
    R454B = 'R454B',
    R407C = 'R407C',
    R22 = 'R22',
    R134A_CONST = 'R134A (állandó sebesség)',
    R134A_VSD = 'R134a (VSD/centrifugás)',
    R1234ze = 'R1234ze',
    R290 = 'R290',
}

export interface HeaterFormData {
    id: string | null
    name: string
    standing: string | null
    building: string | null
    servicedBuilding: Array<string>
    serial: string
    manufacturor: string
    year: number
    type: string
    carrier: HeaterCarrier
    heatingType: HeaterType
    state: HeaterDescriptions
    forwardHeat: number
    backHeat: number
    maxPower: number
    baseType: ElectricCalcMode
    placementType: ElectricCalcInstallation
    ambientMedium: ElectricCalcMedium
    heatTransfer: ElectricCalcSource
    refrigerant: ElectricCalcRefrigerant
    heatLoss: boolean
    couldHeatLoss: boolean
    oversized: boolean
    oversizeRatio: number
    imageFile: File | null
}

export interface HeaterFormErrors {
    name: string
    standing: string
    building: string
    servicedBuilding: string
    serial: string
    manufacturor: string
    type: string
    heatingType: string
    forwardHeat: string
    backHeat: string
    maxPower: string
    oversizeRatio: string
    imageFile: string
}

export enum HeaterFeature {
    SYSTEM_HEAT = 'systemHeat',
    ELECTRIC_EFFICIENCY = 'electricEfficiency',
    REMOTE = 'remote',
}

const condensationGasFeatures: HeaterFeature[] = [HeaterFeature.SYSTEM_HEAT]
const radiantFeatures: HeaterFeature[] = [HeaterFeature.ELECTRIC_EFFICIENCY]
const heatPumpFeatures: HeaterFeature[] = [HeaterFeature.ELECTRIC_EFFICIENCY]
const electricHeatPumpFeatures: HeaterFeature[] = [
    HeaterFeature.SYSTEM_HEAT,
    HeaterFeature.ELECTRIC_EFFICIENCY,
]
const electricBoilerFeatures: HeaterFeature[] = [
    HeaterFeature.SYSTEM_HEAT,
    HeaterFeature.ELECTRIC_EFFICIENCY,
]
const districtHeatingFeatures: HeaterFeature[] = [
    HeaterFeature.SYSTEM_HEAT,
    HeaterFeature.ELECTRIC_EFFICIENCY,
    HeaterFeature.REMOTE,
]

export const DEVICE_FEATURES: Record<HeaterType, HeaterFeature[]> = {
    [HeaterType.CONDENSING_GAS_BOILER]: condensationGasFeatures,
    [HeaterType.LOW_TEMP_GAS_BOILER]: condensationGasFeatures,
    [HeaterType.CONSTANT_TEMP_GAS_BOILER]: condensationGasFeatures,
    [HeaterType.GAS_BURNER]: condensationGasFeatures,
    [HeaterType.INDIVIDUAL_GAS_HEATER]: [],
    [HeaterType.RADIANT_HEATER]: radiantFeatures,

    [HeaterType.HEAT_PUMP_AIR_WATER]: electricHeatPumpFeatures,
    [HeaterType.HEAT_PUMP_AIR_GAS]: heatPumpFeatures,
    [HeaterType.HEAT_PUMP_GROUND]: electricHeatPumpFeatures,
    [HeaterType.HEAT_PUMP_WATER]: electricHeatPumpFeatures,
    [HeaterType.VRV_VRF]: heatPumpFeatures,
    [HeaterType.INDIVIDUAL_ELECTRIC_HEATER]: radiantFeatures,
    [HeaterType.ELECTRIC_BOILER]: electricBoilerFeatures,
    [HeaterType.SPLIT_AC]: heatPumpFeatures,
    [HeaterType.HEAT_PUMP_GENERIC]: heatPumpFeatures,

    [HeaterType.THERMO_VENTILATOR]: radiantFeatures,
    [HeaterType.ROOFTOP]: radiantFeatures,
    [HeaterType.TECH_COOLING_HP]: heatPumpFeatures,
    [HeaterType.TECH_COOLING_CHILLER]: heatPumpFeatures,
    [HeaterType.TECH_COOLING]: heatPumpFeatures,
    [HeaterType.CHILLER]: electricHeatPumpFeatures,

    [HeaterType.DISTRICT_PLATE_HEX]: districtHeatingFeatures,
    [HeaterType.DISTRICT_TUBE_HEX]: districtHeatingFeatures,
    [HeaterType.DISTRICT_MIXING_3WAY]: districtHeatingFeatures,
    [HeaterType.DISTRICT_MIXING_4WAY]: districtHeatingFeatures,
    [HeaterType.DISTRICT_MIXING_MANUAL]: districtHeatingFeatures,
    [HeaterType.DISTRICT_HYDRAULIC_SWITCH]: districtHeatingFeatures,

    [HeaterType.OIL_BURNER]: electricBoilerFeatures,
    [HeaterType.OIL_STOVE]: [],
    [HeaterType.OIL_BOILER]: electricBoilerFeatures,
    [HeaterType.COAL_BOILER]: electricBoilerFeatures,
    [HeaterType.STOVE]: [],
    [HeaterType.FIREWOOD_GASIFIER_BOILER]: condensationGasFeatures,
    [HeaterType.FIREWOOD_BOILER]: condensationGasFeatures,
    [HeaterType.FIREPLACE]: [],
    [HeaterType.BOILER]: electricBoilerFeatures,

    [HeaterType.OTHER]: [],
}

export interface HeaterShort {
    id: string | null
    name: string
    purpose: SystemPurpose
}
