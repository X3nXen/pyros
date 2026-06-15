import type { StandingsFormData } from "../model/Sources.model";

export default class Calls{
    static async postMeasurement(payload: StandingsFormData): Promise<{ success: boolean; message: string }>{
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log("Backend fogadta az adatokat:", payload);
                resolve({ success: true, message: "Sikeres mentés a PHP backendre!" });
            }, 1500);
        });
    }
}