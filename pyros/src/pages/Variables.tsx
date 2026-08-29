import { useState } from 'react'
import type { VariableData } from '../model/Variables.model'
import workTypes from '../model/workTypes.json'
import {
    Box,
    Button,
    Checkbox,
    FormControl,
    FormControlLabel,
    InputLabel,
    MenuItem,
    Select,
    TextField,
} from '@mui/material'
import FormSendProtocol from '../controllers/Forms.control'
import { useAppSelector } from '../store'
import { useNavigate } from 'react-router-dom'

export default function Variables() {
    const [formData, setFormData] = useState<VariableData>({
        buborPercent: 0,
        mnbPercent: 0,
        bondPercent: 0,
        fullName: '',
        foundationYear: 2026,
        foreign: false,
        percent: 0,
        mainActivity: workTypes[0],
        companyPlace: '',
        dataYear: '',
        employeeCount: 0,
        income: 0,
    })
    const [loading, setLoading] = useState<boolean>(false)
    const projectId =
        useAppSelector((state) => state.project.currentTaskId) ?? ''
    const navigate = useNavigate()

    async function handleSubmit() {
        const result = await FormSendProtocol.handleVariables(
            formData,
            setLoading,
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
                gap: 4,
                width: '100%',
                maxWidth: 360,
                mt: 3,
            }}
        >
            <h1>Változók rögzítése</h1>
            <h2>Alapkamatok</h2>
            <FormControl>
                <TextField
                    label="BUBOR (%)"
                    variant="standard"
                    type="number"
                    value={formData.buborPercent}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            buborPercent: Number(e.target.value),
                        })
                    }
                />
            </FormControl>
            <FormControl>
                <TextField
                    label="MNB (%)"
                    variant="standard"
                    type="number"
                    value={formData.mnbPercent}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            mnbPercent: Number(e.target.value),
                        })
                    }
                />
            </FormControl>
            <FormControl>
                <TextField
                    label="Államkötvény (%)"
                    variant="standard"
                    type="number"
                    value={formData.bondPercent}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            bondPercent: Number(e.target.value),
                        })
                    }
                />
            </FormControl>
            <h2>Céginformációk</h2>
            <FormControl>
                <TextField
                    label="Cég teljes neve"
                    variant="standard"
                    value={formData.fullName}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            fullName: e.target.value,
                        })
                    }
                />
            </FormControl>
            <FormControl>
                <TextField
                    label="Cég székhelye"
                    variant="standard"
                    value={formData.companyPlace}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            companyPlace: e.target.value,
                        })
                    }
                />
            </FormControl>
            <FormControl>
                <TextField
                    label="Cég alapítási éve"
                    variant="standard"
                    type="number"
                    value={formData.foundationYear}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            foundationYear: Number(e.target.value),
                        })
                    }
                />
            </FormControl>
            <FormControlLabel
                label="Külföldi tulajdonban áll"
                control={
                    <Checkbox
                        checked={formData.foreign}
                        onChange={(e) =>
                            setFormData({
                                ...formData,
                                foreign: e.target.checked,
                            })
                        }
                    />
                }
            />
            {formData.foreign ? (
                <FormControl>
                    <TextField
                        label="Külföldi tulajdon mértéke (%)"
                        variant="standard"
                        type="number"
                        value={formData.percent}
                        onChange={(e) =>
                            setFormData({
                                ...formData,
                                percent: Number(e.target.value),
                            })
                        }
                    />
                </FormControl>
            ) : (
                <></>
            )}
            <FormControl>
                <InputLabel id="work-type-label">Cégtevékenység</InputLabel>
                <Select
                    label="Tevékenység"
                    labelId="work-type-label"
                    value={formData.mainActivity}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            mainActivity: e.target.value,
                        })
                    }
                >
                    {workTypes.map((e: string, index: number) => (
                        <MenuItem key={index} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <FormControl>
                <TextField
                    label="Bevallott év"
                    variant="standard"
                    value={formData.dataYear}
                    onChange={(e) =>
                        setFormData({ ...formData, dataYear: e.target.value })
                    }
                />
            </FormControl>
            <FormControl>
                <TextField
                    label="Alkalmazottak száma"
                    variant="standard"
                    type="number"
                    value={formData.employeeCount}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            employeeCount: Number(e.target.value),
                        })
                    }
                />
            </FormControl>
            <FormControl>
                <TextField
                    label="Éves bevétel"
                    variant="standard"
                    type="number"
                    value={formData.income}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            income: Number(e.target.value),
                        })
                    }
                />
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
