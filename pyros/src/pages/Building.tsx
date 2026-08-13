import {
    Box,
    Button,
    Checkbox,
    FormControl,
    FormControlLabel,
    FormHelperText,
    FormLabel,
    InputLabel,
    MenuItem,
    Radio,
    RadioGroup,
    Select,
    TextField,
    Typography,
} from '@mui/material'
import { useState } from 'react'
import {
    BuildingDefaults,
    BuildingRunning,
    BuildingUsages,
    CeilingLayerPreset,
    DoorWindowPreset,
    HeatingTypicalRegulationPreset,
    TypicalHeatingTypePreset,
    WallLayerPreset,
    WarmWaterCreationPreset,
    type BuildingErrors,
    type BuildingFormData,
    type BuildingShort,
} from '../model/Building.model'
import { useAppDispatch, useAppSelector } from '../store'
import CloudUploadIcon from '@mui/icons-material/CloudUpload'
import { useNavigate } from 'react-router-dom'
import FormSendProtocol from '../controllers/Forms.control'
import { addBuildingLocally } from '../store/projectSlice'

export default function Building() {
    const navigate = useNavigate()
    const dispatch = useAppDispatch()
    const [formData, setFormData] = useState<BuildingFormData>(BuildingDefaults)
    const [formErrors, setFormErrors] = useState<BuildingErrors | null>(null)
    const [loading, setLoading] = useState<boolean>(false)
    const complexes = useAppSelector((state) => state.project.complexes)
    const subStandings = useAppSelector((state) => state.project.subStandings)

    const handleSubmit = async () => {
        const result = await FormSendProtocol.handleBuildingForm(
            formData,
            setLoading,
            setFormErrors
        )
        console.log(formData)
        console.log(formErrors)
        if (result && result.success) {
            const savedBuilding: BuildingShort = {
                id: formData.id as string,
                name: formData.name,
            }
            dispatch(addBuildingLocally(savedBuilding))
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
            <h1>Épület rögzítése</h1>
            <h2>Általános állapotfelmérés</h2>

            <FormControl fullWidth error={!!formErrors?.name}>
                <TextField
                    label="Épület megnevezése"
                    variant="standard"
                    value={formData.name}
                    onChange={(e) =>
                        setFormData({ ...formData, name: e.target.value })
                    }
                    error={!!formErrors?.name}
                    helperText={formErrors?.name}
                />
            </FormControl>

            <FormControl fullWidth error={!!formErrors?.complex}>
                <InputLabel id="complex-select-label">
                    Hozzárendelt telephely
                </InputLabel>
                <Select
                    labelId="complex-select-label"
                    value={formData.complex || ''}
                    label="Telephely"
                    onChange={(e) => {
                        setFormData({ ...formData, complex: e.target.value })
                    }}
                >
                    {complexes.map((c) => (
                        <MenuItem key={c.id} value={c.id}>
                            {c.name}
                        </MenuItem>
                    ))}
                </Select>
                {formErrors?.complex && (
                    <FormHelperText>{formErrors?.complex}</FormHelperText>
                )}
            </FormControl>

            <FormControl fullWidth error={!!formErrors?.standings}>
                <InputLabel id="standings-select-label">
                    Hozzárendelt mérők
                </InputLabel>
                <Select
                    labelId="standings-select-label"
                    multiple
                    value={formData.standings}
                    label="Mérők"
                    onChange={(e) => {
                        setFormData({
                            ...formData,
                            standings: [...e.target.value],
                        })
                    }}
                >
                    {subStandings.map((s) => (
                        <MenuItem key={s.id as string} value={s.id as string}>
                            {s.name}
                        </MenuItem>
                    ))}
                </Select>
                {formErrors?.standings && (
                    <FormHelperText>{formErrors?.standings}</FormHelperText>
                )}
            </FormControl>

            <FormControl fullWidth>
                <InputLabel id="usage-select-label">Rendeltetése</InputLabel>
                <Select
                    labelId="usage-select-label"
                    value={formData.usage}
                    label="Rendeltetése"
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            usage: e.target.value as BuildingUsages,
                        })
                    }
                >
                    {Object.values(BuildingUsages).map((val) => (
                        <MenuItem key={val} value={val}>
                            {val}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>

            <FormControl component="fieldset">
                <FormLabel component="legend">Védettség</FormLabel>
                <RadioGroup
                    row
                    value={formData.protected ? 'protected_y' : 'protected_n'}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            protected: e.target.value === 'protected_y',
                        })
                    }
                >
                    <FormControlLabel
                        value="protected_y"
                        control={<Radio />}
                        label="Védett"
                    />
                    <FormControlLabel
                        value="protected_n"
                        control={<Radio />}
                        label="Nem védett"
                    />
                </RadioGroup>
            </FormControl>

            <FormControl fullWidth>
                <TextField
                    label="Fűtött/hűtött teljes alapterület (m2)"
                    type="number"
                    variant="standard"
                    slotProps={{ htmlInput: { step: '0.01' } }}
                    value={formData.size || ''}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            size: Number(e.target.value),
                        })
                    }
                />
            </FormControl>

            <FormControl fullWidth>
                <TextField
                    label="Fűtött/hűtött szintek száma"
                    type="number"
                    variant="standard"
                    value={formData.stories || ''}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            stories: Number(e.target.value),
                        })
                    }
                />
            </FormControl>

            <FormControl fullWidth>
                <TextField
                    label="Szint belmagasság (m)"
                    type="number"
                    variant="standard"
                    slotProps={{ htmlInput: { step: '0.01' } }}
                    value={formData.height || ''}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            height: Number(e.target.value),
                        })
                    }
                />
            </FormControl>

            <FormControl fullWidth>
                <TextField
                    label="Belső méretezési hőmérséklet (C°)"
                    type="number"
                    variant="standard"
                    slotProps={{ htmlInput: { step: '0.1' } }}
                    value={formData.insideHeat || ''}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            insideHeat: Number(e.target.value),
                        })
                    }
                />
            </FormControl>

            <FormControl component="fieldset">
                <FormLabel component="legend">Működtetés</FormLabel>
                <RadioGroup
                    row
                    value={formData.running}
                    onChange={(e) =>
                        setFormData({
                            ...formData,
                            running: e.target.value as BuildingRunning,
                        })
                    }
                >
                    <FormControlLabel
                        value={BuildingRunning.CONTINUOUS}
                        control={<Radio />}
                        label="Folyamatos"
                    />
                    <FormControlLabel
                        value={BuildingRunning.PARTITIONED}
                        control={<Radio />}
                        label="Szakaszos"
                    />
                </RadioGroup>
            </FormControl>

            <FormControlLabel
                control={
                    <Checkbox
                        checked={formData.certificate}
                        onChange={(e) =>
                            setFormData({
                                ...formData,
                                certificate: e.target.checked,
                            })
                        }
                    />
                }
                label="Nem szükséges helyszíni felmérés"
            />

            {!formData.certificate && (
                <Box
                    sx={{
                        display: 'flex',
                        flexDirection: 'column',
                        gap: 2,
                        width: '100%',
                    }}
                >
                    <FormControl fullWidth>
                        <TextField
                            label="Padló kerülete (m)"
                            type="number"
                            variant="standard"
                            slotProps={{ htmlInput: { step: '0.01' } }}
                            value={formData.floorSize || ''}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    floorSize: Number(e.target.value),
                                })
                            }
                        />
                    </FormControl>

                    <FormControl fullWidth>
                        <TextField
                            label="Nyílászárók összfelülete (m2)"
                            type="number"
                            variant="standard"
                            slotProps={{ htmlInput: { step: '0.01' } }}
                            value={formData.doorWindowSize || ''}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    doorWindowSize: Number(e.target.value),
                                })
                            }
                        />
                    </FormControl>

                    <FormControl fullWidth>
                        <TextField
                            label="Legalsó szint magassága a talajtól (m)"
                            type="number"
                            variant="standard"
                            slotProps={{ htmlInput: { step: '0.01' } }}
                            value={formData.elevation || ''}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    elevation: Number(e.target.value),
                                })
                            }
                        />
                    </FormControl>

                    <h2>Falazat állapotfelmérés</h2>

                    <FormControl fullWidth>
                        <InputLabel id="wall-layers-label">
                            Falazat rétegek
                        </InputLabel>
                        <Select
                            labelId="wall-layers-label"
                            value={formData.wallLayers}
                            label="Falazat rétegek"
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    wallLayers: e.target
                                        .value as WallLayerPreset,
                                })
                            }
                        >
                            {Object.values(WallLayerPreset).map((val) => (
                                <MenuItem key={val} value={val}>
                                    {val}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>

                    <FormControl fullWidth>
                        <TextField
                            label="Szigetelés vastagsága (cm, 0 ha nincs)"
                            type="number"
                            variant="standard"
                            slotProps={{ htmlInput: { step: '0.01' } }}
                            value={formData.wallInsulationWidth || ''}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    wallInsulationWidth: Number(e.target.value),
                                })
                            }
                        />
                    </FormControl>

                    <h2>Zárófödém állapotfelmérés</h2>

                    <FormControl fullWidth>
                        <InputLabel id="ceiling-layers-label">
                            Zárófödém rétegek
                        </InputLabel>
                        <Select
                            labelId="ceiling-layers-label"
                            value={formData.ceilingLayers}
                            label="Zárófödém rétegek"
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    ceilingLayers: e.target
                                        .value as CeilingLayerPreset,
                                })
                            }
                        >
                            {Object.values(CeilingLayerPreset).map((val) => (
                                <MenuItem key={val} value={val}>
                                    {val}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>

                    <FormControl fullWidth>
                        <TextField
                            label="Szigetelés vastagsága (cm, 0 ha nincs)"
                            type="number"
                            variant="standard"
                            slotProps={{ htmlInput: { step: '0.01' } }}
                            value={formData.ceilingInsulationWidth || ''}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    ceilingInsulationWidth: Number(
                                        e.target.value
                                    ),
                                })
                            }
                        />
                    </FormControl>

                    <h2>Padló állapotfelmérés</h2>
                    <FormControlLabel
                        control={
                            <Checkbox
                                checked={formData.floorInsulation === 1}
                                onChange={(e) =>
                                    setFormData({
                                        ...formData,
                                        floorInsulation: e.target.checked
                                            ? 1
                                            : 0,
                                    })
                                }
                            />
                        }
                        label="Padló szigetelt"
                    />

                    <h2>Nyílászárók állapotfelmérés</h2>
                    <FormControl fullWidth>
                        <InputLabel id="dw-type-label">
                            Jellemző nyílászáró
                        </InputLabel>
                        <Select
                            labelId="dw-type-label"
                            value={formData.doorWindowType}
                            label="Jellemző nyílászáró"
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    doorWindowType: e.target
                                        .value as DoorWindowPreset,
                                })
                            }
                        >
                            {Object.values(DoorWindowPreset).map((val) => (
                                <MenuItem key={val} value={val}>
                                    {val}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>

                    <h2>Hőigény kalkulációs felmérés</h2>

                    <FormControl fullWidth>
                        <InputLabel id="heating-type-label">
                            Fűtés hőtermelésének jellemző módja
                        </InputLabel>
                        <Select
                            labelId="heating-type-label"
                            value={formData.heatingType}
                            label="Fűtés hőtermelésének jellemző módja"
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    heatingType: e.target
                                        .value as TypicalHeatingTypePreset,
                                })
                            }
                        >
                            {Object.values(TypicalHeatingTypePreset).map(
                                (val) => (
                                    <MenuItem key={val} value={val}>
                                        {val}
                                    </MenuItem>
                                )
                            )}
                        </Select>
                    </FormControl>

                    <FormControl fullWidth>
                        <InputLabel id="regulation-mode-label">
                            Jellemző hőleadás szabályozási módja
                        </InputLabel>
                        <Select
                            labelId="regulation-mode-label"
                            value={formData.regulationMode}
                            label="Jellemző hőleadás szabályozási módja"
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    regulationMode: e.target
                                        .value as HeatingTypicalRegulationPreset,
                                })
                            }
                        >
                            {Object.values(HeatingTypicalRegulationPreset).map(
                                (val) => (
                                    <MenuItem key={val} value={val}>
                                        {val}
                                    </MenuItem>
                                )
                            )}
                        </Select>
                    </FormControl>

                    <FormControl fullWidth>
                        <InputLabel id="hmv-creation-label">
                            Jellemző használati melegvíz készítési módja
                        </InputLabel>
                        <Select
                            labelId="hmv-creation-label"
                            value={formData.hmvCreation}
                            label="Jellemző használati melegvíz készítési módja"
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    hmvCreation: e.target
                                        .value as WarmWaterCreationPreset,
                                })
                            }
                        >
                            {Object.values(WarmWaterCreationPreset).map(
                                (val) => (
                                    <MenuItem key={val} value={val}>
                                        {val}
                                    </MenuItem>
                                )
                            )}
                        </Select>
                    </FormControl>

                    <FormControl component="fieldset">
                        <FormLabel component="legend">
                            Használati melegvíz tárolás
                        </FormLabel>
                        <RadioGroup
                            row
                            value={
                                formData.hmvContainment
                                    ? 'hmv_containment_y'
                                    : 'hmv_containment_n'
                            }
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    hmvContainment:
                                        e.target.value === 'hmv_containment_y',
                                })
                            }
                        >
                            <FormControlLabel
                                value="hmv_containment_y"
                                control={<Radio />}
                                label="Van"
                            />
                            <FormControlLabel
                                value="hmv_containment_n"
                                control={<Radio />}
                                label="Nincs"
                            />
                        </RadioGroup>
                    </FormControl>

                    <FormControl component="fieldset">
                        <FormLabel component="legend">
                            Használati melegvíz cirkuláció
                        </FormLabel>
                        <RadioGroup
                            row
                            value={
                                formData.hmvCirculation
                                    ? 'hmv_circulation_y'
                                    : 'hmv_circulation_n'
                            }
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    hmvCirculation:
                                        e.target.value === 'hmv_circulation_y',
                                })
                            }
                        >
                            <FormControlLabel
                                value="hmv_circulation_y"
                                control={<Radio />}
                                label="Van"
                            />
                            <FormControlLabel
                                value="hmv_circulation_n"
                                control={<Radio />}
                                label="Nincs"
                            />
                        </RadioGroup>
                    </FormControl>
                </Box>
            )}

            {formData.certificate ? (
                <Box sx={{ display: 'flex', flexDirection: 'column' }}>
                    <FormControl error={!!formErrors?.qf}>
                        <TextField
                            label="Fajlagos hőenergia igény (kWh/m2a)"
                            variant="standard"
                            type="number"
                            value={formData.qf}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    qf: Number(e.target.value),
                                })
                            }
                        />
                        {formErrors?.qf && (
                            <FormHelperText>{formErrors.qf}</FormHelperText>
                        )}
                    </FormControl>
                    <FormControl error={!!formErrors?.heatLoss}>
                        <TextField
                            label="Hőveszteség"
                            variant="standard"
                            type="number"
                            value={formData.heatLoss}
                            onChange={(e) =>
                                setFormData({
                                    ...formData,
                                    heatLoss: Number(e.target.value),
                                })
                            }
                        />
                    </FormControl>
                    {formErrors?.heatLoss && (
                        <FormHelperText>{formErrors.heatLoss}</FormHelperText>
                    )}
                </Box>
            ) : (
                <></>
            )}

            <Button
                component="label"
                variant="contained"
                color="inherit"
                startIcon={<CloudUploadIcon />}
                sx={{ mt: 1 }}
            >
                Fotó az épületről
                <input
                    type="file"
                    accept="image/*"
                    style={{ display: 'none' }}
                    onChange={(e) => {
                        if (e.target.files && e.target.files[0]) {
                            setFormData({
                                ...formData,
                                imageFile: e.target.files[0],
                            })
                        }
                    }}
                />
            </Button>
            {formErrors?.imageFile && (
                <Typography
                    variant="caption"
                    color="error"
                    sx={{ mt: -2, pl: 2 }}
                >
                    {formErrors.imageFile}
                </Typography>
            )}
            {formData.imageFile && (
                <Typography
                    variant="body2"
                    sx={{
                        textAlign: 'center',
                        color: 'text.secondary',
                        mt: -1,
                    }}
                >
                    Kiválasztott fájl:{' '}
                    <strong>{formData.imageFile.name}</strong>
                </Typography>
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
