import {
    Autocomplete,
    Box,
    Button,
    CircularProgress,
    FormControl,
    FormHelperText,
    InputLabel,
    MenuItem,
    OutlinedInput,
    Select,
    TextField,
    type SelectChangeEvent,
    type Theme,
} from '@mui/material'
import { useState } from 'react'
import {
    type ComplexErrors,
    type ComplexFormData,
    type ComplexShortData,
    type ComplexWorkingData,
    defaultHours,
} from '../model/Complex.model'
import settlementData from '../model/zipToCity.json'
import theme from '../assets/theme'
import FormSendProtocol from '../controllers/Forms.control'
import { useNavigate } from 'react-router-dom'
import { useAppDispatch, useAppSelector } from '../store'
import { addComplexLocally } from '../store/projectSlice'
import CardListing from '../components/CardListing'
import workTypes from '../model/workTypes.json'

function getStyles(id: string, selectedIds: string[], theme: Theme) {
    return {
        fontWeight: selectedIds.includes(id)
            ? theme.typography.fontWeightMedium
            : theme.typography.fontWeightRegular,
    }
}

const ITEM_HEIGHT = 48
const ITEM_PADDING_TOP = 8
const MenuProps = {
    slotProps: {
        paper: {
            style: {
                maxHeight: ITEM_HEIGHT * 4.5 + ITEM_PADDING_TOP,
                width: 250,
            },
        },
    },
}

