import {
    Box,
    Button,
    Checkbox,
    FormControl,
    FormHelperText,
    InputLabel,
    MenuItem,
    Select,
    TextField,
    Typography,
} from '@mui/material'
import type {
    BuildingShort,
    ServicedBuildingShort,
} from '../model/Building.model'
import {
    EMITTER_PURPOSE_TO_TYPE,
    EMITTER_TYPE_TO_REGULATION,
    EmitterHmvRegulation,
    EmitterIndoorUnitPlacement,
    type EmitterErrors,
    type EmitterFormData,
} from '../model/Emitter.model'
import { SystemPurpose } from '../model/System.model'
import { useState } from 'react'
import { ElectricCalcRefrigerant } from '../model/Heater.model'
import CloudUploadIcon from '@mui/icons-material/CloudUpload'

export default function EmitterForm(props: {
    currentActiveEmitter: EmitterFormData
    handleActiveEmitterChange: (
        field: keyof EmitterFormData,
        value:
            | string
            | number
            | string[]
            | boolean
            | File
            | null
            | ServicedBuildingShort[]
    ) => void
    buildings: Array<BuildingShort>
    systemPurpose: SystemPurpose
    emitterErrors: EmitterErrors | null
}) {
    const [currentEmitterType, setCurrentEmitterType] = useState<string | null>(
        props.currentActiveEmitter.type
    )

    return (
        <Box sx={{ display: 'flex', flexDirection: 'column', gap: 1.5 }}>
            <Typography
                variant="h6"
                sx={{
                    borderBottom: '1px solid',
                    borderColor: 'divider',
                    pb: 1,
                }}
            >
                {props.currentActiveEmitter.name || 'Névtelen berendezés'}{' '}
                részletes adatai
            </Typography>

            <FormControl
                error={
                    props.emitterErrors !== null && !!props.emitterErrors.name
                }
            >
                <TextField
                    label="Megnevezés"
                    variant="outlined"
                    value={props.currentActiveEmitter.name}
                    onChange={(e) => {
                        props.handleActiveEmitterChange('name', e.target.value)
                    }}
                />
                {!!props.emitterErrors && props.emitterErrors.name && (
                    <FormHelperText>{props.emitterErrors.name}</FormHelperText>
                )}
            </FormControl>
            <FormControl
                error={
                    props.emitterErrors !== null &&
                    !!props.emitterErrors.building
                }
            >
                <InputLabel id="building-emitter-label">Épület</InputLabel>
                <Select
                    labelId="building-emitter-label"
                    label="Épület"
                    value={props.currentActiveEmitter.building}
                    onChange={(e) => {
                        props.handleActiveEmitterChange(
                            'building',
                            e.target.value
                        )
                    }}
                >
                    {props.buildings.map((e) => (
                        <MenuItem key={e.id} value={e.id}>
                            {e.name}
                        </MenuItem>
                    ))}
                </Select>
                {!!props.emitterErrors && props.emitterErrors.building && (
                    <FormHelperText>
                        {props.emitterErrors.building}
                    </FormHelperText>
                )}
            </FormControl>
            <FormControl
                error={
                    props.emitterErrors !== null &&
                    !!props.emitterErrors.servicedBuilding
                }
            >
                <InputLabel id="pump-serviced-label">
                    Kiszolgált épületek
                </InputLabel>
                <Select
                    labelId="pump-serviced-label"
                    multiple
                    value={props.currentActiveEmitter.servicedBuilding.map(
                        (s) => s.buildingId
                    )}
                    label="Kiszolgált épületek"
                    onChange={(e) => {
                        const selectedIds = e.target.value as string[]
                        const objects: Array<ServicedBuildingShort> =
                            props.buildings
                                .filter((e) => selectedIds.includes(e.id))
                                .map((e: BuildingShort) => ({
                                    buildingId: e.id,
                                    name: e.name,
                                    servicedSize: 0,
                                }))
                        props.handleActiveEmitterChange(
                            'servicedBuilding',
                            objects
                        )
                    }}
                >
                    {props.buildings.map((s) => (
                        <MenuItem key={s.id as string} value={s.id as string}>
                            {s.name}
                        </MenuItem>
                    ))}
                </Select>
                {!!props.emitterErrors &&
                    props.emitterErrors.servicedBuilding && (
                        <FormHelperText>
                            {props.emitterErrors.servicedBuilding}
                        </FormHelperText>
                    )}
            </FormControl>
            {props.currentActiveEmitter.servicedBuilding.map(
                (e: ServicedBuildingShort) => {
                    return (
                        <FormControl>
                            <TextField
                                label={e.name + '-ben kiszolgált terület'}
                                type="number"
                                variant="outlined"
                                value={e.servicedSize}
                                onChange={(event) => {
                                    e.servicedSize = Number(event.target.value)
                                    props.handleActiveEmitterChange(
                                        'servicedBuilding',
                                        props.currentActiveEmitter
                                            .servicedBuilding
                                    )
                                }}
                            />
                        </FormControl>
                    )
                }
            )}
            <FormControl>
                <InputLabel id="emitter-type-label">Hőleadó típusa</InputLabel>
                <Select
                    labelId="emitter-type-label"
                    label="Hőleadó típusa"
                    value={props.currentActiveEmitter.type}
                    onChange={(e) => {
                        props.handleActiveEmitterChange('type', e.target.value)
                        setCurrentEmitterType(e.target.value)
                    }}
                >
                    {(props.systemPurpose === SystemPurpose.BOTH
                        ? EMITTER_PURPOSE_TO_TYPE.cool.concat(
                              EMITTER_PURPOSE_TO_TYPE.heat
                          )
                        : props.systemPurpose === SystemPurpose.COOL
                          ? EMITTER_PURPOSE_TO_TYPE.cool
                          : EMITTER_PURPOSE_TO_TYPE.heat
                    ).map((e) => (
                        <MenuItem key={e} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <FormControl
                error={
                    props.emitterErrors !== null && !!props.emitterErrors.amount
                }
            >
                <TextField
                    variant="outlined"
                    type="number"
                    label="Mennyiség"
                    value={props.currentActiveEmitter.amount}
                    onChange={(e) =>
                        props.handleActiveEmitterChange(
                            'amount',
                            e.target.value
                        )
                    }
                />
                {!!props.emitterErrors && props.emitterErrors.amount && (
                    <FormHelperText>
                        {props.emitterErrors.amount}
                    </FormHelperText>
                )}
            </FormControl>
            <FormControl
                error={
                    props.emitterErrors !== null &&
                    !!props.emitterErrors.forwardHeat
                }
            >
                <TextField
                    variant="outlined"
                    type="number"
                    label="Rendszer előremenő hőmérséklete (C°)"
                    value={props.currentActiveEmitter.forwardHeat}
                    onChange={(e) =>
                        props.handleActiveEmitterChange(
                            'forwardHeat',
                            e.target.value
                        )
                    }
                />
                {!!props.emitterErrors && props.emitterErrors.forwardHeat && (
                    <FormHelperText>
                        {props.emitterErrors.forwardHeat}
                    </FormHelperText>
                )}
            </FormControl>
            <FormControl
                error={
                    props.emitterErrors !== null &&
                    !!props.emitterErrors.backHeat
                }
            >
                <TextField
                    variant="outlined"
                    type="number"
                    label="Rendszer visszatérő hőmérséklete (C°)"
                    value={props.currentActiveEmitter.backHeat}
                    onChange={(e) =>
                        props.handleActiveEmitterChange(
                            'backHeat',
                            e.target.value
                        )
                    }
                />
                {!!props.emitterErrors && props.emitterErrors.backHeat && (
                    <FormHelperText>
                        {props.emitterErrors.backHeat}
                    </FormHelperText>
                )}
            </FormControl>
            <FormControl>
                <InputLabel id="emitter-state-label">
                    Hőleadó leírása
                </InputLabel>
                <Select
                    labelId="emitter-state-label"
                    label="Hőleadó állapota"
                    value={props.currentActiveEmitter.state}
                    onChange={(e) =>
                        props.handleActiveEmitterChange('state', e.target.value)
                    }
                >
                    {currentEmitterType ? (
                        EMITTER_TYPE_TO_REGULATION[currentEmitterType].map(
                            (e) => (
                                <MenuItem key={e} value={e}>
                                    {e}
                                </MenuItem>
                            )
                        )
                    ) : (
                        <></>
                    )}
                </Select>
            </FormControl>
            {currentEmitterType === 'VRV/VRF' ? (
                <Box
                    sx={{ display: 'flex', flexDirection: 'column', gap: 1.5 }}
                >
                    <FormControl>
                        <InputLabel id="vrv-refrigerant">
                            VRV/VRF hűtőfolyadéka
                        </InputLabel>
                        <Select
                            label="VRV/VRF hűtőfolyadéka"
                            labelId="vrv-refrigerant"
                            value={props.currentActiveEmitter.vrvRefrigerant}
                            onChange={(e) =>
                                props.handleActiveEmitterChange(
                                    'vrvRefrigerant',
                                    e.target.value as ElectricCalcRefrigerant
                                )
                            }
                        >
                            {Object.values(ElectricCalcRefrigerant).map((e) => (
                                <MenuItem key={e} value={e}>
                                    {e}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>
                    <FormControl>
                        <InputLabel id="vrv-inside-type">
                            VRV/VRF belső elhelyezés
                        </InputLabel>
                        <Select
                            label="VRV/VRF belső elhelyezés"
                            labelId="vrv-refrigerant"
                            value={props.currentActiveEmitter.vrvInsideType}
                            onChange={(e) =>
                                props.handleActiveEmitterChange(
                                    'vrvInsideType',
                                    e.target.value as EmitterIndoorUnitPlacement
                                )
                            }
                        >
                            {Object.values(EmitterIndoorUnitPlacement).map(
                                (e) => (
                                    <MenuItem key={e} value={e}>
                                        {e}
                                    </MenuItem>
                                )
                            )}
                        </Select>
                    </FormControl>
                </Box>
            ) : (
                <></>
            )}
            {currentEmitterType === 'HMV' ? (
                <Box
                    sx={{ display: 'flex', flexDirection: 'column', gap: 1.5 }}
                >
                    <FormControl>
                        <Checkbox
                            checked={props.currentActiveEmitter.insideRoom}
                            onChange={() =>
                                props.handleActiveEmitterChange(
                                    'insideRoom',
                                    !props.currentActiveEmitter.insideRoom
                                )
                            }
                            slotProps={{
                                input: { 'aria-label': 'Fűtőtt téren belül' },
                            }}
                        />
                    </FormControl>
                    <FormControl>
                        <Checkbox
                            checked={props.currentActiveEmitter.circulation}
                            onChange={() =>
                                props.handleActiveEmitterChange(
                                    'circulation',
                                    !props.currentActiveEmitter.circulation
                                )
                            }
                            slotProps={{
                                input: { 'aria-label': 'Van cirkuláció' },
                            }}
                        />
                    </FormControl>
                    <FormControl>
                        <InputLabel id="hmv-regulation-label">
                            HMV cirkuláció szabályzása
                        </InputLabel>
                        <Select
                            label="HMV cirkuláció szabályzása"
                            labelId="hmv-regulation-label"
                            value={props.currentActiveEmitter.hmvRegulation}
                            onChange={(e) =>
                                props.handleActiveEmitterChange(
                                    'hmvRegulation',
                                    e.target.value as EmitterHmvRegulation
                                )
                            }
                        >
                            {Object.values(EmitterHmvRegulation).map((e) => (
                                <MenuItem key={e} value={e}>
                                    {e}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>
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
                Fotó a hőleadóról
                <input
                    type="file"
                    accept="image/*"
                    style={{ display: 'none' }}
                    onChange={(e) => {
                        if (e.target.files && e.target.files[0]) {
                            props.handleActiveEmitterChange(
                                'imageFile',
                                e.target.files[0]
                            )
                        }
                    }}
                />
            </Button>
            {!!props.emitterErrors && props.emitterErrors.imageFile && (
                <Typography
                    variant="caption"
                    color="error"
                    sx={{ mt: -2, pl: 2 }}
                >
                    {props.emitterErrors.imageFile}
                </Typography>
            )}
            {props.currentActiveEmitter &&
                props.currentActiveEmitter.imageFile && (
                    <Typography
                        variant="body2"
                        sx={{
                            textAlign: 'center',
                            color: 'text.secondary',
                            mt: -1,
                        }}
                    >
                        Kiválasztott fájl:{' '}
                        <strong>
                            {props.currentActiveEmitter.imageFile.name}
                        </strong>
                    </Typography>
                )}
        </Box>
    )
}
