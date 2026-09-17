import { useState } from 'react'
import {
    LightingDim,
    LightingNaturalLightRatio,
    LightingRegulation,
    LightingSolutions,
    ZoneUsage,
    type LightingErrors,
    type LightingForm,
    type LightingFormData,
    type SystemErrors,
} from '../model/Lighting.model'
import {
    Box,
    Button,
    FormControl,
    FormHelperText,
    InputLabel,
    MenuItem,
    Select,
    TextField,
} from '@mui/material'
import { useNavigate } from 'react-router-dom'
import CardListing from '../components/CardListing'
import FormSendProtocol from '../controllers/Forms.control'
import { useAppSelector } from '../store'
import type { StandingsShort } from '../model/Standings.model'
import type { ComplexShortData } from '../model/Complex.model'
import type { BuildingShort } from '../model/Building.model'

export default function LightingSystem() {
    const [formData, setFormData] = useState<LightingForm>({
        complex: '',
        systems: [],
    })
    const [formErrors, setFormErrors] = useState<SystemErrors | null>(null)
    const [activeLightingIndex, setActiveLightingIndex] = useState<
        number | null
    >(null)
    const [loading, setLoading] = useState<boolean>(false)
    const navigate = useNavigate()
    const projectId =
        useAppSelector((state) => state.project.currentTaskId) ?? ''
    const complexes = useAppSelector((state) => state.project.complexes)

    function handleAddLightingSystem() {
        const newLightingSystem: Array<LightingFormData> = [
            ...formData.systems,
            {
                id: null,
                building: '',
                zone: '',
                size: 0,
                solution: LightingSolutions[0],
                dim: LightingDim[0],
                zoneUsage: ZoneUsage[0],
                regulation: LightingRegulation[0],
                naturalLight: LightingNaturalLightRatio[0],
                emergency: false,
                standBy: false,
                standing: null,
            },
        ]

        setFormData({ ...formData, systems: newLightingSystem })
        setActiveLightingIndex(newLightingSystem.length)
    }

    const standings = useAppSelector((state) => state.project.subStandings)
    const buildings = useAppSelector((state) => state.project.buildings)

    function handleActiveLightingSystemChange(
        field: keyof LightingFormData,
        value: string | number | null | boolean
    ) {
        if (activeLightingIndex === null) return

        const updatedSystem = {
            ...formData.systems[activeLightingIndex],
            [field]: value,
        }

        const updatedSystems = [...formData.systems]
        updatedSystems[activeLightingIndex] = updatedSystem
        setFormData({ ...formData, systems: updatedSystems })
    }

    async function handleSubmit() {
        const result = await FormSendProtocol.handleLightingSystem(
            formData,
            setLoading,
            setFormErrors,
            projectId
        )
        if (result && result.success) {
            navigate('../', { replace: true })
        }
    }

    const currentActiveLighting =
        activeLightingIndex === null
            ? null
            : formData.systems[activeLightingIndex]

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
            <h1>Világítási rendszerek rögzítése</h1>
            <FormControl error={!!formErrors?.complex}>
                <InputLabel id="complex-select">Telephely</InputLabel>
                <Select
                    label="Telephely"
                    labelId="complex-select"
                    value={formData.complex}
                    onChange={(e) =>
                        setFormData({ ...formData, complex: e.target.value })
                    }
                >
                    {complexes.map((e: ComplexShortData, index: number) => (
                        <MenuItem key={'complex-' + index} value={e.id}>
                            {e.name}
                        </MenuItem>
                    ))}
                </Select>
                {formErrors?.complex && (
                    <FormHelperText>{formErrors.complex}</FormHelperText>
                )}
            </FormControl>
            <CardListing
                items={formData.systems}
                activeIndex={activeLightingIndex}
                onSelect={(index) => setActiveLightingIndex(index)}
                onAdd={handleAddLightingSystem}
                getName={(lightingSystem) => lightingSystem.zone}
            />

            {currentActiveLighting && (
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
                    <h1>Világítási rendszerek rögzítése</h1>
                    <FormControl
                        error={
                            formErrors !== null &&
                            activeLightingIndex !== null &&
                            !!formErrors.systems &&
                            formErrors.systems[activeLightingIndex] !== 'none'
                        }
                    >
                        <InputLabel id="building-select">Épület</InputLabel>
                        <Select
                            label="Épület"
                            labelId="building-select"
                            value={
                                formData.systems[activeLightingIndex!].building
                            }
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'building',
                                    e.target.value
                                )
                            }
                        >
                            {buildings.map(
                                (e: BuildingShort, index: number) => (
                                    <MenuItem
                                        key={'building-' + index}
                                        value={e.id}
                                    >
                                        {e.name}
                                    </MenuItem>
                                )
                            )}
                        </Select>
                        {formErrors !== null &&
                            activeLightingIndex !== null &&
                            !!formErrors.systems &&
                            formErrors.systems[activeLightingIndex] !==
                                'none' &&
                            (
                                formErrors.systems[
                                    activeLightingIndex
                                ] as LightingErrors
                            ).building && (
                                <FormHelperText>
                                    {
                                        (
                                            formErrors.systems[
                                                activeLightingIndex
                                            ] as LightingErrors
                                        ).building
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl
                        error={
                            formErrors !== null &&
                            activeLightingIndex !== null &&
                            !!formErrors.systems &&
                            formErrors.systems[activeLightingIndex] !== 'none'
                        }
                    >
                        <TextField
                            variant="standard"
                            label="Zóna megnevezése"
                            value={formData.systems[activeLightingIndex!].zone}
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'zone',
                                    e.target.value
                                )
                            }
                        />
                        {formErrors !== null &&
                            activeLightingIndex !== null &&
                            !!formErrors.systems &&
                            formErrors.systems[activeLightingIndex] !==
                                'none' &&
                            (
                                formErrors.systems[
                                    activeLightingIndex
                                ] as LightingErrors
                            ).zone && (
                                <FormHelperText>
                                    {
                                        (
                                            formErrors.systems[
                                                activeLightingIndex
                                            ] as LightingErrors
                                        ).zone
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl
                        error={
                            formErrors !== null &&
                            activeLightingIndex !== null &&
                            !!formErrors.systems &&
                            formErrors.systems[activeLightingIndex] !== 'none'
                        }
                    >
                        <TextField
                            variant="standard"
                            type="number"
                            label="Zóna területe"
                            value={formData.systems[activeLightingIndex!].size}
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'size',
                                    e.target.value
                                )
                            }
                        />
                        {formErrors !== null &&
                            activeLightingIndex !== null &&
                            !!formErrors.systems &&
                            formErrors.systems[activeLightingIndex] !==
                                'none' &&
                            (
                                formErrors.systems[
                                    activeLightingIndex
                                ] as LightingErrors
                            ).size && (
                                <FormHelperText>
                                    {
                                        (
                                            formErrors.systems[
                                                activeLightingIndex
                                            ] as LightingErrors
                                        ).size
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl>
                        <InputLabel id="solution-select">
                            Világítás megoldása
                        </InputLabel>
                        <Select
                            label="Megoldás"
                            labelId="solution-select"
                            value={
                                formData.systems[activeLightingIndex!].solution
                            }
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'solution',
                                    e.target.value
                                )
                            }
                        >
                            {LightingSolutions.map(
                                (e: string, index: number) => (
                                    <MenuItem key={index} value={e}>
                                        {e}
                                    </MenuItem>
                                )
                            )}
                        </Select>
                    </FormControl>
                    <FormControl>
                        <InputLabel id="dim-select">
                            Fényerő szabályozhatóság
                        </InputLabel>
                        <Select
                            label="Szabályozhatóság"
                            labelId="dim-select"
                            value={formData.systems[activeLightingIndex!].dim}
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'dim',
                                    e.target.value
                                )
                            }
                        >
                            {LightingDim.map((e: string, index: number) => (
                                <MenuItem key={index} value={e}>
                                    {e}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>
                    <FormControl>
                        <InputLabel id="zone-usage-select">
                            Zóna rendeltetése
                        </InputLabel>
                        <Select
                            label="Rendeltetés"
                            labelId="zone-usage-select"
                            value={
                                formData.systems[activeLightingIndex!].zoneUsage
                            }
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'zoneUsage',
                                    e.target.value
                                )
                            }
                        >
                            {ZoneUsage.map((e: string, index: number) => (
                                <MenuItem key={index} value={e}>
                                    {e}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>
                    <FormControl>
                        <InputLabel id="regulation-select">
                            Szabályozás típusa
                        </InputLabel>
                        <Select
                            label="Szabályozás"
                            labelId="regulation-select"
                            value={
                                formData.systems[activeLightingIndex!]
                                    .regulation
                            }
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'regulation',
                                    e.target.value
                                )
                            }
                        >
                            {LightingRegulation.map(
                                (e: string, index: number) => (
                                    <MenuItem key={index} value={e}>
                                        {e}
                                    </MenuItem>
                                )
                            )}
                        </Select>
                    </FormControl>
                    <FormControl>
                        <InputLabel id="natural-light-select">
                            Természetes megvilágítás
                        </InputLabel>
                        <Select
                            label="Arány"
                            labelId="natural-light-select"
                            value={
                                formData.systems[activeLightingIndex!]
                                    .naturalLight
                            }
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'naturalLight',
                                    e.target.value
                                )
                            }
                        >
                            {LightingNaturalLightRatio.map(
                                (e: string, index: number) => (
                                    <MenuItem key={index} value={e}>
                                        {e}
                                    </MenuItem>
                                )
                            )}
                        </Select>
                    </FormControl>
                    <FormControl>
                        <InputLabel id="emergency-select">
                            Vészvilágítás
                        </InputLabel>
                        <Select
                            label="Vészvilágítás van/nincs"
                            labelId="emergency-select"
                            value={
                                formData.systems[activeLightingIndex!].emergency
                                    ? 1
                                    : 0
                            }
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'emergency',
                                    Boolean(e.target.value)
                                )
                            }
                        >
                            <MenuItem key={'emergency-yes'} value={1}>
                                Van
                            </MenuItem>
                            <MenuItem key={'emergency-no'} value={0}>
                                Nincs
                            </MenuItem>
                        </Select>
                    </FormControl>
                    <FormControl>
                        <InputLabel id="standby-select">
                            Világításvezérlés készenléti fogyasztásra
                        </InputLabel>
                        <Select
                            label="Készenléti van/nincs"
                            labelId="standby-select"
                            value={
                                formData.systems[activeLightingIndex!].standBy
                                    ? 1
                                    : 0
                            }
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'standBy',
                                    Boolean(e.target.value)
                                )
                            }
                        >
                            <MenuItem key={'standby-yes'} value={1}>
                                Van stand-by fogyasztás
                            </MenuItem>
                            <MenuItem key={'standby-no'} value={0}>
                                Nincs / nem releváns
                            </MenuItem>
                        </Select>
                    </FormControl>
                    <FormControl
                        error={
                            formErrors !== null &&
                            activeLightingIndex !== null &&
                            !!formErrors.systems &&
                            formErrors.systems[activeLightingIndex] !== 'none'
                        }
                    >
                        <InputLabel id="lighting-standing-select">
                            Mérő hozzárendelése
                        </InputLabel>
                        <Select
                            label="Mérő"
                            labelId="lighting-standing-select"
                            value={currentActiveLighting.standing}
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'standing',
                                    e.target.value
                                )
                            }
                        >
                            {standings.map((e: StandingsShort) => (
                                <MenuItem key={e.id} value={e.id}>
                                    {e.name}
                                </MenuItem>
                            ))}
                        </Select>
                        {formErrors !== null &&
                            activeLightingIndex !== null &&
                            !!formErrors.systems &&
                            formErrors.systems[activeLightingIndex] !==
                                'none' &&
                            (
                                formErrors.systems[
                                    activeLightingIndex
                                ] as LightingErrors
                            ).standing && (
                                <FormHelperText>
                                    {
                                        (
                                            formErrors.systems[
                                                activeLightingIndex
                                            ] as LightingErrors
                                        ).standing
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <Button
                        variant="contained"
                        disabled={loading}
                        sx={{ mt: 2 }}
                        onClick={handleSubmit}
                    >
                        Mentés
                    </Button>
                </Box>
            )}
        </Box>
    )
}
