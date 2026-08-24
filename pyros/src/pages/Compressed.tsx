import { useState } from 'react'
import {
    type CompressedFormData,
    CompressorModes,
    WasteUseModes,
    type CompressorData,
    type CompressorFormErrors,
    type CompressorErrors,
} from '../model/Technology.model'
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
import CardListing from '../components/CardListing'
import { useAppSelector } from '../store'
import type { StandingsShort } from '../model/Standings.model'
import FormSendProtocol from '../controllers/Forms.control'
import { useNavigate } from 'react-router-dom'

export default function Compressed() {
    const [formData, setFormData] = useState<CompressedFormData>({
        id: null,
        name: '',
        pressure: 0,
        machines: [],
    })
    const [activeCompressorIndex, setActiveCompressorIndex] = useState<
        number | null
    >(null)
    const [loading, setLoading] = useState<boolean>(false)
    const [errors, setErrors] = useState<CompressorFormErrors | null>(null)

    const standings = [
        ...useAppSelector((state) => state.project.mainStandings),
        ...useAppSelector((state) => state.project.subStandings),
    ]
    const projectId =
        useAppSelector((state) => state.project.currentTaskId) ?? ''
    const navigate = useNavigate()

    function handleAddCompressor() {
        const newCompressors = [
            ...formData.machines,
            {
                id: null,
                mode: CompressorModes[0],
                standing: null,
                hours: 0,
                compressorType: '',
                amount: 0,
                nominalOutput: 0,
                couldWasteUse: false,
                wasteUse: WasteUseModes[0],
            },
        ]

        setFormData({ ...formData, machines: newCompressors })
        setActiveCompressorIndex(newCompressors.length)
    }

    function handleActiveCompressorChange(
        field: keyof CompressorData,
        value: string | number | boolean | null
    ) {
        const compressors = [...formData.machines]
        compressors[activeCompressorIndex!] = {
            ...compressors[activeCompressorIndex!],
            [field]: value,
        }

        setFormData({ ...formData, machines: compressors })
    }

    const currentActiveCompressor =
        activeCompressorIndex === null
            ? null
            : formData.machines[activeCompressorIndex]

    async function handleSubmit() {
        const result = await FormSendProtocol.handleCompressor(
            formData,
            setLoading,
            setErrors,
            projectId
        )
        if (result && result.success) {
            navigate('/')
        }
    }

    return (
        <Box
            sx={{
                display: 'flex',
                flexDirection: 'column',
                gap: 2,
                width: '100%',
                maxWidth: 360,
                mt: 3,
            }}
        >
            <h1>Sűrített levegős rendszer rögzítése</h1>
            <FormControl error={!!errors?.name}>
                <TextField
                    label="Megnevezés"
                    variant="standard"
                    value={formData.name}
                    onChange={(e) =>
                        setFormData({ ...formData, name: e.target.value })
                    }
                />
                {!!errors && errors.name !== '' && (
                    <FormHelperText>{errors.name}</FormHelperText>
                )}
            </FormControl>
            <FormControl error={!!errors?.pressure}>
                <TextField
                    label="Hálózati nyomás"
                    variant="standard"
                    type="number"
                    value={formData.pressure}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            pressure: Number(e.target.value),
                        })
                    }
                />
                {!!errors && errors.pressure !== '' && (
                    <FormHelperText>{errors.pressure}</FormHelperText>
                )}
            </FormControl>

            <CardListing<CompressorData>
                items={formData.machines}
                activeIndex={activeCompressorIndex}
                onSelect={(index) => setActiveCompressorIndex(index)}
                onAdd={handleAddCompressor}
                getName={(item) =>
                    formData.machines.indexOf(item) + 1 + '. kompresszor'
                }
            />

            {currentActiveCompressor && (
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                    <h1>{(activeCompressorIndex ?? 0) + 1}. kompresszor</h1>
                    <FormControl
                        error={
                            errors !== null &&
                            activeCompressorIndex !== null &&
                            errors.compressor[activeCompressorIndex] !==
                                'none' &&
                            (
                                errors.compressor[
                                    activeCompressorIndex
                                ] as CompressorErrors
                            ).compressorType !== ''
                        }
                    >
                        <TextField
                            label="Kompresszor típusa"
                            variant="standard"
                            value={currentActiveCompressor.compressorType}
                            onChange={(e) =>
                                handleActiveCompressorChange(
                                    'compressorType',
                                    e.target.value
                                )
                            }
                        />
                        {!!errors &&
                            errors.compressor[activeCompressorIndex!] !==
                                'none' &&
                            (
                                errors.compressor[
                                    activeCompressorIndex!
                                ] as CompressorErrors
                            ).compressorType !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.compressor[
                                                activeCompressorIndex!
                                            ] as CompressorErrors
                                        ).compressorType
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl
                        error={
                            errors !== null &&
                            activeCompressorIndex !== null &&
                            errors.compressor[activeCompressorIndex] !==
                                'none' &&
                            (
                                errors.compressor[
                                    activeCompressorIndex
                                ] as CompressorErrors
                            ).amount !== ''
                        }
                    >
                        <TextField
                            label="Mennyisége"
                            variant="standard"
                            type="number"
                            value={currentActiveCompressor.amount}
                            onChange={(e) =>
                                handleActiveCompressorChange(
                                    'amount',
                                    e.target.value
                                )
                            }
                        />
                        {!!errors &&
                            errors.compressor[activeCompressorIndex!] !==
                                'none' &&
                            (
                                errors.compressor[
                                    activeCompressorIndex!
                                ] as CompressorErrors
                            ).amount !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.compressor[
                                                activeCompressorIndex!
                                            ] as CompressorErrors
                                        ).amount
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl
                        error={
                            errors !== null &&
                            activeCompressorIndex !== null &&
                            errors.compressor[activeCompressorIndex] !==
                                'none' &&
                            (
                                errors.compressor[
                                    activeCompressorIndex
                                ] as CompressorErrors
                            ).nominalOutput !== ''
                        }
                    >
                        <TextField
                            label="Névleges teljesítmény"
                            variant="standard"
                            type="number"
                            value={currentActiveCompressor.nominalOutput}
                            onChange={(e) =>
                                handleActiveCompressorChange(
                                    'nominalOutput',
                                    e.target.value
                                )
                            }
                        />
                        {!!errors &&
                            errors.compressor[activeCompressorIndex!] !==
                                'none' &&
                            (
                                errors.compressor[
                                    activeCompressorIndex!
                                ] as CompressorErrors
                            ).nominalOutput !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.compressor[
                                                activeCompressorIndex!
                                            ] as CompressorErrors
                                        ).nominalOutput
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl>
                        <InputLabel id="comp-mode-select">
                            Működési módja
                        </InputLabel>
                        <Select
                            label="Működés"
                            labelId="comp-mode-select"
                            value={currentActiveCompressor.mode}
                            onChange={(e) =>
                                handleActiveCompressorChange(
                                    'mode',
                                    e.target.value
                                )
                            }
                        >
                            {CompressorModes.map((e: string, index: number) => (
                                <MenuItem key={index + '-mode'} value={e}>
                                    {e}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>
                    <FormControl
                        error={
                            errors !== null &&
                            activeCompressorIndex !== null &&
                            errors.compressor[activeCompressorIndex] !==
                                'none' &&
                            (
                                errors.compressor[
                                    activeCompressorIndex
                                ] as CompressorErrors
                            ).standing !== ''
                        }
                    >
                        <InputLabel id="comp-standing-select">
                            Mérő hozzárendelés
                        </InputLabel>
                        <Select
                            label="Mérő"
                            labelId="comp-standing-select"
                            value={currentActiveCompressor.standing}
                            onChange={(e) =>
                                handleActiveCompressorChange(
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
                        {!!errors &&
                            errors.compressor[activeCompressorIndex!] !==
                                'none' &&
                            (
                                errors.compressor[
                                    activeCompressorIndex!
                                ] as CompressorErrors
                            ).standing !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.compressor[
                                                activeCompressorIndex!
                                            ] as CompressorErrors
                                        ).standing
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl
                        error={
                            errors !== null &&
                            activeCompressorIndex !== null &&
                            errors.compressor[activeCompressorIndex] !==
                                'none' &&
                            (
                                errors.compressor[
                                    activeCompressorIndex
                                ] as CompressorErrors
                            ).hours !== ''
                        }
                    >
                        <TextField
                            label="Éves terhelt üzemóra (h)"
                            variant="standard"
                            value={currentActiveCompressor.hours}
                            onChange={(e) =>
                                handleActiveCompressorChange(
                                    'hours',
                                    e.target.value
                                )
                            }
                        />
                        {!!errors &&
                            errors.compressor[activeCompressorIndex!] !==
                                'none' &&
                            (
                                errors.compressor[
                                    activeCompressorIndex!
                                ] as CompressorErrors
                            ).hours !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.compressor[
                                                activeCompressorIndex!
                                            ] as CompressorErrors
                                        ).hours
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl>
                        <InputLabel id="waste-use-select">
                            Van lehetőség hulladékhő hasznosításra
                        </InputLabel>
                        <Select
                            label="Hulladékhő hasznosítás"
                            labelId="waste-use-select"
                            value={
                                currentActiveCompressor.couldWasteUse ? 1 : 0
                            }
                            onChange={(e) =>
                                handleActiveCompressorChange(
                                    'couldWasteUse',
                                    e.target.value
                                )
                            }
                        >
                            <MenuItem key="Van" value={1}>
                                Van
                            </MenuItem>
                            <MenuItem key="Nincs" value={0}>
                                Nincs
                            </MenuItem>
                        </Select>
                    </FormControl>
                    <FormControl>
                        <InputLabel id="waste-select">
                            Van hulladékhő hasznosítás
                        </InputLabel>
                        <Select
                            label="Hasznosítás"
                            labelId="waste-select"
                            value={currentActiveCompressor.wasteUse}
                            onChange={(e) =>
                                handleActiveCompressorChange(
                                    'wasteUse',
                                    e.target.value
                                )
                            }
                        >
                            {WasteUseModes.map((e: string, index: number) => (
                                <MenuItem key={index + '-use'} value={e}>
                                    {e}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>
                </Box>
            )}

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
