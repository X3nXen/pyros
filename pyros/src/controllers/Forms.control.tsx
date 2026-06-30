import type { StandingsFormData } from "../model/Standings.model";
import Calls from "./Calls.control";
import { validateBuilding, validateComplex, validateStandings, type StandingsErrors } from "../model/Validation.model";
import type { ComplexErrors, ComplexFormData } from "../model/Complex.model";
import type { BuildingErrors, BuildingFormData } from "../model/Building.model";

export default class FormSendProtocol{
    static async handleMeasurementForm(payload: StandingsFormData, setLoading: (loading: boolean) => void, setErrorMessage: (msg: StandingsErrors | null) => void) : Promise<{success: boolean, reason: string | null}>{
        setErrorMessage(null);
        const errors = validateStandings(payload);
        if(errors){
            setErrorMessage(errors);
            return {success: false, reason: "Validation error"};
        }

        
        try{
            setLoading(true);
            payload.id = crypto.randomUUID();
            const response = await Calls.postMeasurement(payload);
            let success = false;
            let reason = null;
            if(response.success){
                success = true;
                reason = null;
            } else {
                success = false;
                reason = "Backend call error";
            }
            setLoading(false);
            return {success: success, reason: reason};
        }
        catch(error){
            alert("Valami hiba történt a hálózati kommunikáció során");
            console.error(error);
            setLoading(false);
            return {success: false, reason: "Network error"};
        }
    }

    static async handleComplexForm(payload: ComplexFormData, setLoading: (loading: boolean) => void, setErrorMessage: (msg: ComplexErrors | null) => void): Promise<{success: boolean, reason: string | null}>{
        setErrorMessage(null);
        const errors = validateComplex(payload);
        if(errors){
            setErrorMessage(errors);
            return {success: false, reason: "Validation error"};
        }

        try{
            setLoading(true);
            payload.id = crypto.randomUUID();
            const response = await Calls.postComplex(payload);
            let success = false;
            let reason = null;
            if(response.success){
                success = true;
                reason = null;
            } else {
                success = false;
                reason = "Backend call error";
            }
            setLoading(false);
            return {success: success, reason: reason};
        } catch(error){
            alert("Valami hiba történt a hálózati kommunikáció során");
            console.error(error);
            setLoading(false);
            return {success: false, reason: "Network error"};
        }
    }

    static async handleBuildingForm(payload: BuildingFormData, setLoading: (loading: boolean) => void, setErrorMessage: (msg: BuildingErrors | null) => void) : Promise<{success: boolean, reason: string | null}>{
        setErrorMessage(null);
        /**
         * TODO: validate building base data, send image to backend, set local imageids to backend response, then save
         */
        const errors = validateBuilding(payload);
        if(errors){
            setErrorMessage(errors);
            return {success: false, reason: "Validation error"};
        }

        try{
            setLoading(true);
            payload.id = crypto.randomUUID();
            const response = await Calls.postBuilding(payload);
            let success = false;
            let reason = null;
            if(response.success){
                success = true;
                reason = null;
            } else {
                success = false;
                reason = "Backend call error";
            }
            setLoading(false);
            return {success: success, reason: reason};
        } catch(error){
            alert("Valami hiba történt a hálózati kommunikáció során");
            console.error(error);
            setLoading(false);
            return {success: false, reason: "Network error"};
        }
    }
}