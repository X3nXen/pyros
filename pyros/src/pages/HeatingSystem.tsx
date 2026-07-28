import {
    Box,
    Button,
    Divider,
    FormControl,
    FormHelperText,
    InputLabel,
    MenuItem,
    Select,
    TextField,
    Typography,
} from '@mui/material'
import { useState } from 'react'
import {
    ElectricCalcInstallation,
    ElectricCalcMedium,
    ElectricCalcMode,
    ElectricCalcRefrigerant,
    ElectricCalcSource,
    HeaterCarrier,
    HeaterDescriptions,
    type HeaterFormData,
    type HeaterFormErrors,
} from '../model/Heater.model'
import CardListing from '../components/CardListing'
import { useAppDispatch, useAppSelector } from '../store'
import type { StandingsShort } from '../model/Standings.model'
import HeaterForm from '../components/Heater'
import {
    type HeatingSystemErrors,
    type HeatingSystemFormData,
    SystemPurpose,
    SystemRegulation,
    SystemRegulationDesc,
} from '../model/System.model'
import {
    PumpSetting,
    PumpTypes,
    type PumpErrors,
    type PumpFormData,
} from '../model/Pump.model'
import PumpForm from '../components/Pump'
import type { ServicedBuildingShort } from '../model/Building.model'
import {
    EmitterHmvRegulation,
    EmitterIndoorUnitPlacement,
    type EmitterErrors,
    type EmitterFormData,
} from '../model/Emitter.model'
import EmitterForm from '../components/Emitter'
import FormSendProtocol from '../controllers/Forms.control'
import { useNavigate } from 'react-router-dom'
import { addHeaterLocally } from '../store/projectSlice'

