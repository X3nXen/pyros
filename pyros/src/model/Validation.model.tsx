import type { BuildingErrors, BuildingFormData, ServicedBuildingShort } from "./Building.model";
import type { ComplexErrors, ComplexFormData } from "./Complex.model";
import type { EmitterErrors, EmitterFormData } from "./Emitter.model";
import {
  DEVICE_FEATURES,
  HeaterFeature,
  type HeaterFormData,
  type HeaterFormErrors,
} from "./Heater.model";
import type { LightingErrors, LightingFormData } from "./Lighting.model";
import type { PumpErrors, PumpFormData } from "./Pump.model";
import { MeasurementTypes, type StandingsFormData } from "./Standings.model";
import type {
  HeatingSystemErrors,
  HeatingSystemFormData,
} from "./System.model";
import type { VehicleErrors, VehicleFormData } from "./Vehicles.model";
import { VentilationBase, VentilationHeatRetrievers, VentilationInsulationMaterial, VentilationTypes, type VentilationFormData, type VentilationFormErrors } from "./Ventilation.model";

export interface StandingsErrors {
  measurementType: string | null;
  subTo: string;
  source: string | null;
  measurement: string | null;
  dateFrom: string | null;
  dateTo: string | null;
  file: string | null;
}

export function validateStandings(
  payload: StandingsFormData,
): StandingsErrors | null {
  const errors: StandingsErrors = {
    measurementType: "",
    subTo: "",
    source: "",
    measurement: "",
    dateFrom: "",
    dateTo: "",
    file: "",
  };
  let hasError = false;
  if (!payload.measurementType) {
    errors.measurementType = "A mérés típus megadása kötelező!";
    hasError = true;
  }

  if (
    payload.measurementType &&
    payload.measurementType !== 'MAIN' as MeasurementTypes.MAIN &&
    !payload.subTo
  ) {
    errors.subTo = "A főmérő hozzárendelése kötelező!";
    hasError = true;
  }

  if (!payload.source) {
    errors.source = "A mért jellemző megadása kötelező!";
    hasError = true;
  }

  if (!payload.measurement) {
    errors.measurement = "A mértékegység megadása kötelező!";
    hasError = true;
  }

  if (!payload.dateFrom) {
    errors.dateFrom = "A -tól dátum megadása kötelező!";
    hasError = true;
  }

  if (!payload.dateTo) {
    errors.dateTo = "A -ig dátum megadása kötelező!";
    hasError = true;
  }

  if (payload.dateFrom && payload.dateTo) {
    if (payload.dateFrom.isAfter(payload.dateTo)) {
      errors.dateFrom = errors.dateTo =
        "A kezdő dátum nem lehet később, mint a végdátum!";
      hasError = true;
    } else if (payload.dateTo.diff(payload.dateFrom, "month") < 12) {
      errors.dateFrom = errors.dateTo =
        "A kezdő és vég dátum között legalább 12 hónapnak kell lennie!";
      hasError = true;
    }
  }

  if (!payload.file) {
    errors.file = "A kimutatás fájl feltöltése kötelező!";
    hasError = true;
  }

  return hasError ? errors : null;
}

export function validateComplex(
  payload: ComplexFormData,
): ComplexErrors | null {
  const errors: ComplexErrors = {
    name: "",
    address: "",
    postal: "",
    parcelNumber: "",
    meterStandings: "",
  };
  let hasError = false;

  if (!payload.name) {
    errors.name = "Adj meg egy nevet a telephelynek!";
    hasError = true;
  }
  if (!payload.address) {
    errors.address = "Add meg az utca, házszám azonosítót a telephelynek!";
    hasError = true;
  }
  if (!payload.postal) {
    errors.postal = "Add meg a telephelyhez tartozó irányítószámot/települést!";
    hasError = true;
  }
  if (!payload.parcelNumber) {
    errors.parcelNumber = "Add meg a telephely helyrajzi számát!";
    hasError = true;
  }
  if (
    payload.meterStandings === undefined ||
    payload.meterStandings.length === 0
  ) {
    errors.meterStandings =
      "Válassz ki legalább egy főmérőt ami a telephelyhez tartozik!";
    hasError = true;
  }

  return hasError ? errors : null;
}