export default function Complex() {
    const dispatch = useAppDispatch()
    const [formData, setFormData] = useState<ComplexFormData>({
        id: null,
        name: '',
        podId: '',
        address: '',
        postal: 0,
        city: '',
        parcelNumber: '',
        meterStandings: new Array<string>(),
        working: [],
    })
    const [activeIndex, setActiveIndex] = useState<number | null>(null)
    const mainStandings = useAppSelector((state) => state.project.mainStandings)
    const settlements = settlementData
    const projectId =
        useAppSelector((state) => state.project.currentTaskId) ?? ''

    const handleStandingChange = (event: SelectChangeEvent<string[]>) => {
        const {
            target: { value },
        } = event
        const updatedIds = typeof value === 'string' ? value.split(',') : value
        setFormData({
            ...formData,
            meterStandings: updatedIds,
        })
    }

    const navigate = useNavigate()
    const [loading, setLoading] = useState<boolean>(false)
    const [errorMessage, setErrorMessage] = useState<ComplexErrors | null>(
        {} as ComplexErrors
    )

    const handleSave = async () => {
        const result = await FormSendProtocol.handleComplexForm(
            formData,
            setLoading,
            setErrorMessage,
            projectId
        )

        if (result && result.success) {
            const savedResult: ComplexShortData = {
                id: formData.id as string,
                name: formData.name,
            }
            dispatch(addComplexLocally(savedResult))
            navigate('/')
        }
    }

    function addWorkingHours() {
        const newHours = [
            ...formData.working,
            { workType: workTypes[0], workHours: defaultHours[0] },
        ]

        setFormData({ ...formData, working: newHours })
        setActiveIndex(newHours.length - 1)
    }

    function handleActiveChange(
        field: keyof ComplexWorkingData,
        value: string | null
    ) {
        const newWorking = [...formData.working]
        newWorking[activeIndex!] = {
            ...newWorking[activeIndex!],
            [field]: value,
        }

        setFormData({ ...formData, working: newWorking })
    }

    const currentActiveWorking =
        activeIndex !== null ? formData.working[activeIndex] : null

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
            <h1>Telephely rögzítése</h1>
            <FormControl fullWidth error={!!errorMessage?.name}>
                <TextField
                    label="Megnevezés"
                    variant="standard"
                    value={formData.name}
                    onChange={(e) =>
                        setFormData({ ...formData, name: e.target.value })
                    }
                ></TextField>
                {errorMessage?.name && (
                    <FormHelperText>{errorMessage.name}</FormHelperText>
                )}
            </FormControl>
            <FormControl>
                <TextField
                    label="POD azonosító"
                    variant="standard"
                    value={formData.podId}
                    onChange={(e) =>
                        setFormData({ ...formData, podId: e.target.value })
                    }
                />
            </FormControl>
            <FormControl fullWidth error={!!errorMessage?.postal}>
                <Autocomplete
                    disablePortal
                    options={settlements}
                    getOptionLabel={(option) => `${option.zip}, ${option.city}`}
                    renderInput={(params) => (
                        <TextField {...params} label="Település" />
                    )}
                    filterOptions={(options, state) => {
                        const inputValue = state.inputValue.toLowerCase()
                        return options.filter(
                            (option) =>
                                option.zip.toString().includes(inputValue) ||
                                option.city.toLowerCase().includes(inputValue)
                        )
                    }}
                    onChange={(_, newValue) => {
                        setFormData({
                            ...formData,
                            city: newValue ? newValue.city : '',
                            postal: newValue ? newValue.zip : 0,
                        })
                    }}
                />
                {errorMessage?.postal && (
                    <FormHelperText>{errorMessage.postal}</FormHelperText>
                )}
            </FormControl>
            <FormControl fullWidth error={!!errorMessage?.address}>
                <TextField
                    label="Utca, házszám"
                    variant="standard"
                    value={formData.address}
                    onChange={(e) =>
                        setFormData({ ...formData, address: e.target.value })
                    }
                ></TextField>
                {errorMessage?.address && (
                    <FormHelperText>{errorMessage.address}</FormHelperText>
                )}
            </FormControl>
            <FormControl fullWidth error={!!errorMessage?.parcelNumber}>
                <TextField
                    label="Helyrajzi szám"
                    variant="standard"
                    value={formData.parcelNumber}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            parcelNumber: e.target.value,
                        })
                    }
                ></TextField>
                {errorMessage?.parcelNumber && (
                    <FormHelperText>{errorMessage.parcelNumber}</FormHelperText>
                )}
            </FormControl>
            <FormControl
                sx={{ width: '100%' }}
                error={!!errorMessage?.meterStandings}
            >
                <InputLabel id="main-standings-multiple-label">
                    Főmérők
                </InputLabel>
                <Select
                    labelId="main-standings-multiple-label"
                    id="main-standings-multiple"
                    multiple
                    value={formData.meterStandings || []}
                    onChange={handleStandingChange}
                    input={<OutlinedInput label="Főmérők" />}
                    MenuProps={MenuProps}
                    renderValue={(selectedIds) => {
                        return selectedIds
                            .map(
                                (id) =>
                                    mainStandings.find(
                                        (standing) => standing.id === id
                                    )?.name
                            )
                            .filter(Boolean)
                            .join(', ')
                    }}
                >
                    {mainStandings.map((standing) => {
                        const standingId = standing.id || ''
                        return (
                            <MenuItem
                                key={standingId}
                                value={standingId}
                                style={getStyles(
                                    standingId,
                                    formData.meterStandings || [],
                                    theme
                                )}
                            >
                                {standing.name}
                            </MenuItem>
                        )
                    })}
                </Select>
                {errorMessage?.meterStandings && (
                    <FormHelperText>
                        {errorMessage.meterStandings}
                    </FormHelperText>
                )}
            </FormControl>
            <CardListing<ComplexWorkingData>
                items={formData.working}
                activeIndex={activeIndex}
                onSelect={(index) => setActiveIndex(index)}
                onAdd={addWorkingHours}
                getName={(e) =>
                    'Munkatevékenység ' + (formData.working.indexOf(e) + 1)
                }
            />
            {currentActiveWorking && (
                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                    <FormControl>
                        <Autocomplete
                            disablePortal
                            options={workTypes}
                            getOptionLabel={(option) => `${option}`}
                            renderInput={(params) => (
                                <TextField {...params} label="Tevékenység" />
                            )}
                            filterOptions={(options, state) => {
                                const inputValue =
                                    state.inputValue.toLowerCase()
                                return options.filter((option) =>
                                    option.toLowerCase().includes(inputValue)
                                )
                            }}
                            onChange={(_, newValue) => {
                                handleActiveChange('workType', newValue)
                            }}
                            value={currentActiveWorking.workType}
                        />
                    </FormControl>
                    <FormControl>
                        <InputLabel id="work-hours-label">Munkarend</InputLabel>
                        <Select
                            label="Munkarend"
                            labelId="work-hours-label"
                            value={currentActiveWorking.workHours}
                            onChange={(e) =>
                                handleActiveChange('workHours', e.target.value)
                            }
                        >
                            {defaultHours.map((e: string, index: number) => (
                                <MenuItem key={index} value={e}>
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
                startIcon={
                    loading ? (
                        <CircularProgress size={20} color="inherit" />
                    ) : null
                }
                onClick={handleSave}
            >
                Rögzítés
            </Button>
        </Box>
    )
}
