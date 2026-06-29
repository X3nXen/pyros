import type { ComplexFormData } from "../model/Complex.model";
import type { StandingsFormData } from "../model/Standings.model";

export default class Calls{
    /**
    * TODO: Standings backend call implementation, database implementation
    **/
    static async postMeasurement(payload: StandingsFormData): Promise<{ success: boolean; message: string }>{
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log("Backend fogadta az adatokat:", payload);
                resolve({ success: true, message: "Sikeres mentés a PHP backendre!" });
            }, 1500);
        });
    }

    /**
     * TODO: Complex backend call implementation, database implementation
     */
    static async postComplex(payload: ComplexFormData): Promise<{success: boolean; message: string;}>{
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log("Backend fogadta az adatokat:", payload);
                resolve({success: true, message: "Sikeres mentés a PHP backendre!"});
            }, 1500)
        })
    }

    /**
     * TODO: Standings backend call implementation and parsing
     */
    static async getMainStandings(): Promise<{success: boolean, payload: Array<{id: string, name: string}>}> {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log("Backend mock response ok");
                resolve({success: true, payload: [{id: "1225", name: "test főmérő"}, {id: "1223", name: "test főmérő2"}, {id: "1224", name: "test főmérő3"}]});
            }, 1500)
        })
    }
}