export function validateBuilding(
  payload: BuildingFormData,
): BuildingErrors | null {
  const errors: BuildingErrors = {
    complex: "",
    standings: "",
    name: "",
    size: "",
    stories: "",
    height: "",
    insideHeat: "",
    floorSize: "",
    doorWallSize: "",
    elevation: "",
    imageIds: "",
  };

  let hasError = false;

  if (!payload.complex) {
    errors.complex = "Válassz ki egy telephelyet, amihez az épület tartozik!";
    hasError = true;
  }

  if (!payload.standings || payload.standings.length === 0) {
    errors.standings = "Válassz ki legalább egy hozzárendelt almérőt!";
    hasError = true;
  }

  if (!payload.name || payload.name.trim() === "") {
    errors.name = "Add meg a nevet az épülethez!";
    hasError = true;
  }

  if (
    payload.size === undefined ||
    payload.size === null ||
    payload.size <= 0
  ) {
    errors.size = "Az épület területének egy 0-nál nagyobb számot adj meg!";
    hasError = true;
  }

  if (
    payload.stories === undefined ||
    payload.stories === null ||
    payload.stories <= 0 ||
    payload.stories > 163
  ) {
    errors.stories = "A szintek száma egy 1-163 (Burj Khalifa) közötti szám!";
    hasError = true;
  }

  if (
    payload.height === undefined ||
    payload.height === null ||
    payload.height <= 0 ||
    payload.height > 828
  ) {
    errors.height = "A belmagasság egy 1-828 (Burj Khalifa) közötti szám!";
    hasError = true;
  }

  if (
    payload.insideHeat === undefined ||
    payload.insideHeat === null ||
    payload.insideHeat <= 0 ||
    payload.insideHeat > 30
  ) {
    errors.insideHeat = "A belső méretezési hőmérséklet egy 1-30 közötti szám!";
    hasError = true;
  }

  if (!payload.certificate) {
    if (
      payload.floorSize === undefined ||
      payload.floorSize === null ||
      payload.floorSize <= 0
    ) {
      errors.floorSize = "A padló kerülete egy 0-nál nagyobb szám!";
      hasError = true;
    }

    if (
      payload.doorWallSize === undefined ||
      payload.doorWallSize === null ||
      payload.doorWallSize <= 0
    ) {
      errors.doorWallSize =
        "A nyílászárók összfelülete egy 0-nál nagyobb szám!";
      hasError = true;
    }

    if (
      payload.elevation === undefined ||
      payload.elevation === null ||
      payload.elevation < 0 ||
      payload.elevation > 10
    ) {
      errors.elevation = "A magasság a talajtól egy 0-10 közötti szám!";
      hasError = true;
    }
  }

  /**
   * TODO: Validate images
   */

  return hasError ? errors : null;
}

