import { useState } from 'react'
import {
    VehicleCategories,
    type VehicleErrors,
    type VehicleFormData,
    VehicleFuelCategories,
    VehicleUsageMetricCategories,
} from '../model/Vehicles.model'
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
import { useAppSelector } from '../store'
import type { ComplexShortData } from '../model/Complex.model'
import FormSendProtocol from '../controllers/Forms.control'
import { useNavigate } from 'react-router-dom'

export default function Vehicles() {
    const [formData, setFormData] = useState<VehicleFormData>({
        id: null,
        complex: null,
        name: '',
        category: VehicleCategories[0],
        motorSize: 0,
        hibrid: false,
        fuel: VehicleFuelCategories[0],
        usageMetric: VehicleUsageMetricCategories[0],
        usageValue: 0,
        usageValue2: 0,
        subStanding: null,
    })
    const [formErrors, setFormErrors] = useState<VehicleErrors | null>(null)
    const [loading, setLoading] = useState<boolean>(false)

    const complexes = useAppSelector((state) => state.project.complexes)
    const subStandings = useAppSelector((state) => state.project.subStandings)

    const navigate = useNavigate()
    const projectId =
        useAppSelector((state) => state.project.currentTaskId) ?? ''

    async function handleSubmit() {
        const result = await FormSendProtocol.handleVehicle(
            formData,
            setLoading,
            setFormErrors,
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
                gap: 3,
                width: '100%',
                maxWidth: 360,
                mt: 3,
            }}
        >
            <h1>Jármű rögzítése</h1>
            <FormControl error={!!formErrors?.name}>
                <TextField
                    label="Megnevezés"
                    variant="standard"
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
                    {complexes.map((e: ComplexShortData) => (
                        <MenuItem key={e.id} value={e.id}>
                            {e.name}
                        </MenuItem>
                    ))}
                </Select>
                {formErrors?.complex && (
                    <FormHelperText>{formErrors.complex}</FormHelperText>
                )}
            </FormControl>
            <FormControl>
                <InputLabel id="category-select">Jármű kategóriája</InputLabel>
                <Select
                    label="Kategória"
                    labelId="category-select"
                    value={formData.category}
                    onChange={(e) =>
                        setFormData({ ...formData, category: e.target.value })
                    }
                >
                    {VehicleCategories.map((e: string, index: number) => (
                        <MenuItem key={index + '-category'} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <FormControl>
                <InputLabel id="fuel-select">Üzemanyag</InputLabel>
                <Select
                    label="Üzemanyag"
                    labelId="fuel-select"
                    value={formData.fuel}
                    onChange={(e) =>
                        setFormData({ ...formData, fuel: e.target.value })
                    }
                >
                    {VehicleFuelCategories.map((e: string, index: number) => (
                        <MenuItem key={index + '-fuel'} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            {formData.category === 'Személygépjármű' ? (
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
                    <FormControl error={!!formErrors?.motorSize}>
                        <TextField
                            label="Motor hengerűrtartalma (cm3)"
                            variant="standard"
                            type="number"
                            value={formData.motorSize}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    motorSize: Number(e.target.value),
                                })
                            }
                        />
                        {formErrors?.motorSize && (
                            <FormHelperText>
                                {formErrors.motorSize}
                            </FormHelperText>
                        )}
                    </FormControl>
                    <FormControl>
                        <InputLabel id="hibrid-select">Működési mód</InputLabel>
                        <Select
                            label="Működés"
                            labelId="hibrid-select"
                            value={formData.hibrid ? 1 : 0}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    hibrid: Boolean(e.target.value),
                                })
                            }
                        >
                            <MenuItem key={0} value={0}>
                                Nem hibrid
                            </MenuItem>
                            <MenuItem key={1} value={1}>
                                Hibrid
                            </MenuItem>
                        </Select>
                    </FormControl>
                </Box>
            ) : (
                <></>
            )}
            <FormControl>
                <InputLabel id="metric-select">Használati jellemző</InputLabel>
                <Select
                    label="Jellemző"
                    labelId="metric-select"
                    value={formData.usageMetric}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            usageMetric: e.target.value,
                        })
                    }
                >
                    {VehicleUsageMetricCategories.map(
                        (e: string, index: number) => (
                            <MenuItem key={index + '-metric'} value={e}>
                                {e}
                            </MenuItem>
                        )
                    )}
                </Select>
            </FormControl>
            <FormControl error={!!formErrors?.usageValue}>
                <TextField
                    label={
                        'Használati érték (' +
                        (formData.usageMetric === 'tkm'
                            ? 'km'
                            : formData.usageMetric) +
                        ')'
                    }
                    variant="standard"
                    type="number"
                    value={formData.usageValue}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            usageValue: Number(e.target.value),
                        })
                    }
                />
                {formErrors?.usageValue && (
                    <FormHelperText>{formErrors.usageValue}</FormHelperText>
                )}
            </FormControl>
            {formData.usageMetric === 'tkm' ? (
                <FormControl error={!!formErrors?.usageValue2}>
                    <TextField
                        label="Használati érték (t)"
                        variant="standard"
                        type="number"
                        value={formData.usageValue2}
                        onChange={(e) =>
                            setFormData({
                                ...formData,
                                usageValue2: Number(e.target.value),
                            })
                        }
                    />
                    {formErrors?.usageValue2 && (
                        <FormHelperText>
                            {formErrors.usageValue2}
                        </FormHelperText>
                    )}
                </FormControl>
            ) : (
                <></>
            )}
            <FormControl error={!!formErrors?.subStanding}>
                <InputLabel id="standing-select">Almérő</InputLabel>
                <Select
                    label="Főmérő"
                    labelId="standing-select"
                    value={formData.subStanding}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            subStanding: e.target.value,
                        })
                    }
                >
                    {subStandings.map((e) => (
                        <MenuItem key={e.id} value={e.id}>
                            {e.name}
                        </MenuItem>
                    ))}
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
    )
}