export default function HeatingSystem() {
    const [formData, setFormData] = useState<HeatingSystemFormData>({
        id: null,
        name: '',
        standing: null,
        systemPurpose: SystemPurpose.HEAT,
        systemRegulation: SystemRegulation.NONE,
        systemRegulationDesc: SystemRegulationDesc.NONE,
        heaters: [],
        pumps: [],
        emitters: [],
    })

    const [activeHeaterIndex, setActiveHeaterIndex] = useState<number | null>(
        null
    )
    const [activePumpIndex, setActivePumpIndex] = useState<number | null>(null)
    const [activeEmitterIndex, setActiveEmitterIndex] = useState<number | null>(
        null
    )
    const [formErrors, setFormErrors] = useState<HeatingSystemErrors | null>(
        null
    )
    const [loading, setLoading] = useState<boolean>(false)

    const buildings = useAppSelector((state) => state.project.buildings)
    const subStandings = useAppSelector((state) => state.project.subStandings)
    const mainStandings = useAppSelector((state) => state.project.mainStandings)
    const allStandings = subStandings.concat(mainStandings)
    const navigate = useNavigate()
    const dispatch = useAppDispatch()

    function handleAddHeater() {
        const newHeater: HeaterFormData = {
            id: null,
            name: `Hőtermelő ${formData.heaters.length + 1}`,
            standing: null,
            building: null,
            servicedBuilding: [],
            serial: '',
            manufacturor: '',
            year: new Date().getFullYear(),
            type: '',
            carrier: HeaterCarrier.NATURAL_GAS,
            heatingType: '',
            state: HeaterDescriptions.SERVICED,
            forwardHeat: 0,
            backHeat: 0,
            maxPower: 0,
            baseType: ElectricCalcMode.UNKNOWN,
            placementType: ElectricCalcInstallation.UNKNOWN,
            ambientMedium: ElectricCalcMedium.UNKNOWN,
            heatTransfer: ElectricCalcSource.UNKNOWN,
            refrigerant: ElectricCalcRefrigerant.UNKNOWN,
            heatLoss: false,
            couldHeatLoss: false,
            oversized: false,
            oversizeRatio: 0,
            imageIds: [],
        }

        setFormData({
            ...formData,
            heaters: [...formData.heaters, newHeater],
        })

        setActiveHeaterIndex(formData.heaters.length)
    }

    function handleAddPump() {
        const newPump: PumpFormData = {
            id: null,
            name: '',
            building: null,
            servicedBuilding: [],
            manufacturor: '',
            type: '',
            year: new Date().getFullYear(),
            archetype: PumpTypes.TYPE_A,
            archetypeSetting: PumpSetting.SET_A,
            serialNumber: '',
            powerUsage: 0,
            imageIds: [],
        }

        setFormData({ ...formData, pumps: [...formData.pumps, newPump] })
        setActivePumpIndex(formData.pumps.length)
    }

    function handleAddEmitter() {
        const newEmitter: EmitterFormData = {
            id: null,
            name: '',
            building: null,
            servicedBuilding: [],
            type: '',
            amount: 0,
            forwardHeat: 0,
            backHeat: 0,
            state: '',
            vrvRefrigerant: ElectricCalcRefrigerant.UNKNOWN,
            vrvInsideType: EmitterIndoorUnitPlacement.CEILING,
            insideRoom: false,
            circulation: false,
            circulatoryPumps: [],
            hmvRegulation: EmitterHmvRegulation.NONE,
            imageIds: [],
        }

        setFormData({
            ...formData,
            emitters: [...formData.emitters, newEmitter],
        })
        setActiveEmitterIndex(formData.emitters.length)
    }

    function handleActiveHeaterChange(
        field: keyof HeaterFormData,
        value: string | number | string[] | boolean | null
    ) {
        if (activeHeaterIndex === null) return

        const updatedHeaters = [...formData.heaters]
        updatedHeaters[activeHeaterIndex] = {
            ...updatedHeaters[activeHeaterIndex],
            [field]: value,
        }

        setFormData({
            ...formData,
            heaters: updatedHeaters,
        })
    }

    function handleActivePumpChange(
        field: keyof PumpFormData,
        value:
            | string
            | number
            | string[]
            | boolean
            | null
            | ServicedBuildingShort[]
    ) {
        if (activePumpIndex === null) return
        const updatedPumps = [...formData.pumps]
        updatedPumps[activePumpIndex] = {
            ...updatedPumps[activePumpIndex],
            [field]: value,
        }
        setFormData({ ...formData, pumps: updatedPumps })
    }

    function handleActiveEmitterChange(
        field: keyof EmitterFormData,
        value:
            | string
            | number
            | string[]
            | boolean
            | null
            | ServicedBuildingShort[]
    ) {
        if (activeEmitterIndex === null) return
        const updatedEmitters = [...formData.emitters]
        updatedEmitters[activeEmitterIndex] = {
            ...updatedEmitters[activeEmitterIndex],
            [field]: value,
        }
        setFormData({ ...formData, emitters: updatedEmitters })
    }

    const handleSubmit = async () => {
        const result = await FormSendProtocol.handleHeatingSystemForm(
            formData,
            setLoading,
            setFormErrors
        )
        if (result && result.success) {
            formData.heaters.forEach((e: HeaterFormData) => {
                dispatch(
                    addHeaterLocally({
                        id: e.id,
                        name: e.name,
                        purpose: formData.systemPurpose,
                    })
                )
            })
            navigate('/')
        }
    }

    const currentActiveHeater =
        activeHeaterIndex !== null ? formData.heaters[activeHeaterIndex] : null
    const currentActivePump =
        activePumpIndex !== null ? formData.pumps[activePumpIndex] : null
    const currentActiveEmitter =
        activeEmitterIndex !== null
            ? formData.emitters[activeEmitterIndex]
            : null

    return (
        <Box
            sx={{
                display: 'flex',
                flexDirection: 'column',
                gap: 3,
                width: '100%',
                maxWidth: 360,
                mt: 3,
            }}
        >
            <h1>Fűtő/hűtő rendszer rögzítése</h1>
            <FormControl
                variant="standard"
                fullWidth
                error={!!formErrors?.name}
            >
                <TextField
                    label="Megnevezés"
                    variant="standard"
                    value={formData.name}
                    onChange={(e) =>
                        setFormData({ ...formData, name: e.target.value })
                    }
                />
                {formErrors?.name && (
                    <FormHelperText>{formErrors?.name}</FormHelperText>
                )}
            </FormControl>

            <FormControl variant="standard" fullWidth>
                <InputLabel id="system-purpose-selector">
                    Rendszer működése
                </InputLabel>
                <Select
                    labelId="system-purpose-selector"
                    value={formData.systemPurpose || ''}
                    label="Rendszer működése"
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            systemPurpose: e.target.value as SystemPurpose,
                        })
                    }
                >
                    {Object.values(SystemPurpose).map((value) => (
                        <MenuItem key={value} value={value}>
                            {value}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>

            <FormControl
                variant="standard"
                fullWidth
                error={!!formErrors?.standing}
            >
                <InputLabel id="system-standings">Hozzárendelt mérő</InputLabel>
                <Select
                    labelId="system-standings"
                    value={formData.standing || ''}
                    label="Hozzárendelt mérő"
                    onChange={(e) =>
                        setFormData({ ...formData, standing: e.target.value })
                    }
                >
                    {allStandings.map((e: StandingsShort) => (
                        <MenuItem key={e.id} value={e.id as string}>
                            {e.name}
                        </MenuItem>
                    ))}
                </Select>
                {formErrors?.standing && (
                    <FormHelperText>{formErrors?.standing}</FormHelperText>
                )}
            </FormControl>

            <FormControl variant="standard" fullWidth>
                <InputLabel id="system-regulation-selector">
                    Rendszer beszabályozás
                </InputLabel>
                <Select
                    labelId="system-regulation-selector"
                    value={formData.systemRegulation || ''}
                    label="Rendszer beszabályozás"
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            systemRegulation: e.target
                                .value as SystemRegulation,
                        })
                    }
                >
                    {Object.values(SystemRegulation).map((value) => (
                        <MenuItem key={value} value={value}>
                            {value}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>

            <FormControl variant="standard" fullWidth>
                <InputLabel id="system-regulation-description-selector">
                    Beszabályozás leírása
                </InputLabel>
                <Select
                    labelId="system-regulation-description-selector"
                    value={formData.systemRegulationDesc || ''}
                    label="Beszabályozás leírása"
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            systemRegulationDesc: e.target
                                .value as SystemRegulationDesc,
                        })
                    }
                >
                    {Object.values(SystemRegulationDesc).map((value) => (
                        <MenuItem key={value} value={value}>
                            {value}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <Divider sx={{ my: 1 }} />

            <Typography variant="h6" component="h2">
                Hőtermelők rögzítése
            </Typography>

            <CardListing<HeaterFormData>
                items={formData.heaters}
                activeIndex={activeHeaterIndex}
                onSelect={(index) => setActiveHeaterIndex(index)}
                onAdd={handleAddHeater}
                getName={(heater) => heater.name}
            />

            {currentActiveHeater && (
                <HeaterForm
                    currentActiveHeater={currentActiveHeater}
                    handleActiveHeaterChange={handleActiveHeaterChange}
                    allStandings={allStandings}
                    buildings={buildings}
                    systemPurpose={formData.systemPurpose}
                    heaterIndex={activeHeaterIndex}
                    heaterErrors={
                        activeHeaterIndex !== null && formErrors !== null
                            ? typeof formErrors!.heaters[activeHeaterIndex!] ===
                              'string'
                                ? null
                                : (formErrors!.heaters[
                                      activeHeaterIndex!
                                  ] as HeaterFormErrors)
                            : null
                    }
                />
            )}

            <Typography variant="h6" component="h2">
                Szivattyúk rögzítése
            </Typography>

            <CardListing<PumpFormData>
                items={formData.pumps}
                activeIndex={activePumpIndex}
                onSelect={(index) => setActivePumpIndex(index)}
                onAdd={handleAddPump}
                getName={(pump) => pump.name}
            />

            {currentActivePump && (
                <PumpForm
                    currentActivePump={currentActivePump}
                    handleActivePumpChange={handleActivePumpChange}
                    buildings={buildings}
                    pumpErrors={
                        activePumpIndex !== null && formErrors !== null
                            ? typeof formErrors!.pumps[activePumpIndex!] ===
                              'string'
                                ? null
                                : (formErrors!.pumps[
                                      activePumpIndex!
                                  ] as PumpErrors)
                            : null
                    }
                />
            )}

            <Typography variant="h6" component="h2">
                Hőleadó rögzítése
            </Typography>

            <CardListing<EmitterFormData>
                items={formData.emitters}
                activeIndex={activePumpIndex}
                onSelect={(index) => setActiveEmitterIndex(index)}
                onAdd={handleAddEmitter}
                getName={(emitter) => emitter.name}
            />

            {currentActiveEmitter && (
                <EmitterForm
                    currentActiveEmitter={currentActiveEmitter}
                    handleActiveEmitterChange={handleActiveEmitterChange}
                    buildings={buildings}
                    systemPurpose={formData.systemPurpose}
                    emitterErrors={
                        activeEmitterIndex !== null && formErrors !== null
                            ? typeof formErrors!.emitters[
                                  activeEmitterIndex!
                              ] === 'string'
                                ? null
                                : (formErrors!.emitters[
                                      activeEmitterIndex!
                                  ] as EmitterErrors)
                            : null
                    }
                />
            )}

            {/**
             * TODO: Solve image uploading with linking
             */}

            <Button
                variant="contained"
                disabled={loading}
                sx={{ mt: 2 }}
                onClick={handleSubmit}
            >
                Mentés
            </Button>
        </Box>
    )
}