export function validateHeatingSystem(payload: HeatingSystemFormData) {
  const errors: HeatingSystemErrors = {
    name: "",
    standing: "",
    heaters: [],
    pumps: [],
    emitters: [],
  };

  let hasError = false;

  if (!payload.name || payload.name === "") {
    errors.name = "Add meg a rendszer megnevezését!";
    hasError = true;
  }

  if (!payload.standing || payload.standing === "") {
    errors.standing = "Add meg a rendszerhez tartozó mérőt!";
    hasError = true;
  }

  payload.heaters.forEach((item: HeaterFormData) => {
    let localHasError = false;
    const heaterElem: HeaterFormErrors = {
      name: "",
      standing: "",
      building: "",
      servicedBuilding: "",
      serial: "",
      manufacturor: "",
      type: "",
      heatingType: "",
      forwardHeat: "",
      backHeat: "",
      maxPower: "",
      oversizeRatio: "",
      imageIds: [],
    };
    if (!item.name || item.name === "") {
      heaterElem.name = "Add meg a hőtermelő nevét!";
      localHasError = true;
    }
    if (!item.standing || item.standing === "") {
      heaterElem.standing = "Add meg a hőtermelőhöz tartozó mérőt!";
      localHasError = true;
    }
    if (!item.building || item.building === "") {
      heaterElem.building = "Add meg a hőtermelőhöz tartozó épületet!";
      localHasError = true;
    }
    if (!item.servicedBuilding || item.servicedBuilding.length === 0) {
      heaterElem.servicedBuilding =
        "Add meg a hőtermelő által kiszolgált épületeket!";
      localHasError = true;
    }
    if (!item.serial || item.serial === "") {
      heaterElem.serial = "Add meg a hőtermelő gyári számát!";
      localHasError = true;
    }
    if (!item.manufacturor || item.manufacturor === "") {
      heaterElem.manufacturor = "Add meg a hőtermelő gyártóját!";
      localHasError = true;
    }
    if (!item.type || item.type === "") {
      heaterElem.type = "Add meg a hőtermelő típusát!";
      localHasError = true;
    }
    if (!item.heatingType || item.heatingType === "") {
      heaterElem.heatingType = "Válaszd ki a hőtermelés jellegét!";
      localHasError = true;
    }
    if (
      item.heatingType &&
      DEVICE_FEATURES[item.heatingType].includes(HeaterFeature.SYSTEM_HEAT)
    ) {
      if (!item.forwardHeat || item.forwardHeat === 0) {
        heaterElem.forwardHeat = "Add meg az előremenő hőmérsékletet!";
        localHasError = true;
      }
      if (!item.backHeat || item.backHeat === 0) {
        heaterElem.backHeat = "Add meg a visszatérő hőmérsékletet!";
        localHasError = true;
      }
    }

    if (!item.maxPower || item.maxPower === 0) {
      heaterElem.maxPower = "Add meg a berendezés max. bevitt teljesítményét!";
      localHasError = true;
    }

    if (item.oversized && (!item.oversizeRatio || item.oversizeRatio === 0)) {
      heaterElem.oversizeRatio = "Add meg a túlméretezettség mértékét!";
      localHasError = true;
    }
    if (localHasError) {
      errors.heaters.push(heaterElem);
      hasError = true;
    } else {
      errors.heaters.push("none");
    }
  });

  payload.pumps.forEach((item: PumpFormData) => {
    let localHasError = false;
    const pumpElem: PumpErrors = {
      name: "",
      building: "",
      servicedBuilding: "",
      manufacturor: "",
      type: "",
      serialNumber: "",
      powerUsage: "",
    };

    if (!item.name || item.name === "") {
      pumpElem.name = "Add meg a szivattyú nevét!";
      localHasError = true;
    }
    if (!item.building || item.building === "") {
      pumpElem.building = "Add meg a szivattyúhoz tartozó épületet!";
      localHasError = true;
    }
    if (!item.servicedBuilding || item.servicedBuilding.length === 0) {
      pumpElem.servicedBuilding =
        "Add meg a szivattyú által kiszolgált épületeket és területeket!";
      localHasError = true;
    }
    if (!item.manufacturor || item.manufacturor === "") {
      pumpElem.manufacturor = "Add meg a szivattyú gyártóját!";
      localHasError = true;
    }
    if (!item.type || item.type === "") {
      pumpElem.type = "Add meg a szivattyú típusát!";
      localHasError = true;
    }
    if (!item.serialNumber || item.serialNumber === "") {
      pumpElem.serialNumber = "Add meg a szivattyú gyári számát!";
      localHasError = true;
    }
    if (!item.powerUsage || item.powerUsage === 0) {
      pumpElem.powerUsage = "Add meg a szivattyú villamos energiaigényét!";
      localHasError = true;
    }
    if (localHasError) {
      errors.pumps.push(pumpElem);
      hasError = true;
    } else {
      errors.pumps.push("none");
    }
  });

  payload.emitters.forEach((item: EmitterFormData) => {
    let localHasError = false;
    const emitterElem: EmitterErrors = {
      name: "",
      building: "",
      servicedBuilding: "",
      amount: "",
      forwardHeat: "",
      backHeat: "",
      imageIds: "",
    };

    if(!item.name || item.name === ""){
      emitterElem.name = "Add meg a hőleadó megnevezését!";
      localHasError = true;
    }
    if(!item.building || item.building === ""){
      emitterElem.building = "Add meg a hőleadóhoz tartozó épületet!";
      localHasError = true;
    }
    if(!item.servicedBuilding || item.servicedBuilding.length === 0){
      emitterElem.servicedBuilding = "Add meg a hőtermelő által kiszolgált épületeket és területeket!";
      localHasError = true;
    }
    if(!item.amount || item.amount === 0){
      emitterElem.amount = "Add meg a hőleadók darabszámát!";
      localHasError = true;
    }
    if(!item.forwardHeat || item.forwardHeat === 0){
      emitterElem.forwardHeat = "Add meg a hőleadó rendszerében az előremenő hőmérsékletet!";
      localHasError = true;
    }
    if(!item.backHeat || item.backHeat === 0){
      emitterElem.backHeat = "Add meg a hőleadó rendszerében a visszatérő hőmérsékletet!";
      localHasError = true;
    }
    if(localHasError){
      errors.emitters.push(emitterElem);
      hasError = true;
    } else {
      errors.emitters.push("none");
    }
  });
  return hasError ? errors : null;
}

