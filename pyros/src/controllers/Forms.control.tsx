import type { StandingsFormData } from '../model/Standings.model'
import Calls from './Calls.control'
import {
    validateBuilding,
    validateComplex,
    validateCompressed,
    validateCooling,
    validateHeatingSystem,
    validateLightingSystem,
    validateOther,
    validateStandings,
    validateSteam,
    validateVehicle,
    validateVentilationSystem,
    type StandingsErrors,
} from '../model/Validation.model'
import type { ComplexErrors, ComplexFormData } from '../model/Complex.model'
import type { BuildingErrors, BuildingFormData } from '../model/Building.model'
import type {
    HeatingSystemErrors,
    HeatingSystemFormData,
} from '../model/System.model'
import type {
    VentilationFormData,
    VentilationFormErrors,
} from '../model/Ventilation.model'
import type { LightingErrors, LightingFormData } from '../model/Lighting.model'
import type { VehicleErrors, VehicleFormData } from '../model/Vehicles.model'
import type { ProductFormData } from '../model/Product.model'
import {
    TechnologyType,
    type CompressedFormData,
    type CompressorFormErrors,
    type CoolingErrors,
    type CoolingFormData,
    type OtherErrors,
    type OtherFormData,
    type SteamErrors,
    type SteamFormData,
} from '../model/Technology.model'

export default class FormSendProtocol {
    static async handleMeasurementForm(
        payload: StandingsFormData,
        setLoading: (loading: boolean) => void,
        setErrorMessage: (msg: StandingsErrors | null) => void
    ): Promise<{ success: boolean; reason: string | null }> {
        setErrorMessage(null)
        const errors = validateStandings(payload)
        if (errors) {
            setErrorMessage(errors)
            console.log('Ide jön be inkább csak nem ad errort', errors)
            return { success: false, reason: 'Validation error' }
        }

        console.log('Ide bejön')

        try {
            setLoading(true)
            payload.id = crypto.randomUUID()
            const response = await Calls.postMeasurement(payload)
            let success = false
            let reason = null
            if (response.success) {
                success = true
                reason = null
            } else {
                success = false
                reason = 'Backend call error'
            }
            setLoading(false)
            return { success: success, reason: reason }
        } catch (error) {
            alert('Valami hiba történt a hálózati kommunikáció során')
            console.error(error)
            setLoading(false)
            return { success: false, reason: 'Network error' }
        }
    }

    static async handleComplexForm(
        payload: ComplexFormData,
        setLoading: (loading: boolean) => void,
        setErrorMessage: (msg: ComplexErrors | null) => void
    ): Promise<{ success: boolean; reason: string | null }> {
        setErrorMessage(null)
        const errors = validateComplex(payload)
        if (errors) {
            setErrorMessage(errors)
            return { success: false, reason: 'Validation error' }
        }

        try {
            setLoading(true)
            payload.id = crypto.randomUUID()
            const response = await Calls.postComplex(payload)
            let success = false
            let reason = null
            if (response.success) {
                success = true
                reason = null
            } else {
                success = false
                reason = 'Backend call error'
            }
            setLoading(false)
            return { success: success, reason: reason }
        } catch (error) {
            alert('Valami hiba történt a hálózati kommunikáció során')
            console.error(error)
            setLoading(false)
            return { success: false, reason: 'Network error' }
        }
    }

    static async handleBuildingForm(
        payload: BuildingFormData,
        setLoading: (loading: boolean) => void,
        setErrorMessage: (msg: BuildingErrors | null) => void
    ): Promise<{ success: boolean; reason: string | null }> {
        setErrorMessage(null)
        /**
         * TODO: validate building base data, send image to backend, set local imageids to backend response, then save
         */
        const errors = validateBuilding(payload)
        if (errors) {
            setErrorMessage(errors)
            return { success: false, reason: 'Validation error' }
        }

        try {
            setLoading(true)
            payload.id = crypto.randomUUID()
            const response = await Calls.postBuilding(payload)
            let success = false
            let reason = null
            if (response.success) {
                success = true
                reason = null
            } else {
                success = false
                reason = 'Backend call error'
            }
            setLoading(false)
            return { success: success, reason: reason }
        } catch (error) {
            alert('Valami hiba történt a hálózati kommunikáció során')
            console.error(error)
            setLoading(false)
            return { success: false, reason: 'Network error' }
        }
    }

