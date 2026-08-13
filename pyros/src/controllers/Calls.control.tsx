import type { BuildingFormData, BuildingShort } from '../model/Building.model'
import type { ComplexFormData, ComplexShortData } from '../model/Complex.model'
import type { HeaterFormData, HeaterShort } from '../model/Heater.model'
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
import {
    TechnologyType,
    type CompressedFormData,
    type CoolingFormData,
    type OtherFormData,
    type SteamFormData,
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
        payload: StandingsFormData & {
            excelFile?: File | null
            file?: File | null
        }
    ): Promise<{ success: boolean; message: string; id?: string }> {
        try {
            const formData = new FormData()

            const { excelFile, file, ...restPayload } = payload
            const targetFile = excelFile || file

            if (targetFile) {
                formData.append('excel', targetFile)
            }

            formData.append('data', JSON.stringify(restPayload))

            const response = await fetch('http://localhost:8000/standings', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: formData,
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
                id: data.id,
            }
        } catch (error) {
            console.error('Hálózati vagy szerver hiba:', error)
            return {
                success: false,
                message: 'Nem sikerült kapcsolódni a szerverhez.',
            }
        }
    }

    static async postComplex(
        payload: ComplexFormData
    ): Promise<{ success: boolean; message: string; id?: string }> {
        try {
            const response = await fetch('http://localhost:8000/complex', {
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
                id: data.id,
            }
        } catch (error) {
            console.error('Hálózati vagy szerver hiba:', error)
            return {
                success: false,
                message: 'Nem sikerült kapcsolódni a szerverhez.',
            }
        }
    }

    static async postBuilding(payload: BuildingFormData): Promise<{
        success: boolean
        message: string
        id?: string
    }> {
        try {
            const formData = new FormData()
            const { imageFile, ...restOfPayload } = payload

            formData.append('data', JSON.stringify(restOfPayload))

            if (imageFile instanceof File) {
                formData.append('imageFile', imageFile)
            }

            const response = await fetch('http://localhost:8000/buildings', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: formData,
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
                id: data.id,
            }
        } catch (error) {
            console.error('Hálózati vagy szerver hiba:', error)
            return {
                success: false,
                message: 'Nem sikerült kapcsolódni a szerverhez.',
            }
        }
    }

    static async postHeatingSystem(
        payload: HeatingSystemFormData
    ): Promise<{ success: boolean; message: string }> {
        try {
            const formData = new FormData()

            payload.heaters?.forEach((heater, index) => {
                if (heater.imageFile instanceof File) {
                    formData.append(
                        `heaters_${index}_imageFile`,
                        heater.imageFile
                    )
                }
            })

            payload.pumps?.forEach((pump, index) => {
                if (pump.imageFile instanceof File) {
                    formData.append(`pumps_${index}_imageFile`, pump.imageFile)
                }
            })

            payload.emitters?.forEach((emitter, index) => {
                if (emitter.imageFile instanceof File) {
                    formData.append(
                        `emitters_${index}_imageFile`,
                        emitter.imageFile
                    )
                }
            })

            formData.append('data', JSON.stringify(payload))

            const response = await fetch('http://localhost:8000/heating', {
                method: 'POST',
                body: formData,
            })

            const result = await response.json()

            if (!response.ok || result.status === 'error') {
                return {
                    success: false,
                    message: result.message || 'Hiba történt a mentés során.',
                }
            }

            return {
                success: true,
                message: result.message || 'Sikeres mentés a PHP backendre!',
            }
        } catch (error) {
            console.error('Hálózati hiba a mentés során:', error)
            return {
                success: false,
                message: 'Nem sikerült kapcsolódni a szerverhez.',
            }
        }
    }

    static async postVentilationSystem(
        payload: VentilationFormData
    ): Promise<{ success: boolean; message: string }> {
        try {
            const formData = new FormData()

            if (payload.firstImage instanceof File) {
                formData.append(`ventilation_0_imageFile`, payload.firstImage)
            }
            if (payload.secondImage instanceof File) {
                formData.append(`ventilation_1_imageFile`, payload.secondImage)
            }
            if (payload.thirdImage instanceof File) {
                formData.append(`ventilation_2_imageFile`, payload.thirdImage)
            }
            formData.append('data', JSON.stringify(payload))

            const response = await fetch('http://localhost:8000/ventilation', {
                method: 'POST',
                body: formData,
            })

            const result = await response.json()

            if (!response.ok || result.status === 'error') {
                return {
                    success: false,
                    message: result.message || 'Hiba történt a mentés során.',
                }
            }

            return {
                success: true,
                message: result.message || 'Sikeres mentés a PHP backendre!',
            }
        } catch (error) {
            console.error('Hálózati hiba a mentés során:', error)
            return {
                success: false,
                message: 'Nem sikerült kapcsolódni a szerverhez.',
            }
        }
    }

    static async postLightingSystem(
        payload: Array<LightingFormData>
    ): Promise<{ success: boolean; message: string }> {
        try {
            const response = await fetch('http://localhost:8000/lighting', {
                method: 'POST',
                body: JSON.stringify(payload),
            })

            const result = await response.json()

            if (!response.ok || result.status === 'error') {
                return {
                    success: false,
                    message: result.message || 'Hiba történt a mentés során.',
                }
            }

            return {
                success: true,
                message: result.message || 'Sikeres mentés a PHP backendre!',
            }
        } catch (error) {
            console.error('Hálózati hiba a mentés során:', error)
            return {
                success: false,
                message: 'Nem sikerült kapcsolódni a szerverhez.',
            }
        }
    }

    static async postVehicle(
        payload: VehicleFormData
    ): Promise<{ success: boolean; message: string }> {
        try {
            const response = await fetch('http://localhost:8000/vehicle', {
                method: 'POST',
                body: JSON.stringify(payload),
            })

            const result = await response.json()

            if (!response.ok || result.status === 'error') {
                return {
                    success: false,
                    message: result.message || 'Hiba történt a mentés során.',
                }
            }

            return {
                success: true,
                message: result.message || 'Sikeres mentés a PHP backendre!',
            }
        } catch (error) {
            console.error('Hálózati hiba a mentés során:', error)
            return {
                success: false,
                message: 'Nem sikerült kapcsolódni a szerverhez.',
            }
        }
    }

    static async postTechnology(
        payload:
            | CompressedFormData
            | SteamFormData
            | CoolingFormData
            | OtherFormData,
        type: TechnologyType
    ): Promise<{ success: boolean; message: string }> {
        const appendedPayload = {
            ...payload,
            technologyType: type,
        }
        try {
            const response = await fetch('http://localhost:8000/technology', {
                method: 'POST',
                body: JSON.stringify(appendedPayload),
            })

            const result = await response.json()

            if (!response.ok || result.status === 'error') {
                return {
                    success: false,
                    message: result.message || 'Hiba történt a mentés során.',
                }
            }

            return {
                success: true,
                message: result.message || 'Sikeres mentés a PHP backendre!',
            }
        } catch (error) {
            console.error('Hálózati hiba a mentés során:', error)
            return {
                success: false,
                message: 'Nem sikerült kapcsolódni a szerverhez.',
            }
        }
    }

    static async postProduct(
        payload: ProductFormData & {
            excelFile?: File | null
            file?: File | null
        }
    ): Promise<{ success: boolean; message: string; id?: string }> {
        try {
            const formData = new FormData()

            const { excelFile, file, ...restPayload } = payload
            const targetFile = excelFile || file

            if (targetFile) {
                formData.append('excel', targetFile)
            }

            formData.append('data', JSON.stringify(restPayload))

            const response = await fetch('http://localhost:8000/product', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: formData,
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
                id: data.id,
            }
        } catch (error) {
            console.error('Hálózati vagy szerver hiba:', error)
            return {
                success: false,
                message: 'Nem sikerült kapcsolódni a szerverhez.',
            }
        }
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
                'http://localhost:8000/standings?type=SUB',
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
        try {
            const response = await fetch('http://localhost:8000/heating', {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                },
            })

            if (!response.ok) {
                throw new Error(`HTTP hiba! Státusz: ${response.status}`)
            }

            const data = await response.json()

            const purposeMap: Record<string, SystemPurpose> = {
                HEAT: SystemPurpose.HEAT,
                COOL: SystemPurpose.COOL,
                BOTH: SystemPurpose.BOTH,
            }

            const heaters: Array<HeaterShort> = data.flatMap(
                (system: { purpose: SystemPurpose; heaters: string | [] }) => {
                    const parsedHeaters =
                        typeof system.heaters === 'string'
                            ? JSON.parse(system.heaters)
                            : system.heaters || []

                    return parsedHeaters.map((h: HeaterFormData) => ({
                        id: h.id,
                        name: h.name,
                        purpose: purposeMap[system.purpose] ?? system.purpose,
                    }))
                }
            )
            console.log(heaters)
            return {
                success: true,
                payload: heaters,
            }
        } catch (error) {
            console.error('Hiba a hőtermelők lekérése során:', error)
            return {
                success: false,
                payload: [],
            }
        }
    }
}
