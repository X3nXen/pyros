import type { BuildingErrors, BuildingFormData } from "./Building.model";
import type { ComplexErrors, ComplexFormData } from "./Complex.model";
import { MeasurementTypes, type StandingsFormData } from "./Standings.model";

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
    payload.measurementType !== MeasurementTypes.MAIN &&
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

export function validateComplex(payload: ComplexFormData) : ComplexErrors | null {
  const errors: ComplexErrors = {
    name: "",
    address: "",
    postal: "",
    parcelNumber: "",
    meterStandings: "",
  };
  let hasError = false;

  if(!payload.name){
    errors.name = "Adj meg egy nevet a telephelynek!";
    hasError = true;
  }
  if(!payload.address){
    errors.address = "Add meg az utca, házszám azonosítót a telephelynek!";
    hasError = true;
  }
  if(!payload.postal){
    errors.postal = "Add meg a telephelyhez tartozó irányítószámot/települést!";
    hasError = true; 
  }
  if(!payload.parcelNumber){
    errors.parcelNumber = "Add meg a telephely helyrajzi számát!";
    hasError = true;
  }
  if(payload.meterStandings === undefined || payload.meterStandings.length === 0){
    errors.meterStandings = "Válassz ki legalább egy főmérőt ami a telephelyhez tartozik!";
    hasError = true;
  }

  return hasError ? errors : null;
}

export function validateBuilding(payload: BuildingFormData) : BuildingErrors | null {
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
    imageIds: ""
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

  if (payload.size === undefined || payload.size === null || payload.size <= 0) {
    errors.size = "Az épület területének egy 0-nál nagyobb számot adj meg!";
    hasError = true;
  }

  if (payload.stories === undefined || payload.stories === null || payload.stories <= 0 || payload.stories > 163) {
    errors.stories = "A szintek száma egy 1-163 (Burj Khalifa) közötti szám!";
    hasError = true;
  }

  if (payload.height === undefined || payload.height === null || payload.height <= 0 || payload.height > 828) {
    errors.height = "A belmagasság egy 1-828 (Burj Khalifa) közötti szám!";
    hasError = true;
  }

  if (payload.insideHeat === undefined || payload.insideHeat === null || payload.insideHeat <= 0 || payload.insideHeat > 30) {
    errors.insideHeat = "A belső méretezési hőmérséklet egy 1-30 közötti szám!";
    hasError = true;
  }

  if (!payload.certificate) {
    
    if (payload.floorSize === undefined || payload.floorSize === null || payload.floorSize <= 0) {
      errors.floorSize = "A padló kerülete egy 0-nál nagyobb szám!";
      hasError = true;
    }

    if (payload.doorWallSize === undefined || payload.doorWallSize === null || payload.doorWallSize <= 0) {
      errors.doorWallSize = "A nyílászárók összfelülete egy 0-nál nagyobb szám!";
      hasError = true;
    }

    if (payload.elevation === undefined || payload.elevation === null || payload.elevation < 0 || payload.elevation > 10) {
      errors.elevation = "A magasság a talajtól egy 0-10 közötti szám!";
      hasError = true;
    }
  }

  /**
   * TODO: Validate images
   */

  return hasError ? errors : null;
}
