import { useState } from 'react'
import {
    HeaterModes,
    SteamMachineModes,
    SteamUseModes,
    WasteUseModes,
    type SteamErrors,
    type SteamFormData,
    type SteamMachineData,
    type SteamMachineErrors,
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

export default function Steam() {
    const [formData, setFormData] = useState<SteamFormData>({
        id: null,
        name: '',
        pressure: 0,
        heaterMode: HeaterModes[0],
        steamUse: SteamUseModes[0],
        machines: [],
    })
    const [loading, setLoading] = useState<boolean>(false)
    const [errors, setErrors] = useState<SteamErrors | null>(null)
    const [activeSteamMachineIndex, setActiveSteamMachineIndex] = useState<
        number | null
    >(null)
    const navigate = useNavigate()

    const standings = [
        ...useAppSelector((state) => state.project.mainStandings),
        ...useAppSelector((state) => state.project.subStandings),
    ]
    const currentActiveSteamMachine =
        activeSteamMachineIndex !== null
            ? formData.machines[activeSteamMachineIndex]
            : null

    function handleAddSteamMachine() {
        const newSteamMachine: SteamMachineData = {
            id: null,
            standing: null,
            mode: SteamMachineModes[0],
            type: '',
            nominalOutput: 0,
            couldSmokeUse: false,
            smokeUse: WasteUseModes[0],
        }

        setFormData({
            ...formData,
            machines: [...formData.machines, newSteamMachine],
        })
        setActiveSteamMachineIndex(formData.machines.length)
    }

    function handleActiveMachineChange(
        field: keyof SteamMachineData,
        value: string | number | boolean | null
    ) {
        const newMachines = [...formData.machines]
        newMachines[activeSteamMachineIndex!] = {
            ...newMachines[activeSteamMachineIndex!],
            [field]: value,
        }

        setFormData({ ...formData, machines: newMachines })
    }

    async function handleSubmit() {
        const result = await FormSendProtocol.handleSteam(
            formData,
            setLoading,
            setErrors
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
            <h1>Gőzrendszer rögzítés</h1>
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
                {!!errors && errors.name !== '' && (
                    <FormHelperText>{errors.pressure}</FormHelperText>
                )}
            </FormControl>

            <FormControl>
                <InputLabel id="heater-mode-select">
                    Hőfejlesztő működési módja
                </InputLabel>
                <Select
                    label="Mód"
                    labelId="heater-mode-select"
                    value={formData.heaterMode}
                    onChange={(e) =>
                        setFormData({ ...formData, heaterMode: e.target.value })
                    }
                >
                    {HeaterModes.map((e: string, index: number) => (
                        <MenuItem key={index + '-heater-mode'} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>

            <FormControl>
                <InputLabel id="steam-use-select">
                    Gőzfogyasztás jellege
                </InputLabel>
                <Select
                    label="Jelleg"
                    labelId="steam-use-select"
                    value={formData.steamUse}
                    onChange={(e) =>
                        setFormData({ ...formData, steamUse: e.target.value })
                    }
                >
                    {SteamUseModes.map((e: string, index: number) => (
                        <MenuItem key={index + '-heater-mode'} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>

            <CardListing<SteamMachineData>
                items={formData.machines}
                activeIndex={activeSteamMachineIndex}
                onAdd={handleAddSteamMachine}
                onSelect={(index) => setActiveSteamMachineIndex(index)}
                getName={(item) =>
                    formData.machines.indexOf(item) + 1 + '. gőzfejlesztő'
                }
            />

            {currentActiveSteamMachine && (
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                    <FormControl
                        error={
                            !!errors &&
                            activeSteamMachineIndex !== null &&
                            errors.machines[activeSteamMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeSteamMachineIndex
                                ] as SteamMachineErrors
                            ).type !== ''
                        }
                    >
                        <TextField
                            label="Gőzfejlesztő típusa"
                            variant="standard"
                            value={currentActiveSteamMachine.type}
                            onChange={(e) =>
                                handleActiveMachineChange(
                                    'type',
                                    e.target.value
                                )
                            }
                        />
                        {!!errors &&
                            errors.machines[activeSteamMachineIndex!] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeSteamMachineIndex!
                                ] as SteamMachineErrors
                            ).type !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.machines[
                                                activeSteamMachineIndex!
                                            ] as SteamMachineErrors
                                        ).type
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl
                        error={
                            !!errors &&
                            activeSteamMachineIndex !== null &&
                            errors.machines[activeSteamMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeSteamMachineIndex
                                ] as SteamMachineErrors
                            ).nominalOutput !== ''
                        }
                    >
                        <TextField
                            label="Névleges teljesítmény"
                            variant="standard"
                            value={currentActiveSteamMachine.nominalOutput}
                            type="number"
                            onChange={(e) =>
                                handleActiveMachineChange(
                                    'nominalOutput',
                                    e.target.value
                                )
                            }
                        />
                        {!!errors &&
                            errors.machines[activeSteamMachineIndex!] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeSteamMachineIndex!
                                ] as SteamMachineErrors
                            ).nominalOutput !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.machines[
                                                activeSteamMachineIndex!
                                            ] as SteamMachineErrors
                                        ).nominalOutput
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl
                        error={
                            !!errors &&
                            activeSteamMachineIndex !== null &&
                            errors.machines[activeSteamMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeSteamMachineIndex
                                ] as SteamMachineErrors
                            ).standing !== ''
                        }
                    >
                        <InputLabel id="standing-select">
                            Mérő hozzárendelése
                        </InputLabel>
                        <Select
                            label="Mérő"
                            labelId="standing-select"
                            value={currentActiveSteamMachine.standing}
                            onChange={(e) =>
                                handleActiveMachineChange(
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
                            errors.machines[activeSteamMachineIndex!] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeSteamMachineIndex!
                                ] as SteamMachineErrors
                            ).standing !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.machines[
                                                activeSteamMachineIndex!
                                            ] as SteamMachineErrors
                                        ).standing
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl>
                        <InputLabel id="machine-mode-select">
                            Működési mód
                        </InputLabel>
                        <Select
                            label="Mód"
                            labelId="machine-mode-select"
                            value={currentActiveSteamMachine.mode}
                            onChange={(e) =>
                                handleActiveMachineChange(
                                    'mode',
                                    e.target.value
                                )
                            }
                        >
                            {SteamMachineModes.map(
                                (e: string, index: number) => (
                                    <MenuItem
                                        key={index + '-machine-mode'}
                                        value={e}
                                    >
                                        {e}
                                    </MenuItem>
                                )
                            )}
                        </Select>
                    </FormControl>
                    <FormControl>
                        <InputLabel id="waste-use-select">
                            Van lehetőség füstgázhő hasznosításra
                        </InputLabel>
                        <Select
                            label="Füstgázhő hasznosítás"
                            labelId="waste-use-select"
                            value={
                                currentActiveSteamMachine.couldSmokeUse ? 1 : 0
                            }
                            onChange={(e) =>
                                handleActiveMachineChange(
                                    'couldSmokeUse',
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
                            Van füstgázhő hasznosítás
                        </InputLabel>
                        <Select
                            label="Hasznosítás"
                            labelId="waste-select"
                            value={currentActiveSteamMachine.smokeUse}
                            onChange={(e) =>
                                handleActiveMachineChange(
                                    'smokeUse',
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
