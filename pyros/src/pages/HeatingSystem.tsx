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
    COOL_CARRIER_TO_TYPE,
    ElectricCalcInstallation,
    ElectricCalcMedium,
    ElectricCalcMode,
    ElectricCalcRefrigerant,
    ElectricCalcSource,
    HEAT_CARRIER_TO_TYPE,
    HeaterCarrier,
    HeaterDescriptions,
    HeaterRegulations,
    HeaterType,
    PurposeToCarrier,
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
        systemRegulation: 'NONE' as SystemRegulation,
        systemRegulationDesc: 'NONE' as SystemRegulationDesc,
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
    const projectId =
        useAppSelector((state) => state.project.currentTaskId) ?? ''

    function handleAddHeater() {
        const currentPurpose = formData.systemPurpose
        const defaultCarrier =
            PurposeToCarrier[currentPurpose]?.[0] ?? HeaterCarrier.NATURAL_GAS

        const defaultHeatingType: HeaterType =
            formData.systemPurpose === SystemPurpose.BOTH
                ? (Array.from(
                      new Set([
                          ...(HEAT_CARRIER_TO_TYPE[defaultCarrier] ?? []),
                          ...(COOL_CARRIER_TO_TYPE[defaultCarrier] ?? []),
                      ])
                  )[0] ?? HeaterType.OTHER)
                : formData.systemPurpose === SystemPurpose.HEAT
                  ? (HEAT_CARRIER_TO_TYPE[defaultCarrier]?.[0] ??
                    HeaterType.OTHER)
                  : (COOL_CARRIER_TO_TYPE[defaultCarrier]?.[0] ??
                    COOL_CARRIER_TO_TYPE[HeaterCarrier.NATURAL_GAS]?.[0] ??
                    HeaterType.OTHER)
        const newHeater: HeaterFormData = {
            id: null,
            name: `Hőtermelő ${formData.heaters.length + 1}`,
            standing: null,
            building: null,
            servicedBuilding: [],
            regulation: HeaterRegulations[0],
            carrier: defaultCarrier,
            heatingType: defaultHeatingType,
            state: 'SERVICED' as HeaterDescriptions,
            baseType: 'UNKNOWN' as ElectricCalcMode,
            placementType: 'UNKNOWN' as ElectricCalcInstallation,
            ambientMedium: 'UNKNOWN' as ElectricCalcMedium,
            heatTransfer: 'UNKNOWN' as ElectricCalcSource,
            refrigerant: 'UNKNOWN' as ElectricCalcRefrigerant,
            heatLoss: false,
            couldHeatLoss: false,
            imageFile: null,
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
            archetype: 'TYPE_A' as PumpTypes,
            archetypeSetting: 'SET_A' as PumpSetting,
            imageFile: null,
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
            state: '',
            vrvInsideType: 'CEILING' as EmitterIndoorUnitPlacement,
            insideRoom: false,
            circulation: false,
            circulatoryPumps: [],
            hmvRegulation: 'NONE' as EmitterHmvRegulation,
            imageFile: null,
        }

        setFormData({
            ...formData,
            emitters: [...formData.emitters, newEmitter],
        })
        setActiveEmitterIndex(formData.emitters.length)
    }

    function handleActiveHeaterChange(
        field: keyof HeaterFormData,
        value: string | number | string[] | boolean | File | null
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
            | File
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
            | File
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
            setFormErrors,
            projectId
        )
        console.log(formData)
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
                            {value}{' '}
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
                        <MenuItem
                            key={'standing-' + e.id}
                            value={e.id as string}
                        >
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
                            systemRegulation: e.target.value,
                        })
                    }
                >
                    {Object.keys(SystemRegulation).map((value: string) => (
                        <MenuItem value={value}>
                            {
                                SystemRegulation[
                                    value as keyof typeof SystemRegulation
                                ]
                            }
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
                            systemRegulationDesc: e.target.value,
                        })
                    }
                >
                    {Object.keys(SystemRegulationDesc).map((value: string) => (
                        <MenuItem value={value}>
                            {
                                SystemRegulationDesc[
                                    value as keyof typeof SystemRegulationDesc
                                ]
                            }
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
