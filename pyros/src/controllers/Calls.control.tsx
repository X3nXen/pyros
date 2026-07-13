import type { BuildingFormData, BuildingShort } from "../model/Building.model";
import type { ComplexFormData, ComplexShortData } from "../model/Complex.model";
import type { ClickupTaskShort } from "../model/LoginData.model";
import type {
  StandingsFormData,
  StandingsShort,
} from "../model/Standings.model";
import type { HeatingSystemFormData } from "../model/System.model";
import type { VentilationFormData } from "../model/Ventilation.model";

export default class Calls {

/**
 *  TODO: Clickup audit table integration and call implementation
 */
  static async getClickupTasks(): Promise<{
    success: boolean;
    payload: Array<ClickupTaskShort>;
  }> {
    const MOCK_CLICKUP_TASKS = [
      { id: "task_budapest_01", name: "Budapest irodaház audit (#1234)" },
      { id: "task_gyor_02", name: "Győri gyáregység energetika (#5678)" },
      { id: "task_debrecen_03", name: "Debreceni iskola felmérés (#9012)" },
    ];
    return new Promise((resolve) => {
      setTimeout(() => {
        console.log("Clickup fogadta a kérést");
        resolve({ success: true, payload: MOCK_CLICKUP_TASKS });
      }, 1500);
    });
  }

  /**
   * TODO: Standings backend call implementation, database implementation
   **/
  static async postMeasurement(
    payload: StandingsFormData,
  ): Promise<{ success: boolean; message: string }> {
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
  static async postComplex(
    payload: ComplexFormData,
  ): Promise<{ success: boolean; message: string }> {
    return new Promise((resolve) => {
      setTimeout(() => {
        console.log("Backend fogadta az adatokat:", payload);
        resolve({ success: true, message: "Sikeres mentés a PHP backendre!" });
      }, 1500);
    });
  }

/**
 * TODO: Building backend call implementation, database implementation
 */
  static async postBuilding(payload: BuildingFormData): Promise<{success: boolean, message: string}> {
    return new Promise((resolve) => {
        setTimeout(() => {
            console.log("Backend fogadta az adatokat:", payload);
            resolve({success: true, message: "Sikeres mentés a PHP backendre"});
        }, 1500)
    })
  }

  /**
   * TODO: Heating system backend call implementation, database implementation
   */
  static async postHeatingSystem(payload: HeatingSystemFormData): Promise<{success: boolean, message: string}> {
    return new Promise((resolve) => {
      setTimeout(() => {
        console.log("Backend fogadta az adatokat:", payload);
        resolve({success: true, message: "Sikeres mentés a PHP backendre!"});
      }, 1500)
    })
  }

  static async postVentilationSystem(payload: VentilationFormData): Promise<{success: boolean, message: string}>{
    return new Promise((resolve) => {
      setTimeout(()=> {
        console.log("Backend fogadta az adatokat:", payload);
        resolve({success: true, message: "Sikeres mentés a PHP backendre!"})
      }, 1500)
    })
  }

  /**
   * TODO: Standings backend call implementation and parsing
   */
  static async getMainStandings(): Promise<{
    success: boolean;
    payload: Array<StandingsShort>;
  }> {
    return new Promise((resolve) => {
      setTimeout(() => {
        console.log("Backend mock response ok");
        resolve({
          success: true,
          payload: [
            { id: "1225", name: "test főmérő" },
            { id: "1223", name: "test főmérő2" },
            { id: "1224", name: "test főmérő3" },
          ],
        });
      }, 1500);
    });
  }

  /**
   * TODO: Standings backend call implementation and parsing
   */
  static async getSubStandings(): Promise<{
    success: boolean;
    payload: Array<StandingsShort>;
  }> {
    return new Promise((resolve) => {
      setTimeout(() => {
        console.log("Backend mock response ok");
        resolve({
          success: true,
          payload: [
            { id: "1333", name: "test almérő" },
            { id: "1334", name: "test almérő2" },
            { id: "1335", name: "test virtuális mérő 1" },
          ],
        });
      }, 1500);
    });
  }

  /**
   * TODO: Complex backend call implementation and parsing
   */
  static async getComplexes(): Promise<{
    success: boolean;
    payload: Array<ComplexShortData>;
  }> {
    return new Promise((resolve) => {
      setTimeout(() => {
        console.log("Backend mock response ok");
        resolve({
          success: true,
          payload: [
            { id: "17", name: "Martonvásárhelyi teszt telephely" },
            { id: "18", name: "Budapesti teszt telephely" },
          ],
        });
      }, 1500);
    });
  }
/**
   * TODO: Building backend call implementation and parsing
   */
  static async getBuildings(): Promise<{
    success: boolean;
    payload: Array<BuildingShort>;
  }> {
    return new Promise((resolve) => {
      setTimeout(() => {
        console.log("Backend mock response ok");
        resolve({
          success: true,
          payload: [
            { id: "44", name: "A tesz épület" },
            { id: "45", name: "B teszt épület" },
          ],
        });
      }, 1500);
    });
  }
}