    static async handleHeatingSystemForm(
        payload: HeatingSystemFormData,
        setLoading: (loading: boolean) => void,
        setErrorMessage: (msg: HeatingSystemErrors | null) => void
    ): Promise<{ success: boolean; reason: string | null }> {
        setErrorMessage(null)
        /**
         * TODO: validate heaters pumps emitters base data, send image to backend, set local imageids to backend response, then save
         */
        const errors = validateHeatingSystem(payload)
        if (errors) {
            setErrorMessage(errors)
            return { success: false, reason: 'Validation error' }
        }

        try {
            setLoading(true)
            payload.id = crypto.randomUUID()
            const response = await Calls.postHeatingSystem(payload)
            let success = false
            let reason = null
            if (response.success) {
                success = true
                reason = null
            } else {
                success = false
                reason = 'Backend call error'
            }
            setLoading(false)
            return { success: success, reason: reason }
        } catch (error) {
            alert('Valami hiba történt a hálózati kommunikáció során')
            console.error(error)
            setLoading(false)
            return { success: false, reason: 'Network error' }
        }
    }

    static async handleVentilationSystem(
        payload: VentilationFormData,
        setLoading: (loading: boolean) => void,
        setErrorMessage: (msg: VentilationFormErrors | null) => void
    ) {
        setErrorMessage(null)
        /**
         * TODO: send image, get back id, write in imageids
         */
        const errors = validateVentilationSystem(payload)
        if (errors) {
            setErrorMessage(errors)
            return { success: false, reason: 'Validation error' }
        }

        try {
            setLoading(true)
            payload.id = crypto.randomUUID()
            const response = await Calls.postVentilationSystem(payload)
            let success = false
            let reason = null
            if (response.success) {
                success = true
                reason = null
            } else {
                success = false
                reason = 'Backend call error'
            }
            setLoading(false)
            return { success: success, reason: reason }
        } catch (error) {
            alert('Valami hiba történt a hálózati kommunikáció során')
            console.error(error)
            setLoading(false)
            return { success: false, reason: 'Network error' }
        }
    }

    static async handleLightingSystem(
        payload: Array<LightingFormData>,
        setLoading: (loading: boolean) => void,
        setErrorMessage: (msg: Array<string | LightingErrors> | null) => void
    ) {
        setErrorMessage(null)
        const errors = validateLightingSystem(payload)
        if (errors) {
            setErrorMessage(errors)
            return { success: false, reason: 'Validation error' }
        }

        try {
            setLoading(true)
            const response = await Calls.postLightingSystem(payload)
            let success = false
            let reason = null
            if (response.success) {
                success = true
                reason = null
            } else {
                success = false
                reason = 'Backend call error'
            }
            setLoading(false)
            return { success: success, reason: reason }
        } catch (error) {
            alert('Valami hiba történt a hálózati kommunikáció során')
            console.error(error)
            setLoading(false)
            return { success: false, reason: 'Network error' }
        }
    }

    static async handleVehicle(
        payload: VehicleFormData,
        setLoading: (loading: boolean) => void,
        setErrorMessage: (msg: VehicleErrors | null) => void
    ) {
        setErrorMessage(null)
        const errors = validateVehicle(payload)
        if (errors) {
            setErrorMessage(errors)
            return { success: false, reason: 'Validation error' }
        }

        try {
            setLoading(true)
            const response = await Calls.postVehicle(payload)
            let success = false
            let reason = null
            if (response.success) {
                success = true
                reason = null
            } else {
                success = false
                reason = 'Backend call error'
            }
            setLoading(false)
            return { success: success, reason: reason }
        } catch (error) {
            alert('Valami hiba történt a hálózati kommunikáció során')
            console.error(error)
            setLoading(false)
            return { success: false, reason: 'Network error' }
        }
    }

