import { useState } from 'react'
import {
    CirculationTypes,
    HMVTypes,
    type HMVData,
    type HMVFormErrors,
} from '../model/HMV.model'
import { useAppSelector } from '../store'
import {
    Box,
    Button,
    Checkbox,
    FormControl,
    FormControlLabel,
    FormHelperText,
    InputLabel,
    MenuItem,
    Select,
    TextField,
} from '@mui/material'
import { useNavigate } from 'react-router-dom'
import type { ComplexShortData } from '../model/Complex.model'
import type { BuildingShort } from '../model/Building.model'
import type { StandingsShort } from '../model/Standings.model'
import type { HeaterShort } from '../model/Heater.model'
import FormSendProtocol from '../controllers/Forms.control'

export default function HMVSystem() {
    const [formData, setFormData] = useState<HMVData>({
        id: null,
        name: '',
        complex: '',
        building: '',
        standing: '',
        type: HMVTypes[0],
        amount: 0,
        heating: '',
        regulation: CirculationTypes[0],
        circulation: false,
        containment: false,
    })
    const [formErrors, setFormErrors] = useState<HMVFormErrors | null>(null)
    const [loading, setLoading] = useState<boolean>(false)
    const navigate = useNavigate()

    const complexes = useAppSelector((state) => state.project.complexes)
    const buildings = useAppSelector((state) => state.project.buildings)
    const standings = [
        ...useAppSelector((state) => state.project.mainStandings),
        ...useAppSelector((state) => state.project.subStandings),
    ]
    const heaters = useAppSelector((state) => state.project.heaters)
    const projectId = useAppSelector((state) => state.project.currentTaskId)

    async function handleSubmit() {
        const result = await FormSendProtocol.handleHMVSystemForm(
            formData,
            setLoading,
            setFormErrors,
            projectId!
        )
        if (result && result.success) {
            navigate('../', { replace: true })
        }
    }

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
            <h1>HMV rendszer rögzítése</h1>
            <FormControl error={!!formErrors?.name}>
                <TextField
                    variant="standard"
                    label="Megnevezés"
                    value={formData.name}
                    onChange={(e) =>
                        setFormData({ ...formData, name: e.target.value })
                    }
                />
                {formErrors?.name && (
                    <FormHelperText>{formErrors.name}</FormHelperText>
                )}
            </FormControl>
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
            <FormControl error={!!formErrors?.building}>
                <InputLabel id="building-select">Épület</InputLabel>
                <Select
                    label="Épület"
                    labelId="building-select"
                    value={formData.building}
                    onChange={(e) =>
                        setFormData({ ...formData, building: e.target.value })
                    }
                >
                    {buildings.map((e: BuildingShort, index: number) => (
                        <MenuItem key={'building-' + index} value={e.id}>
                            {e.name}
                        </MenuItem>
                    ))}
                </Select>
                {formErrors?.building && (
                    <FormHelperText>{formErrors.building}</FormHelperText>
                )}
            </FormControl>
            <FormControl error={!!formErrors?.standing}>
                <InputLabel id="standing-select">Mérés</InputLabel>
                <Select
                    label="Mérés"
                    labelId="standing-select"
                    value={formData.standing}
                    onChange={(e) =>
                        setFormData({ ...formData, standing: e.target.value })
                    }
                >
                    {standings.map((e: StandingsShort, index: number) => (
                        <MenuItem key={'standing-' + index} value={e.id}>
                            {e.name}
                        </MenuItem>
                    ))}
                </Select>
                {formErrors?.standing && (
                    <FormHelperText>{formErrors.standing}</FormHelperText>
                )}
            </FormControl>
            <FormControl>
                <InputLabel id="type-select">
                    Jellemző használati melegvíz készítési módja
                </InputLabel>
                <Select
                    label="Készítés módja"
                    labelId="type-select"
                    value={formData.type}
                    onChange={(e) =>
                        setFormData({ ...formData, type: e.target.value })
                    }
                >
                    {HMVTypes.map((e: string, index: number) => (
                        <MenuItem key={'standing-' + index} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <FormControl error={!!formErrors?.amount}>
                <TextField
                    variant="standard"
                    type="number"
                    label="Mennyiség"
                    value={formData.amount}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            amount: Number(e.target.value),
                        })
                    }
                />
                {formErrors?.amount && (
                    <FormHelperText>{formErrors.amount}</FormHelperText>
                )}
            </FormControl>
            <FormControl error={!!formErrors?.heating}>
                <InputLabel id="heating-select">Hőtermelője</InputLabel>
                <Select
                    label="Hőtermelője"
                    labelId="heating-select"
                    value={formData.heating}
                    onChange={(e) =>
                        setFormData({ ...formData, heating: e.target.value })
                    }
                >
                    {heaters.map((e: HeaterShort, index: number) => (
                        <MenuItem key={'heater-' + index} value={e.id!}>
                            {e.name}
                        </MenuItem>
                    ))}
                </Select>
                {formErrors?.heating && (
                    <FormHelperText>{formErrors.heating}</FormHelperText>
                )}
            </FormControl>
            <FormControl>
                <InputLabel id="regulation-select">
                    Szabályozás módja
                </InputLabel>
                <Select
                    label="Szabályozás"
                    labelId="regulation-select"
                    value={formData.regulation}
                    onChange={(e) =>
                        setFormData({ ...formData, regulation: e.target.value })
                    }
                >
                    {CirculationTypes.map((e: string, index: number) => (
                        <MenuItem key={'regulation-' + index} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <FormControlLabel
                label="Cirkuláció van"
                control={
                    <Checkbox
                        value={formData.circulation}
                        onChange={(e) =>
                            setFormData({
                                ...formData,
                                circulation: e.target.checked,
                            })
                        }
                    />
                }
            />
            <FormControlLabel
                label="HMV tárolás van"
                control={
                    <Checkbox
                        value={formData.containment}
                        onChange={(e) =>
                            setFormData({
                                ...formData,
                                containment: e.target.checked,
                            })
                        }
                    />
                }
            />
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
