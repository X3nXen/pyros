import { useState } from 'react'
import {
    VentilationHeatRetrievers,
    VentilationInsulationMaterial,
    VentilationRegulation,
    VentilationRunning,
    VentilationStateTypes,
    VentilationTypes,
    type VentilationFormData,
    VentilationBase,
    type VentilationFormErrors,
} from '../model/Ventilation.model'
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
import type {
    BuildingShort,
    ServicedBuildingShort,
} from '../model/Building.model'
import { useNavigate } from 'react-router-dom'
import FormSendProtocol from '../controllers/Forms.control'
import { SystemPurpose } from '../model/System.model'

export default function VentilationSystem() {
    const [formData, setFormData] = useState<VentilationFormData>({
        id: null,
        name: '',
        building: null,
        servicedBuilding: [],
        type: VentilationBase.BASE_A,
        forwardHeat: 0,
        backHeat: 0,
        state: VentilationStateTypes.TYPE_A,
        ventilatorType: VentilationTypes.TYPE_A,
        ventilationOther: '',
        suckRatio: 0,
        blowRatio: 0,
        suckPower: 0,
        blowPower: 0,
        retriever: VentilationHeatRetrievers.TYPE_A,
        retrieverYear: new Date().getFullYear(),
        insulationWidth: 0,
        insulationMaterial: VentilationInsulationMaterial.TYPE_A,
        regulation: VentilationRegulation.TYPE_A,
        running: VentilationRunning.TYPE_A,
        heating: false,
        heaterId: null,
        cooling: false,
        coolingId: null,
        imageIds: [],
    })
    const [loading, setLoading] = useState<boolean>(false)
    const [formErrors, setFormErrors] = useState<VentilationFormErrors | null>(
        null
    )

    const buildings = useAppSelector((state) => state.project.buildings)
    const heaters = useAppSelector((state) => state.project.heaters)

    const navigate = useNavigate()

    function handleChange(
        field: keyof VentilationFormData,
        value: string | number | boolean | [] | null | ServicedBuildingShort[]
    ) {
        setFormData({ ...formData, [field]: value })
    }

    async function handleSubmit() {
        const result = await FormSendProtocol.handleVentilationSystem(
            formData,
            setLoading,
            setFormErrors
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
            <h1>Légtechnikai hálózat rögzítése</h1>
            <FormControl error={!!formErrors?.name}>
                <TextField
                    variant="standard"
                    label="Megnevezés"
                    value={formData.name}
                    onChange={(e) => handleChange('name', e.target.value)}
                />
                {formErrors?.name && (
                    <FormHelperText>{formErrors.name}</FormHelperText>
                )}
            </FormControl>
            <FormControl error={!!formErrors?.building}>
                <InputLabel id="building-select">Épület</InputLabel>
                <Select
                    label="Épület"
                    labelId="building-select"
                    value={formData.building}
                    onChange={(e) => handleChange('building', e.target.value)}
                >
                    {buildings.map((e: BuildingShort) => (
                        <MenuItem key={e.id} value={e.id}>
                            {e.name}
                        </MenuItem>
                    ))}
                </Select>
                {formErrors?.building && (
                    <FormHelperText>{formErrors.building}</FormHelperText>
                )}
            </FormControl>
            <FormControl error={!!formErrors?.servicedBuilding}>
                <InputLabel id="building-serviced-label">
                    Kiszolgált épületek
                </InputLabel>
                <Select
                    labelId="building-serviced-label"
                    multiple
                    value={formData.servicedBuilding.map((s) => s.buildingId)}
                    label="Kiszolgált épületek"
                    onChange={(e) => {
                        const selectedIds = e.target.value as string[]
                        const objects: Array<ServicedBuildingShort> = buildings
                            .filter((e) => selectedIds.includes(e.id))
                            .map((e: BuildingShort) => ({
                                buildingId: e.id,
                                name: e.name,
                                servicedSize: 0,
                            }))
                        handleChange('servicedBuilding', objects)
                    }}
                >
                    {buildings.map((s) => (
                        <MenuItem key={s.id as string} value={s.id as string}>
                            {s.name}
                        </MenuItem>
                    ))}
                </Select>
                {formErrors?.servicedBuilding && (
                    <FormHelperText>
                        {formErrors.servicedBuilding}
                    </FormHelperText>
                )}
            </FormControl>
            {formData.servicedBuilding.map((e: ServicedBuildingShort) => {
                return (
                    <FormControl
                        error={
                            !!(
                                formErrors?.servicedSizes &&
                                formErrors?.servicedSizes[e.buildingId]
                            )
                        }
                    >
                        <TextField
                            label={e.name + '-ben kiszolgált terület'}
                            type="number"
                            variant="outlined"
                            value={e.servicedSize}
                            onChange={(event) => {
                                e.servicedSize = Number(event.target.value)
                                handleChange(
                                    'servicedBuilding',
                                    formData.servicedBuilding
                                )
                            }}
                        />
                        {formErrors?.servicedSizes &&
                            formErrors?.servicedSizes[e.buildingId] && (
                                <FormHelperText>
                                    {formErrors.servicedSizes[e.buildingId]}
                                </FormHelperText>
                            )}
                    </FormControl>
                )
            })}
            <FormControl>
                <InputLabel id="type-select">
                    Légtechnikai rendszer működési módja
                </InputLabel>
                <Select
                    label="Működési mód"
                    labelId="type-select"
                    value={formData.type}
                    onChange={(e) => handleChange('type', e.target.value)}
                >
                    {Object.values(VentilationBase).map((e) => (
                        <MenuItem key={e} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <FormControl error={!!formErrors?.forwardHeat}>
                <TextField
                    type="number"
                    variant="standard"
                    label="Rendszer előremenő hőmérséklete"
                    value={formData.forwardHeat}
                    onChange={(e) =>
                        handleChange('forwardHeat', e.target.value)
                    }
                />
                {formErrors?.forwardHeat && (
                    <FormHelperText>{formErrors.forwardHeat}</FormHelperText>
                )}
            </FormControl>
            <FormControl error={!!formErrors?.backHeat}>
                <TextField
                    type="number"
                    variant="standard"
                    label="Rendszer visszatérő hőmérséklete"
                    value={formData.backHeat}
                    onChange={(e) => handleChange('backHeat', e.target.value)}
                />
                {formErrors?.backHeat && (
                    <FormHelperText>{formErrors.backHeat}</FormHelperText>
                )}
            </FormControl>
            <FormControl>
                <InputLabel id="state-select">
                    Légtechnikai rendszer állapota
                </InputLabel>
                <Select
                    label="Állapot"
                    labelId="state-select"
                    value={formData.state}
                    onChange={(e) => handleChange('state', e.target.value)}
                >
                    {Object.values(VentilationStateTypes).map((e) => (
                        <MenuItem key={e} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <FormControl>
                <InputLabel id="fan-select">
                    Ventilátor működési módja
                </InputLabel>
                <Select
                    label="Működési mód"
                    labelId="fan-select"
                    value={formData.ventilatorType}
                    onChange={(e) =>
                        handleChange('ventilatorType', e.target.value)
                    }
                >
                    {Object.values(VentilationTypes).map((e) => (
                        <MenuItem key={e} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            {formData.ventilatorType === VentilationTypes.OTHER ? (
                <FormControl error={!!formErrors?.ventilationOther}>
                    <TextField
                        label="Egyéb megnevezése"
                        variant="standard"
                        value={formData.ventilationOther}
                        onChange={(e) =>
                            handleChange('ventilationOther', e.target.value)
                        }
                    />
                    {formErrors?.ventilationOther && (
                        <FormHelperText>
                            {formErrors.ventilationOther}
                        </FormHelperText>
                    )}
                </FormControl>
            ) : (
                <></>
            )}
            <FormControl error={!!formErrors?.suckRatio}>
                <TextField
                    label="Elszívó hálózat légszállítása (m3)"
                    variant="standard"
                    type="number"
                    value={formData.suckRatio}
                    onChange={(e) => handleChange('suckRatio', e.target.value)}
                />
                {formErrors?.suckRatio && (
                    <FormHelperText>{formErrors.suckRatio}</FormHelperText>
                )}
            </FormControl>
            <FormControl error={!!formErrors?.suckPower}>
                <TextField
                    label="Elszívó hálózat teljesítménye (W)"
                    variant="standard"
                    type="number"
                    value={formData.suckPower}
                    onChange={(e) => handleChange('suckPower', e.target.value)}
                />
                {formErrors?.suckPower && (
                    <FormHelperText>{formErrors.suckPower}</FormHelperText>
                )}
            </FormControl>
            <FormControl error={!!formErrors?.blowRatio}>
                <TextField
                    label="Befúvó hálózat légszállítása (m3)"
                    variant="standard"
                    type="number"
                    value={formData.blowRatio}
                    onChange={(e) => handleChange('blowRatio', e.target.value)}
                />
                {formErrors?.blowRatio && (
                    <FormHelperText>{formErrors.blowRatio}</FormHelperText>
                )}
            </FormControl>
            <FormControl error={!!formErrors?.blowPower}>
                <TextField
                    label="Befúvó hálózat légszállítása (m3)"
                    variant="standard"
                    type="number"
                    value={formData.blowPower}
                    onChange={(e) => handleChange('blowPower', e.target.value)}
                />
                {formErrors?.blowPower && (
                    <FormHelperText>{formErrors.blowPower}</FormHelperText>
                )}
            </FormControl>
            <FormControl>
                <InputLabel id="retriever-select">
                    Hővisszanyerő működési módja
                </InputLabel>
                <Select
                    label="Hővisszanyerő működési módja"
                    labelId="retriever-select"
                    value={formData.retriever}
                    onChange={(e) => handleChange('retriever', e.target.value)}
                >
                    {Object.values(VentilationHeatRetrievers).map((e) => (
                        <MenuItem key={e} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            {formData.retriever !== VentilationHeatRetrievers.NONE ? (
                <FormControl error={!!formErrors?.retrieverYear}>
                    <TextField
                        label="Hővisszanyerő gyártási éve"
                        variant="standard"
                        type="number"
                        value={formData.retrieverYear}
                        onChange={(e) =>
                            handleChange('retrieverYear', e.target.value)
                        }
                    />
                    {formErrors?.retrieverYear && (
                        <FormHelperText>
                            {formErrors.retrieverYear}
                        </FormHelperText>
                    )}
                </FormControl>
            ) : (
                <></>
            )}
            <FormControl>
                <InputLabel id="insulation-select">
                    Légkezelő hálózat szigetelésének anyaga
                </InputLabel>
                <Select
                    label="Szigetelés anyaga"
                    labelId="insulation-select"
                    value={formData.insulationMaterial}
                    onChange={(e) =>
                        handleChange('insulationMaterial', e.target.value)
                    }
                >
                    {Object.values(VentilationInsulationMaterial).map((e) => (
                        <MenuItem key={e} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            {formData.insulationMaterial ===
            VentilationInsulationMaterial.NONE ? (
                <></>
            ) : (
                <FormControl error={!!formErrors?.insulationWidth}>
                    <TextField
                        label="Légkezelő hálózat szigetelésének vastagsága (mm)"
                        type="number"
                        variant="standard"
                        value={formData.insulationWidth}
                        onChange={(e) =>
                            handleChange('insulationWidth', e.target.value)
                        }
                    />
                    {formErrors?.insulationWidth && (
                        <FormHelperText>
                            {formErrors.insulationWidth}
                        </FormHelperText>
                    )}
                </FormControl>
            )}
            <FormControl>
                <InputLabel id="regulation-select">
                    Légkezelő szabályozása
                </InputLabel>
                <Select
                    label="Szabályozás"
                    labelId="regukation-select"
                    value={formData.regulation}
                    onChange={(e) => handleChange('regulation', e.target.value)}
                >
                    {Object.values(VentilationRegulation).map((e) => (
                        <MenuItem key={e} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <FormControl>
                <InputLabel id="running-select">
                    Légkezelő jellemző működtetése
                </InputLabel>
                <Select
                    label="Működtetés"
                    labelId="running-select"
                    value={formData.running}
                    onChange={(e) => handleChange('running', e.target.value)}
                >
                    {Object.values(VentilationRunning).map((e) => (
                        <MenuItem key={e} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <FormControl>
                <InputLabel id="heating-select">
                    Légkezelő utófűtéssel rendelkezik
                </InputLabel>
                <Select
                    label="Rendelkezik"
                    labelId="heating-select"
                    value={formData.heating ? 1 : 0}
                    onChange={(e) =>
                        handleChange('heating', Boolean(e.target.value))
                    }
                >
                    <MenuItem key={0} value={0}>
                        Nem
                    </MenuItem>
                    <MenuItem key={1} value={1}>
                        Igen
                    </MenuItem>
                </Select>
            </FormControl>
            {formData.heating ? (
                <FormControl error={!!formErrors?.heaterId}>
                    <InputLabel id="heater-select">
                        Hőtermelő kiválasztása
                    </InputLabel>
                    <Select
                        label="Hőtermelő"
                        labelId="heater-select"
                        value={formData.heaterId}
                        onChange={(e) =>
                            handleChange('heaterId', e.target.value)
                        }
                    >
                        {heaters
                            .filter(
                                (e) =>
                                    e.purpose === SystemPurpose.HEAT ||
                                    e.purpose === SystemPurpose.BOTH
                            )
                            .map((e) => (
                                <MenuItem key={e.id!} value={e.id!}>
                                    {e.name}
                                </MenuItem>
                            ))}
                    </Select>
                    {formErrors?.heaterId && (
                        <FormHelperText>{formErrors.heaterId}</FormHelperText>
                    )}
                </FormControl>
            ) : (
                <></>
            )}
            <FormControl>
                <InputLabel id="cooling-select">
                    Légkezelő utóhűtéssel rendelkezik
                </InputLabel>
                <Select
                    label="Rendelkezik"
                    labelId="cooling-select"
                    value={formData.cooling ? 1 : 0}
                    onChange={(e) =>
                        handleChange('cooling', Boolean(e.target.value))
                    }
                >
                    <MenuItem key={0} value={0}>
                        Nem
                    </MenuItem>
                    <MenuItem key={1} value={1}>
                        Igen
                    </MenuItem>
                </Select>
            </FormControl>
            {formData.cooling ? (
                <FormControl error={!!formErrors?.coolerId}>
                    <InputLabel id="cooler-select">
                        Hűtőberendezés kiválasztása
                    </InputLabel>
                    <Select
                        label="Hűtőberendezés"
                        labelId="cooler-select"
                        value={formData.coolingId}
                        onChange={(e) =>
                            handleChange('coolingId', e.target.value)
                        }
                    >
                        {heaters
                            .filter(
                                (e) =>
                                    e.purpose === SystemPurpose.COOL ||
                                    e.purpose === SystemPurpose.BOTH
                            )
                            .map((e) => (
                                <MenuItem key={e.id!} value={e.id!}>
                                    {e.name}
                                </MenuItem>
                            ))}
                    </Select>
                    {formErrors?.coolerId && (
                        <FormHelperText>{formErrors.coolerId}</FormHelperText>
                    )}
                </FormControl>
            ) : (
                <></>
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