    static async handleCompressor(
        payload: CompressedFormData,
        setLoading: (loading: boolean) => void,
        setErrorMessage: (msg: CompressorFormErrors | null) => void
    ) {
        setErrorMessage(null)
        const errors = validateCompressed(payload)
        if (errors) {
            setErrorMessage(errors)
            return { success: false, reason: 'Validation error' }
        }

        try {
            setLoading(true)
            const response = await Calls.postTechnology(
                payload,
                TechnologyType.COMPRESSED_AIR
            )
            let success = false
            let reason = null
            if (response.success) {
                success = true
                reason = null
            } else {
                success = false
                reason = 'Backend call error'
            }
            setLoading(false)
            return { success: success, reason: reason }
        } catch (error) {
            alert('Valami hiba történt a hálózati kommunikáció során')
            console.error(error)
            setLoading(false)
            return { success: false, reason: 'Network error' }
        }
    }

    static async handleSteam(
        payload: SteamFormData,
        setLoading: (loading: boolean) => void,
        setErrorMessage: (msg: SteamErrors | null) => void
    ) {
        setErrorMessage(null)
        const errors = validateSteam(payload)
        if (errors) {
            setErrorMessage(errors)
            return { success: false, reason: 'Validation error' }
        }

        try {
            setLoading(true)
            const response = await Calls.postTechnology(
                payload,
                TechnologyType.STEAM
            )
            let success = false
            let reason = null
            if (response.success) {
                success = true
                reason = null
            } else {
                success = false
                reason = 'Backend call error'
            }
            setLoading(false)
            return { success: success, reason: reason }
        } catch (error) {
            alert('Valami hiba történt a hálózati kommunikáció során')
            console.error(error)
            setLoading(false)
            return { success: false, reason: 'Network error' }
        }
    }

    static async handleCooling(
        payload: CoolingFormData,
        setLoading: (loading: boolean) => void,
        setErrorMessage: (msg: CoolingErrors | null) => void
    ) {
        setErrorMessage(null)
        const errors = validateCooling(payload)
        if (errors) {
            setErrorMessage(errors)
            return { success: false, reason: 'Validation error' }
        }

        try {
            setLoading(true)
            const response = await Calls.postTechnology(
                payload,
                TechnologyType.COOLING
            )
            let success = false
            let reason = null
            if (response.success) {
                success = true
                reason = null
            } else {
                success = false
                reason = 'Backend call error'
            }
            setLoading(false)
            return { success: success, reason: reason }
        } catch (error) {
            alert('Valami hiba történt a hálózati kommunikáció során')
            console.error(error)
            setLoading(false)
            return { success: false, reason: 'Network error' }
        }
    }

    static async handleOther(
        payload: OtherFormData,
        setLoading: (loading: boolean) => void,
        setErrorMessage: (msg: OtherErrors | null) => void
    ) {
        setErrorMessage(null)
        const errors = validateOther(payload)
        if (errors) {
            setErrorMessage(errors)
            return { success: false, reason: 'Validation error' }
        }

        try {
            setLoading(true)
            const response = await Calls.postTechnology(
                payload,
                TechnologyType.OTHER
            )
            let success = false
            let reason = null
            if (response.success) {
                success = true
                reason = null
            } else {
                success = false
                reason = 'Backend call error'
            }
            setLoading(false)
            return { success: success, reason: reason }
        } catch (error) {
            alert('Valami hiba történt a hálózati kommunikáció során')
            console.error(error)
            setLoading(false)
            return { success: false, reason: 'Network error' }
        }
    }
    static async handleProduct(
        payload: ProductFormData,
        setLoading: (loading: boolean) => void,
        setErrorMessage: (msg: string | null) => void
    ) {
        setErrorMessage(null)
        const errors = payload.file === null ? 'Töltsd fel az adatot!' : null
        if (errors) {
            setErrorMessage(errors)
            return { success: false, reason: 'Validation error' }
        }

        try {
            setLoading(true)
            const response = await Calls.postProduct(payload)
            let success = false
            let reason = null
            if (response.success) {
                success = true
                reason = null
            } else {
                success = false
                reason = 'Backend call error'
            }
            setLoading(false)
            return { success: success, reason: reason }
        } catch (error) {
            alert('Valami hiba történt a hálózati kommunikáció során')
            console.error(error)
            setLoading(false)
            return { success: false, reason: 'Network error' }
        }
    }
}
