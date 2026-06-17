import { MeasurementTypes, type StandingsFormData } from "./Sources.model";

export interface StandingsErrors {
  measurementType: string | null;
  subTo: string;
  source: string | null;
  measurement: string | null;
  dateFrom: string | null;
  dateTo: string | null;
  file: string | null;
}

export function validateStandings(payload: StandingsFormData): StandingsErrors | null{
    const errors: StandingsErrors = {
        measurementType: "",
        subTo: "",
        source: "",
        measurement: "",
        dateFrom: "",
        dateTo: "",
        file: ""
    };
    let hasError = false;
    if(!payload.measurementType){
        errors.measurementType = "A mérés típus megadása kötelező!";
        hasError = true;
    }

    if(payload.measurementType && payload.measurementType !== MeasurementTypes.MAIN && !payload.subTo){
        errors.subTo = "A főmérő hozzárendelése kötelező!";
        hasError = true;
    }

    if(!payload.source){
        errors.source = "A mért jellemző megadása kötelező!";
        hasError = true;
    }

    if(!payload.measurement){
        errors.measurement = "A mértékegység megadása kötelező!";
        hasError = true;
    }


    if(!payload.dateFrom){
        errors.dateFrom = "A -tól dátum megadása kötelező!"
        hasError = true;
    }

    if(!payload.dateTo){
        errors.dateTo = "A -ig dátum megadása kötelező!";
        hasError = true;
    }

    if (payload.dateFrom && payload.dateTo) {
        if (payload.dateFrom.isAfter(payload.dateTo)) {
            errors.dateFrom = errors.dateTo = "A kezdő dátum nem lehet később, mint a végdátum!";
            hasError = true;
        } else if (payload.dateTo.diff(payload.dateFrom, "month") < 12) {
            errors.dateFrom = errors.dateTo = "A kezdő és vég dátum között legalább 12 hónapnak kell lennie!";
            hasError = true;
        }
    }

    if(!payload.file){
        errors.file = "A kimutatás fájl feltöltése kötelező!";
        hasError = true;
    }

    return hasError ? errors : null;
}