import type { BuildingFormData, BuildingShort } from '../model/Building.model'
import type { ComplexFormData, ComplexShortData } from '../model/Complex.model'
import type { HeaterShort } from '../model/Heater.model'
import type { LightingFormData } from '../model/Lighting.model'
import type { ClickupTaskShort } from '../model/LoginData.model'
import type { ProductFormData } from '../model/Product.model'
import type {
    StandingsFormData,
    StandingsShort,
} from '../model/Standings.model'
import {
    SystemPurpose,
    type HeatingSystemFormData,
} from '../model/System.model'
import type {
    CompressedFormData,
    CoolingFormData,
    OtherFormData,
    SteamFormData,
    TechnologyType,
} from '../model/Technology.model'
import type { VehicleFormData } from '../model/Vehicles.model'
import type { VentilationFormData } from '../model/Ventilation.model'

export default class Calls {
    /**
     *  TODO: Clickup audit table integration and call implementation
     */
    static async getClickupTasks(): Promise<{
        success: boolean
        payload: Array<ClickupTaskShort>
    }> {
        const MOCK_CLICKUP_TASKS = [
            { id: 'task_budapest_01', name: 'Budapest irodaház audit (#1234)' },
            { id: 'task_gyor_02', name: 'Győri gyáregység energetika (#5678)' },
            {
                id: 'task_debrecen_03',
                name: 'Debreceni iskola felmérés (#9012)',
            },
        ]
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log('Clickup fogadta a kérést')
                resolve({ success: true, payload: MOCK_CLICKUP_TASKS })
            }, 1500)
        })
    }

    static async postMeasurement(
        payload: StandingsFormData
    ): Promise<{ success: boolean; message: string }> {
        try {
            const response = await fetch('http://localhost:8000/standings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify(payload),
            })

            const data = await response.json()

            if (!response.ok) {
                return {
                    success: false,
                    message: data.message || 'Hiba történt a mentés során.',
                }
            }

            return {
                success: true,
                message: data.message || 'Sikeres mentés!',
            }
        } catch (error) {
            console.error('Hálózati vagy szerver hiba:', error)
            return {
                success: false,
                message: 'Nem sikerült kapcsolódni a szerverhez.',
            }
        }
    }

    /**
     * TODO: Complex backend call implementation, database implementation
     */
    static async postComplex(
        payload: ComplexFormData
    ): Promise<{ success: boolean; message: string }> {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log('Backend fogadta az adatokat:', payload)
                resolve({
                    success: true,
                    message: 'Sikeres mentés a PHP backendre!',
                })
            }, 1500)
        })
    }

    /**
     * TODO: Building backend call implementation, database implementation
     */
    static async postBuilding(
        payload: BuildingFormData
    ): Promise<{ success: boolean; message: string }> {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log('Backend fogadta az adatokat:', payload)
                resolve({
                    success: true,
                    message: 'Sikeres mentés a PHP backendre',
                })
            }, 1500)
        })
    }

    /**
     * TODO: Heating system backend call implementation, database implementation
     */
    static async postHeatingSystem(
        payload: HeatingSystemFormData
    ): Promise<{ success: boolean; message: string }> {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log('Backend fogadta az adatokat:', payload)
                resolve({
                    success: true,
                    message: 'Sikeres mentés a PHP backendre!',
                })
            }, 1500)
        })
    }

    static async postVentilationSystem(
        payload: VentilationFormData
    ): Promise<{ success: boolean; message: string }> {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log('Backend fogadta az adatokat:', payload)
                resolve({
                    success: true,
                    message: 'Sikeres mentés a PHP backendre!',
                })
            }, 1500)
        })
    }

    static async postLightingSystem(
        payload: Array<LightingFormData>
    ): Promise<{ success: boolean; message: string }> {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log('Backend fogadta az adatokat:', payload)
                resolve({
                    success: true,
                    message: 'Sikeres mentés a PHP backendre!',
                })
            }, 1500)
        })
    }

    static async postVehicle(
        payload: VehicleFormData
    ): Promise<{ success: boolean; message: string }> {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log('Backend fogadta az adatokat:', payload)
                resolve({
                    success: true,
                    message: 'Sikeres mentés a PHP backendre!',
                })
            }, 1500)
        })
    }

    static async postTechnology(
        payload:
            | CompressedFormData
            | SteamFormData
            | CoolingFormData
            | OtherFormData,
        type: TechnologyType
    ): Promise<{ success: boolean; message: string }> {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log('Backend fogadta az adatokat:', payload, type)
                resolve({
                    success: true,
                    message: 'Sikeres mentés a PHP backendre!',
                })
            }, 1500)
        })
    }

    static async postProduct(
        payload: ProductFormData
    ): Promise<{ success: boolean; message: string }> {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log('Backend fogadta az adatokat:', payload)
                resolve({
                    success: true,
                    message: 'Sikeres mentés a PHP backendre!',
                })
            }, 1500)
        })
    }

    static async getMainStandings(): Promise<{
        success: boolean
        payload: Array<StandingsShort>
    }> {
        try {
            const response = await fetch(
                'http://localhost:8000/standings?type=MAIN',
                {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                    },
                }
            )

            if (!response.ok) {
                throw new Error(`HTTP hiba! Státusz: ${response.status}`)
            }

            const data: Array<StandingsShort> = await response.json()

            return {
                success: true,
                payload: data,
            }
        } catch (error) {
            console.error('Hiba a főmérők lekérése során:', error)
            return {
                success: false,
                payload: [],
            }
        }
    }

    /**
     * TODO: Standings backend call implementation and parsing
     */
    static async getSubStandings(): Promise<{
        success: boolean
        payload: Array<StandingsShort>
    }> {
        try {
            const response = await fetch(
                'http://localhost:8000/standings?type=OTHER',
                {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                    },
                }
            )

            if (!response.ok) {
                throw new Error(`HTTP hiba! Státusz: ${response.status}`)
            }

            const data: Array<StandingsShort> = await response.json()

            return {
                success: true,
                payload: data,
            }
        } catch (error) {
            console.error('Hiba az almérők lekérése során:', error)
            return {
                success: false,
                payload: [],
            }
        }
    }

    /**
     * TODO: Complex backend call implementation and parsing
     */
    static async getComplexes(): Promise<{
        success: boolean
        payload: Array<ComplexShortData>
    }> {
        try {
            const response = await fetch('http://localhost:8000/complex', {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                },
            })

            if (!response.ok) {
                throw new Error(`HTTP hiba! Státusz: ${response.status}`)
            }

            const data: Array<ComplexShortData> = await response.json()

            return {
                success: true,
                payload: data,
            }
        } catch (error) {
            console.error('Hiba a telephelyek lekérése során:', error)
            return {
                success: false,
                payload: [],
            }
        }
    }

    static async getBuildings(): Promise<{
        success: boolean
        payload: Array<BuildingShort>
    }> {
        try {
            const response = await fetch('http://localhost:8000/buildings', {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                },
            })

            if (!response.ok) {
                throw new Error(`HTTP hiba! Státusz: ${response.status}`)
            }

            const data: Array<BuildingShort> = await response.json()

            return {
                success: true,
                payload: data,
            }
        } catch (error) {
            console.error('Hiba az épületek lekérése során:', error)
            return {
                success: false,
                payload: [],
            }
        }
    }

    static async getHeaters(): Promise<{
        success: boolean
        payload: Array<HeaterShort>
    }> {
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log('Backend fogadta a kérést')
                resolve({
                    success: true,
                    payload: [
                        {
                            id: '122',
                            name: 'Teszt fűtő 1',
                            purpose: SystemPurpose.HEAT,
                        },
                        {
                            id: '123',
                            name: 'Teszt fűtő 2',
                            purpose: SystemPurpose.HEAT,
                        },
                        {
                            id: '124',
                            name: 'Teszt hűtő 1',
                            purpose: SystemPurpose.COOL,
                        },
                        {
                            id: '125',
                            name: 'Teszt hűtő 2',
                            purpose: SystemPurpose.COOL,
                        },
                    ],
                })
            }, 1500)
        })
    }
}
