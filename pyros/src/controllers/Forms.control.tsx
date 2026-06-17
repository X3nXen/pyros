import type { NavigateFunction } from "react-router-dom";
import type { StandingsFormData } from "../model/Standings.model";
import Calls from "./Calls.control";
import { validateStandings, type StandingsErrors } from "../model/Validation.model";

export default class FormSendProtocol{
    static async handleMeasurementForm(payload: StandingsFormData, navigate: NavigateFunction, setLoading: (loading: boolean) => void, setErrorMessage: (msg: StandingsErrors | null) => void){
        setErrorMessage(null);
        const errors = validateStandings(payload);
        if(errors){
            setErrorMessage(errors);
            return;
        }

        try{
            setLoading(true);
            const response = await Calls.postMeasurement(payload);
            if(response.success){
                navigate("/")
            } else {
                return;
            }
        }
        catch(error){
            alert("Valami hiba történt a hálózati kommunikáció során");
            console.error(error);
        } finally {
            setLoading(false);
        }
    }
}