import { useState } from 'react'
import {
    CoolerModes,
    CoolingMachineModes,
    WasteUseModes,
    type CoolingErrors,
    type CoolingFormData,
    type CoolingMachineData,
    type CoolingMachineErrors,
} from '../model/Technology.model'
import { useNavigate } from 'react-router-dom'
import { useAppSelector } from '../store'
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
import type { StandingsShort } from '../model/Standings.model'
import FormSendProtocol from '../controllers/Forms.control'

export default function Cooling() {
    const [formData, setFormData] = useState<CoolingFormData>({
        id: null,
        name: '',
        coolerMode: CoolerModes[0],
        machines: [],
    })
    const [loading, setLoading] = useState<boolean>(false)
    const [activeCoolingMachineIndex, setActiveCoolingMachineIndex] = useState<
        number | null
    >(null)
    const [errors, setErrors] = useState<CoolingErrors | null>(null)
    const navigate = useNavigate()

    const standings = [
        ...useAppSelector((state) => state.project.mainStandings),
        ...useAppSelector((state) => state.project.subStandings),
    ]
    const projectId =
        useAppSelector((state) => state.project.currentTaskId) ?? ''

    const currentActiveCoolingMachine =
        activeCoolingMachineIndex !== null
            ? formData.machines[activeCoolingMachineIndex]
            : null

    function handleAddCoolingMachine() {
        const newMachine: CoolingMachineData = {
            id: null,
            mode: CoolingMachineModes[0],
            standing: null,
            type: '',
            nominalOutput: 0,
            couldWasteUse: false,
            wasteUse: WasteUseModes[0],
        }

        setFormData({
            ...formData,
            machines: [...formData.machines, newMachine],
        })
        setActiveCoolingMachineIndex(formData.machines.length)
    }

    function handleActiveCoolingMachineChange(
        field: keyof CoolingMachineData,
        value: string | boolean | number | null
    ) {
        const newMachines = [...formData.machines]
        newMachines[activeCoolingMachineIndex!] = {
            ...newMachines[activeCoolingMachineIndex!],
            [field]: value,
        }

        setFormData({ ...formData, machines: newMachines })
    }

    async function handleSubmit() {
        const result = await FormSendProtocol.handleCooling(
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
            <h1>Technológiai hűtőrendszer rögzítés</h1>
            <FormControl error={!!errors && errors.name !== ''}>
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

            <FormControl>
                <InputLabel id="cooler-mode-select">
                    Hűtőgép működési módja
                </InputLabel>
                <Select
                    label="Működés"
                    labelId="cooler-mode-select"
                    value={formData.coolerMode}
                    onChange={(e) =>
                        setFormData({ ...formData, coolerMode: e.target.value })
                    }
                >
                    {CoolerModes.map((e: string, index: number) => (
                        <MenuItem key={index + '-cooler-mode'} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>

            <CardListing<CoolingMachineData>
                items={formData.machines}
                activeIndex={activeCoolingMachineIndex}
                onSelect={(index) => setActiveCoolingMachineIndex(index)}
                onAdd={handleAddCoolingMachine}
                getName={(item) =>
                    formData.machines.indexOf(item) + 1 + '. hűtőberendezés'
                }
            />

            {currentActiveCoolingMachine && (
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                    <FormControl
                        error={
                            !!errors &&
                            activeCoolingMachineIndex !== null &&
                            errors.machines[activeCoolingMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeCoolingMachineIndex
                                ] as CoolingMachineErrors
                            ).type !== ''
                        }
                    >
                        <TextField
                            label="Hűtőberendezés típusa"
                            variant="standard"
                            value={currentActiveCoolingMachine.type}
                            onChange={(e) =>
                                handleActiveCoolingMachineChange(
                                    'type',
                                    e.target.value
                                )
                            }
                        />
                        {!!errors &&
                            activeCoolingMachineIndex !== null &&
                            errors.machines[activeCoolingMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeCoolingMachineIndex
                                ] as CoolingMachineErrors
                            ).type !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.machines[
                                                activeCoolingMachineIndex
                                            ] as CoolingMachineErrors
                                        ).type
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl
                        error={
                            !!errors &&
                            activeCoolingMachineIndex !== null &&
                            errors.machines[activeCoolingMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeCoolingMachineIndex
                                ] as CoolingMachineErrors
                            ).nominalOutput !== ''
                        }
                    >
                        <TextField
                            label="Hűtőberendezés névleges teljesítménye"
                            variant="standard"
                            type="number"
                            value={currentActiveCoolingMachine.nominalOutput}
                            onChange={(e) =>
                                handleActiveCoolingMachineChange(
                                    'nominalOutput',
                                    e.target.value
                                )
                            }
                        />
                        {!!errors &&
                            activeCoolingMachineIndex !== null &&
                            errors.machines[activeCoolingMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeCoolingMachineIndex
                                ] as CoolingMachineErrors
                            ).nominalOutput !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.machines[
                                                activeCoolingMachineIndex
                                            ] as CoolingMachineErrors
                                        ).nominalOutput
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl>
                        <InputLabel id="machine-mode-select">
                            Hűtőberendezés működési módja
                        </InputLabel>
                        <Select
                            label="Működés"
                            labelId="machine-mode-select"
                            value={currentActiveCoolingMachine.mode}
                            onChange={(e) =>
                                handleActiveCoolingMachineChange(
                                    'mode',
                                    e.target.value
                                )
                            }
                        >
                            {CoolingMachineModes.map(
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
                    <FormControl
                        error={
                            !!errors &&
                            activeCoolingMachineIndex !== null &&
                            errors.machines[activeCoolingMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeCoolingMachineIndex
                                ] as CoolingMachineErrors
                            ).standing !== ''
                        }
                    >
                        <InputLabel id="machine-standing-select">
                            Mérő hozzárendelése
                        </InputLabel>
                        <Select
                            label="Mérő"
                            labelId="machine-standing-select"
                            value={currentActiveCoolingMachine.standing}
                            onChange={(e) =>
                                handleActiveCoolingMachineChange(
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
                            activeCoolingMachineIndex !== null &&
                            errors.machines[activeCoolingMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeCoolingMachineIndex
                                ] as CoolingMachineErrors
                            ).standing !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.machines[
                                                activeCoolingMachineIndex
                                            ] as CoolingMachineErrors
                                        ).standing
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
                                currentActiveCoolingMachine.couldWasteUse
                                    ? 1
                                    : 0
                            }
                            onChange={(e) =>
                                handleActiveCoolingMachineChange(
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
                            value={currentActiveCoolingMachine.wasteUse}
                            onChange={(e) =>
                                handleActiveCoolingMachineChange(
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
