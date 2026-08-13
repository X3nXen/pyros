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
import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import CardListing from '../components/CardListing'
import FormSendProtocol from '../controllers/Forms.control'
import type { StandingsShort } from '../model/Standings.model'
import {
    type OtherFormData,
    type OtherErrors,
    type OtherDeviceData,
    WasteUseModes,
    type OtherDeviceErrors,
    OtherMachineModes,
} from '../model/Technology.model'
import { useAppSelector } from '../store'

export default function Other() {
    const [formData, setFormData] = useState<OtherFormData>({
        id: null,
        name: '',
        machines: [],
    })
    const [loading, setLoading] = useState<boolean>(false)
    const [activeOtherMachineIndex, setActiveOtherMachineIndex] = useState<
        number | null
    >(null)
    const [errors, setErrors] = useState<OtherErrors | null>(null)
    const navigate = useNavigate()

    const standings = [
        ...useAppSelector((state) => state.project.mainStandings),
        ...useAppSelector((state) => state.project.subStandings),
    ]

    const currentActiveOtherMachine =
        activeOtherMachineIndex !== null
            ? formData.machines[activeOtherMachineIndex]
            : null

    function handleAddOtherMachine() {
        const newMachine: OtherDeviceData = {
            id: null,
            mode: OtherMachineModes[0],
            standing: null,
            type: '',
            nominalOutput: 0,
            hours: 0,
            couldWasteUse: false,
            wasteUse: WasteUseModes[0],
            amount: 0,
        }

        setFormData({
            ...formData,
            machines: [...formData.machines, newMachine],
        })
        setActiveOtherMachineIndex(formData.machines.length)
    }

    function handleActiveOtherMachineChange(
        field: keyof OtherDeviceData,
        value: string | boolean | number | null
    ) {
        const newMachines = [...formData.machines]
        newMachines[activeOtherMachineIndex!] = {
            ...newMachines[activeOtherMachineIndex!],
            [field]: value,
        }

        setFormData({ ...formData, machines: newMachines })
    }

    async function handleSubmit() {
        const result = await FormSendProtocol.handleOther(
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

            <CardListing<OtherDeviceData>
                items={formData.machines}
                activeIndex={activeOtherMachineIndex}
                onSelect={(index) => setActiveOtherMachineIndex(index)}
                onAdd={handleAddOtherMachine}
                getName={(item) =>
                    formData.machines.indexOf(item) + 1 + '. berendezés'
                }
            />

            {currentActiveOtherMachine && (
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                    <FormControl
                        error={
                            !!errors &&
                            activeOtherMachineIndex !== null &&
                            errors.machines[activeOtherMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeOtherMachineIndex
                                ] as OtherDeviceErrors
                            ).type !== ''
                        }
                    >
                        <TextField
                            label="Berendezés típusa"
                            variant="standard"
                            value={currentActiveOtherMachine.type}
                            onChange={(e) =>
                                handleActiveOtherMachineChange(
                                    'type',
                                    e.target.value
                                )
                            }
                        />
                        {!!errors &&
                            activeOtherMachineIndex !== null &&
                            errors.machines[activeOtherMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeOtherMachineIndex
                                ] as OtherDeviceErrors
                            ).type !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.machines[
                                                activeOtherMachineIndex
                                            ] as OtherDeviceErrors
                                        ).type
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl
                        error={
                            !!errors &&
                            activeOtherMachineIndex !== null &&
                            errors.machines[activeOtherMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeOtherMachineIndex
                                ] as OtherDeviceErrors
                            ).amount !== ''
                        }
                    >
                        <TextField
                            label="Mennyiség"
                            variant="standard"
                            type="number"
                            value={currentActiveOtherMachine.amount}
                            onChange={(e) =>
                                handleActiveOtherMachineChange(
                                    'amount',
                                    e.target.value
                                )
                            }
                        />
                        {!!errors &&
                            activeOtherMachineIndex !== null &&
                            errors.machines[activeOtherMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeOtherMachineIndex
                                ] as OtherDeviceErrors
                            ).amount !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.machines[
                                                activeOtherMachineIndex
                                            ] as OtherDeviceErrors
                                        ).amount
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl
                        error={
                            !!errors &&
                            activeOtherMachineIndex !== null &&
                            errors.machines[activeOtherMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeOtherMachineIndex
                                ] as OtherDeviceErrors
                            ).nominalOutput !== ''
                        }
                    >
                        <TextField
                            label="Berendezés névleges teljesítménye"
                            variant="standard"
                            type="number"
                            value={currentActiveOtherMachine.nominalOutput}
                            onChange={(e) =>
                                handleActiveOtherMachineChange(
                                    'nominalOutput',
                                    e.target.value
                                )
                            }
                        />
                        {!!errors &&
                            activeOtherMachineIndex !== null &&
                            errors.machines[activeOtherMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeOtherMachineIndex
                                ] as OtherDeviceErrors
                            ).nominalOutput !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.machines[
                                                activeOtherMachineIndex
                                            ] as OtherDeviceErrors
                                        ).nominalOutput
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl>
                        <InputLabel id="machine-mode-select">
                            Berendezés működési módja
                        </InputLabel>
                        <Select
                            label="Működés"
                            labelId="machine-mode-select"
                            value={currentActiveOtherMachine.mode}
                            onChange={(e) =>
                                handleActiveOtherMachineChange(
                                    'mode',
                                    e.target.value
                                )
                            }
                        >
                            {OtherMachineModes.map(
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
                            activeOtherMachineIndex !== null &&
                            errors.machines[activeOtherMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeOtherMachineIndex
                                ] as OtherDeviceErrors
                            ).hours !== ''
                        }
                    >
                        <TextField
                            label="Becsült éves üzemidő (h)"
                            variant="standard"
                            type="number"
                            value={currentActiveOtherMachine.hours}
                            onChange={(e) =>
                                handleActiveOtherMachineChange(
                                    'hours',
                                    e.target.value
                                )
                            }
                        />
                        {!!errors &&
                            activeOtherMachineIndex !== null &&
                            errors.machines[activeOtherMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeOtherMachineIndex
                                ] as OtherDeviceErrors
                            ).hours !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.machines[
                                                activeOtherMachineIndex
                                            ] as OtherDeviceErrors
                                        ).hours
                                    }
                                </FormHelperText>
                            )}
                    </FormControl>
                    <FormControl
                        error={
                            !!errors &&
                            activeOtherMachineIndex !== null &&
                            errors.machines[activeOtherMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeOtherMachineIndex
                                ] as OtherDeviceErrors
                            ).standing !== ''
                        }
                    >
                        <InputLabel id="machine-standing-select">
                            Mérő hozzárendelése
                        </InputLabel>
                        <Select
                            label="Mérő"
                            labelId="machine-standing-select"
                            value={currentActiveOtherMachine.standing}
                            onChange={(e) =>
                                handleActiveOtherMachineChange(
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
                            activeOtherMachineIndex !== null &&
                            errors.machines[activeOtherMachineIndex] !==
                                'none' &&
                            (
                                errors.machines[
                                    activeOtherMachineIndex
                                ] as OtherDeviceErrors
                            ).standing !== '' && (
                                <FormHelperText>
                                    {
                                        (
                                            errors.machines[
                                                activeOtherMachineIndex
                                            ] as OtherDeviceErrors
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
                                currentActiveOtherMachine.couldWasteUse ? 1 : 0
                            }
                            onChange={(e) =>
                                handleActiveOtherMachineChange(
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
                            value={currentActiveOtherMachine.wasteUse}
                            onChange={(e) =>
                                handleActiveOtherMachineChange(
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
