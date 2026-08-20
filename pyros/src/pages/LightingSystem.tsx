import { useState } from 'react'
import {
    LightingDim,
    LightingNaturalLightRatio,
    LightingRegulation,
    LightingSolutions,
    ZoneUsage,
    type LightingErrors,
    type LightingFormData,
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

export default function LightingSystem() {
    const [formData, setFormData] = useState<Array<LightingFormData>>([])
    const [formErrors, setFormErrors] = useState<Array<
        LightingErrors | string
    > | null>(null)
    const [activeLightingIndex, setActiveLightingIndex] = useState<
        number | null
    >(null)
    const [loading, setLoading] = useState<boolean>(false)
    const navigate = useNavigate()
    const projectId =
        useAppSelector((state) => state.project.currentTaskId) ?? ''

    function handleAddLightingSystem() {
        const newLightingSystem: LightingFormData = {
            id: null,
            zone: '',
            size: 0,
            solution: LightingSolutions[0],
            dim: LightingDim[0],
            zoneUsage: ZoneUsage[0],
            regulation: LightingRegulation[0],
            naturalLight: LightingNaturalLightRatio[0],
            emergency: false,
            standBy: false,
        }

        setFormData([...formData, newLightingSystem])
        setActiveLightingIndex(formData.length)
    }

    function handleActiveLightingSystemChange(
        field: keyof LightingFormData,
        value: string | number | null | boolean
    ) {
        if (activeLightingIndex === null) return

        const updatedSystem = {
            ...formData[activeLightingIndex],
            [field]: value,
        }

        const updatedFormData = JSON.parse(JSON.stringify(formData))
        updatedFormData[activeLightingIndex] = updatedSystem

        setFormData(updatedFormData)
    }

    async function handleSubmit() {
        const result = await FormSendProtocol.handleLightingSystem(
            formData,
            setLoading,
            setFormErrors,
            projectId
        )
        if (result && result.success) {
            navigate('/')
        }
    }

    const currentActiveLighting =
        activeLightingIndex === null ? null : formData[activeLightingIndex]

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
            <CardListing
                items={formData}
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
                            formErrors[activeLightingIndex] !== 'none'
                        }
                    >
                        <TextField
                            variant="standard"
                            label="Zóna megnevezése"
                            value={formData[activeLightingIndex!].zone}
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'zone',
                                    e.target.value
                                )
                            }
                        />
                        {formErrors !== null &&
                            activeLightingIndex !== null &&
                            formErrors[activeLightingIndex] !== 'none' &&
                            (formErrors[activeLightingIndex] as LightingErrors)
                                .zone && (
                                <FormHelperText>
                                    {
                                        (
                                            formErrors[
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
                            formErrors[activeLightingIndex] !== 'none'
                        }
                    >
                        <TextField
                            variant="standard"
                            type="number"
                            label="Zóna területe"
                            value={formData[activeLightingIndex!].size}
                            onChange={(e) =>
                                handleActiveLightingSystemChange(
                                    'size',
                                    e.target.value
                                )
                            }
                        />
                        {formErrors !== null &&
                            activeLightingIndex !== null &&
                            formErrors[activeLightingIndex] !== 'none' &&
                            (formErrors[activeLightingIndex] as LightingErrors)
                                .size && (
                                <FormHelperText>
                                    {
                                        (
                                            formErrors[
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
                            value={formData[activeLightingIndex!].solution}
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
                            value={formData[activeLightingIndex!].dim}
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
                            value={formData[activeLightingIndex!].zoneUsage}
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
                            value={formData[activeLightingIndex!].regulation}
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
                            value={formData[activeLightingIndex!].naturalLight}
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
                                formData[activeLightingIndex!].emergency ? 1 : 0
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
                                formData[activeLightingIndex!].standBy ? 1 : 0
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