export function validateVentilationSystem(payload: VentilationFormData){
  const errors: VentilationFormErrors = {
    name: "",
    building: "",
    servicedBuilding: "",
    servicedSizes: null,
    forwardHeat: "",
    backHeat: "",
    ventilationOther: "",
    suckRatio: "",
    suckPower: "",
    blowRatio: "",
    blowPower: "",
    retrieverYear: "",
    insulationWidth: "",
    imageIds: ""
  };
  let hasError = false;

  if(!payload.name || payload.name === ""){
    errors.name = "Add meg a légkezelő rendszer megnevezését!"
    hasError = true;
  }

  if(!payload.building || payload.building === ""){
    errors.building = "Add meg a légkezelő rendszerhez tartozó épületet!"
    hasError = true;
  }

  if(!payload.servicedBuilding || payload.servicedBuilding.length === 0){
    errors.servicedBuilding = "Add meg a légkezelő által kiszolgált épületeket!"
    hasError = true;
  }

  if(payload.servicedBuilding.length > 0){
    payload.servicedBuilding.forEach((e: ServicedBuildingShort) => {
      if(!e.servicedSize || e.servicedSize === 0){
        errors.servicedSizes = {...errors.servicedSizes, [e.buildingId]: "Add meg a kiszolgált területet az épületben!"}
        hasError = true;
      }
    })
  }

  if(payload.type === VentilationBase.WATER){
    if(!payload.forwardHeat || payload.forwardHeat === 0){
      errors.forwardHeat = "Add meg a rendszer előremenő hőmérsékletét!";
      hasError = true;
    }
    if(!payload.backHeat || payload.backHeat === 0){
      errors.backHeat = "Add meg a rendszer visszatérő hőmérsékletét!";
      hasError = true;
    }
  }

  if(payload.ventilatorType === VentilationTypes.OTHER){
    if(!payload.ventilationOther || payload.ventilationOther === ""){
      errors.ventilationOther = "Add meg a ventilátor típusát!";
      hasError = true;
    }
  }

  if(!payload.suckPower || payload.suckPower === 0){
    errors.suckPower = "Add meg az elszívó hálózat teljesítményét!"
    hasError = true;
  }
  if(!payload.suckRatio || payload.suckRatio === 0){
    errors.suckRatio = "Add meg az elszívó hálózat légszállítását!"
    hasError = true;
  }
  if(!payload.blowPower || payload.blowPower === 0){
    errors.blowPower = "Add meg a befúvó hálózat teljesítményét!"
    hasError = true;
  }
  if(!payload.blowRatio || payload.blowRatio === 0){
    errors.blowRatio = "Add meg a befúvó hálózat légszállítását!"
    hasError = true;
  }

  if(payload.retriever !== VentilationHeatRetrievers.NONE){
    if(!payload.retrieverYear || payload.retrieverYear < 0 || payload.retrieverYear > new Date().getFullYear()){
      errors.retrieverYear = "Add meg a hővisszanyerő gyártási évét!";
      hasError = true;
    }
  }

  if(payload.insulationMaterial !== VentilationInsulationMaterial.NONE){
    if(!payload.insulationWidth || payload.insulationWidth < 0) {
      errors.insulationWidth = "Add meg a szigetelés vastagságát!";
      hasError = true;
    }
  }

  return hasError ? errors : null;
}

export function validateLightingSystem(payload: Array<LightingFormData>) {
  const errors: Array<string | LightingErrors> = [];
  let hasError = false;
  payload.forEach((e) => {
    let localHasError = false;
    const localErrors: LightingErrors = {
      zone: "",
      size: ""
    }
    if(!e.zone || e.zone === ""){
      localErrors.zone = "Add meg a zóna nevét!";
      localHasError = true;
    }
    if(!e.size || e.size <= 0){
      localErrors.size = "Add meg a zóna méretét!";
      localHasError = true;
    }
    if(localHasError){
      hasError = true;
      errors.push(localErrors);
    } else {
      errors.push("none");
    }
  })
  return hasError ? errors : null
}

export function validateVehicle(payload: VehicleFormData) {
  const errors: VehicleErrors = {
    complex: "",
    name: "",
    usageValue: "",
    subStanding: ""
  };
  let hasError = false;

  if(!payload.name || payload.name === ""){
    errors.name = "Add meg a jármű megnevezését!";
    hasError = true;
  }

  if(!payload.complex || payload.complex === ""){
    errors.complex = "Add meg a telephelyet!";
    hasError = true;
  }

  if(!payload.usageValue || payload.usageValue <=0){
    errors.usageValue = "Add meg a használat mennyiségét!";
    hasError = true;
  }

  if(!payload.subStanding || payload.subStanding === ""){
    errors.subStanding = "Add meg az almérőt!";
    hasError = true;
  }

  return hasError ? errors : null;
}
