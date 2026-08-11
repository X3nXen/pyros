import {
    Box,
    Typography,
    FormControl,
    TextField,
    InputLabel,
    Select,
    MenuItem,
    OutlinedInput,
    type Theme,
    FormHelperText,
    Button,
} from '@mui/material'
import theme from '../assets/theme'
import {
    HeaterDescriptions,
    DEVICE_FEATURES,
    HeaterFeature,
    ElectricCalcMode,
    ElectricCalcInstallation,
    ElectricCalcMedium,
    ElectricCalcSource,
    ElectricCalcRefrigerant,
    type HeaterFormData,
    type HeaterFormErrors,
    PurposeToCarrier,
    HEAT_CARRIER_TO_TYPE,
    COOL_CARRIER_TO_TYPE,
} from '../model/Heater.model'
import type { StandingsShort } from '../model/Standings.model'
import { type BuildingShort } from '../model/Building.model'
import { SystemPurpose } from '../model/System.model'
import CloudUploadIcon from '@mui/icons-material/CloudUpload'

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

export default function HeaterForm(props: {
    currentActiveHeater: HeaterFormData
    handleActiveHeaterChange: (
        field: keyof HeaterFormData,
        value: string | number | string[] | boolean | File | null
    ) => void
    allStandings: Array<StandingsShort>
    buildings: Array<BuildingShort>
    systemPurpose: SystemPurpose
    heaterIndex: number | null
    heaterErrors: HeaterFormErrors | null
}) {
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
                {props.currentActiveHeater.name || 'Névtelen berendezés'}{' '}
                részletes adatai
            </Typography>

            <FormControl
                fullWidth
                error={props.heaterErrors !== null && !!props.heaterErrors.name}
            >
                <TextField
                    label="Hőtermelő/hűtőberendezés megnevezése"
                    variant="outlined"
                    size="small"
                    value={props.currentActiveHeater.name}
                    onChange={(e) =>
                        props.handleActiveHeaterChange('name', e.target.value)
                    }
                />
                {!!props.heaterErrors && props.heaterErrors.name && (
                    <FormHelperText>{props.heaterErrors.name}</FormHelperText>
                )}
            </FormControl>

            <FormControl
                error={
                    props.heaterErrors !== null && !!props.heaterErrors.standing
                }
            >
                <InputLabel id="heater-standings">Hozzárendelt mérő</InputLabel>
                <Select
                    labelId="heater-standings"
                    value={props.currentActiveHeater.standing}
                    label="Hozzárendelt mérő"
                    onChange={(e) =>
                        props.handleActiveHeaterChange(
                            'standing',
                            e.target.value
                        )
                    }
                >
                    {props.allStandings.map((e: StandingsShort) => {
                        return (
                            <MenuItem value={e.id as string}>{e.name}</MenuItem>
                        )
                    })}
                </Select>
                {!!props.heaterErrors && props.heaterErrors.standing && (
                    <FormHelperText>
                        {props.heaterErrors.standing}
                    </FormHelperText>
                )}
            </FormControl>
            <FormControl
                fullWidth
                size="small"
                error={
                    props.heaterErrors !== null && !!props.heaterErrors.building
                }
            >
                <InputLabel id="heater-building-label">Épület</InputLabel>
                <Select
                    labelId="heater-building-label"
                    label="Épület"
                    value={props.currentActiveHeater.building || ''}
                    onChange={(e) =>
                        props.handleActiveHeaterChange(
                            'building',
                            e.target.value
                        )
                    }
                >
                    {props.buildings.map((e: BuildingShort) => {
                        return <MenuItem value={e.id}>{e.name}</MenuItem>
                    })}
                    <MenuItem value="-9999">Kültér</MenuItem>
                </Select>
                {!!props.heaterErrors && props.heaterErrors.building && (
                    <FormHelperText>
                        {props.heaterErrors.building}
                    </FormHelperText>
                )}
            </FormControl>
            <FormControl
                error={
                    props.heaterErrors !== null &&
                    !!props.heaterErrors.servicedBuilding
                }
            >
                <InputLabel id="heater-serviced-label">
                    Kiszolgált épületek
                </InputLabel>
                <Select
                    labelId="heater-serviced-label"
                    id="heater-serviced-multiple"
                    multiple
                    value={props.currentActiveHeater.servicedBuilding || []}
                    onChange={(e) =>
                        props.handleActiveHeaterChange(
                            'servicedBuilding',
                            e.target.value
                        )
                    }
                    input={<OutlinedInput label="Kiszolgált épületek" />}
                    MenuProps={MenuProps}
                    renderValue={(selectedIds) => {
                        return selectedIds
                            .map(
                                (id) =>
                                    props.buildings.find(
                                        (building) => building.id === id
                                    )?.name
                            )
                            .filter(Boolean)
                            .join(', ')
                    }}
                >
                    {props.buildings.map((building) => {
                        const buildingId = building.id || ''
                        return (
                            <MenuItem
                                key={buildingId}
                                value={buildingId}
                                style={getStyles(
                                    buildingId,
                                    props.currentActiveHeater
                                        .servicedBuilding || [],
                                    theme
                                )}
                            >
                                {building.name}
                            </MenuItem>
                        )
                    })}
                </Select>
                {!!props.heaterErrors &&
                    props.heaterErrors.servicedBuilding && (
                        <FormHelperText>
                            {props.heaterErrors.servicedBuilding}
                        </FormHelperText>
                    )}
            </FormControl>

            <FormControl
                fullWidth
                error={
                    props.heaterErrors !== null &&
                    !!props.heaterErrors.manufacturor
                }
            >
                <TextField
                    label="Gyártó"
                    variant="outlined"
                    size="small"
                    value={props.currentActiveHeater.manufacturor}
                    onChange={(e) =>
                        props.handleActiveHeaterChange(
                            'manufacturor',
                            e.target.value
                        )
                    }
                />
                {!!props.heaterErrors && props.heaterErrors.manufacturor && (
                    <FormHelperText>
                        {props.heaterErrors.manufacturor}
                    </FormHelperText>
                )}
            </FormControl>

            <FormControl fullWidth>
                <TextField
                    label="Gyártási év"
                    type="number"
                    variant="outlined"
                    size="small"
                    value={props.currentActiveHeater.year}
                    onChange={(e) =>
                        props.handleActiveHeaterChange(
                            'year',
                            Number(e.target.value)
                        )
                    }
                />
            </FormControl>

            <FormControl
                fullWidth
                error={
                    props.heaterErrors !== null && !!props.heaterErrors.serial
                }
            >
                <TextField
                    label="Gyári szám"
                    variant="outlined"
                    size="small"
                    value={props.currentActiveHeater.serial}
                    onChange={(e) =>
                        props.handleActiveHeaterChange('serial', e.target.value)
                    }
                />
                {!!props.heaterErrors && props.heaterErrors.serial && (
                    <FormHelperText>{props.heaterErrors.serial}</FormHelperText>
                )}
            </FormControl>

            <FormControl
                fullWidth
                error={props.heaterErrors !== null && !!props.heaterErrors.type}
            >
                <TextField
                    label="Típus"
                    variant="outlined"
                    size="small"
                    value={props.currentActiveHeater.type}
                    onChange={(e) =>
                        props.handleActiveHeaterChange('type', e.target.value)
                    }
                />
                {!!props.heaterErrors && props.heaterErrors.type && (
                    <FormHelperText>{props.heaterErrors.type}</FormHelperText>
                )}
            </FormControl>

            <FormControl fullWidth size="small">
                <InputLabel id="heater-state-label">Állapot</InputLabel>
                <Select
                    labelId="heater-state-label"
                    label="Állapot"
                    value={props.currentActiveHeater.state}
                    onChange={(e) =>
                        props.handleActiveHeaterChange(
                            'state',
                            e.target.value as HeaterDescriptions
                        )
                    }
                >
                    {Object.keys(HeaterDescriptions).map((key) => (
                        <MenuItem value={key}>
                            {
                                HeaterDescriptions[
                                    key as keyof typeof HeaterDescriptions
                                ]
                            }
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>

            <FormControl fullWidth size="small">
                <InputLabel id="heater-carrier-label">
                    Energiahordozó
                </InputLabel>
                <Select
                    labelId="heater-carrier-label"
                    label="Energiahordozó"
                    value={props.currentActiveHeater.carrier}
                    onChange={(e) =>
                        props.handleActiveHeaterChange(
                            'carrier',
                            e.target.value
                        )
                    }
                >
                    {PurposeToCarrier[props.systemPurpose].map((val) => (
                        <MenuItem key={val} value={val}>
                            {val}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>

            <FormControl
                fullWidth
                size="small"
                error={
                    props.heaterErrors !== null &&
                    !!props.heaterErrors.heatingType
                }
            >
                <InputLabel id="heater-heatingtype-label">Jellege</InputLabel>
                <Select
                    labelId="heater-heatingtype-label"
                    label="Jellege"
                    value={props.currentActiveHeater.heatingType}
                    onChange={(e) =>
                        props.handleActiveHeaterChange(
                            'heatingType',
                            e.target.value
                        )
                    }
                >
                    {(props.systemPurpose === SystemPurpose.HEAT
                        ? HEAT_CARRIER_TO_TYPE[
                              props.currentActiveHeater.carrier
                          ]
                        : props.systemPurpose === SystemPurpose.COOL
                          ? COOL_CARRIER_TO_TYPE[
                                props.currentActiveHeater.carrier
                            ]
                          : Array.from(
                                new Set([
                                    ...(HEAT_CARRIER_TO_TYPE[
                                        props.currentActiveHeater.carrier
                                    ] ?? []),
                                    ...(COOL_CARRIER_TO_TYPE[
                                        props.currentActiveHeater.carrier
                                    ] ?? []),
                                ])
                            ))!.map((opt) => (
                        <MenuItem key={opt} value={opt}>
                            {opt}
                        </MenuItem>
                    ))}
                </Select>
                {!!props.heaterErrors && props.heaterErrors.heatingType && (
                    <FormHelperText>
                        {props.heaterErrors.heatingType}
                    </FormHelperText>
                )}
            </FormControl>

            <FormControl
                fullWidth
                error={
                    props.heaterErrors !== null && !!props.heaterErrors.maxPower
                }
            >
                <TextField
                    label="Max. bevitt teljesítmény (kW)"
                    type="number"
                    variant="outlined"
                    size="small"
                    value={props.currentActiveHeater.maxPower || ''}
                    onChange={(e) =>
                        props.handleActiveHeaterChange(
                            'maxPower',
                            Number(e.target.value)
                        )
                    }
                />
                {!!props.heaterErrors && props.heaterErrors.maxPower && (
                    <FormHelperText>
                        {props.heaterErrors.maxPower}
                    </FormHelperText>
                )}
            </FormControl>

            {(() => {
                const activeFeatures =
                    DEVICE_FEATURES[props.currentActiveHeater.heatingType] || []
                return (
                    <>
                        {activeFeatures.includes(HeaterFeature.SYSTEM_HEAT) && (
                            <>
                                <FormControl
                                    error={
                                        props.heaterErrors !== null &&
                                        !!props.heaterErrors.forwardHeat
                                    }
                                >
                                    <TextField
                                        label="Előremenő hőmérséklet (°C)"
                                        type="number"
                                        size="small"
                                        fullWidth
                                        value={
                                            props.currentActiveHeater
                                                .forwardHeat || ''
                                        }
                                        onChange={(e) =>
                                            props.handleActiveHeaterChange(
                                                'forwardHeat',
                                                Number(e.target.value)
                                            )
                                        }
                                    />
                                    {!!props.heaterErrors &&
                                        props.heaterErrors.forwardHeat && (
                                            <FormHelperText>
                                                {props.heaterErrors.forwardHeat}
                                            </FormHelperText>
                                        )}
                                </FormControl>
                                <FormControl
                                    error={
                                        props.heaterErrors !== null &&
                                        !!props.heaterErrors.backHeat
                                    }
                                >
                                    <TextField
                                        label="Visszatérő hőmérséklet (°C)"
                                        type="number"
                                        size="small"
                                        fullWidth
                                        value={
                                            props.currentActiveHeater
                                                .backHeat || ''
                                        }
                                        onChange={(e) =>
                                            props.handleActiveHeaterChange(
                                                'backHeat',
                                                Number(e.target.value)
                                            )
                                        }
                                    />
                                    {!!props.heaterErrors &&
                                        props.heaterErrors.backHeat && (
                                            <FormHelperText>
                                                {props.heaterErrors.backHeat}
                                            </FormHelperText>
                                        )}
                                </FormControl>
                            </>
                        )}

                        {activeFeatures.includes(
                            HeaterFeature.ELECTRIC_EFFICIENCY
                        ) && (
                            <>
                                <FormControl fullWidth size="small">
                                    <InputLabel id="base-type-label">
                                        Működési mód
                                    </InputLabel>
                                    <Select
                                        labelId="base-type-label"
                                        label="Működési mód"
                                        value={
                                            props.currentActiveHeater.baseType
                                        }
                                        onChange={(e) =>
                                            props.handleActiveHeaterChange(
                                                'baseType',
                                                e.target
                                                    .value as ElectricCalcMode
                                            )
                                        }
                                    >
                                        {Object.values(ElectricCalcMode).map(
                                            (v) => (
                                                <MenuItem key={v} value={v}>
                                                    {v}
                                                </MenuItem>
                                            )
                                        )}
                                    </Select>
                                </FormControl>

                                <FormControl fullWidth size="small">
                                    <InputLabel id="placement-type-label">
                                        Kültéri telepítési körülmények
                                    </InputLabel>
                                    <Select
                                        labelId="placement-type-label"
                                        label="Kültéri telepítési körülmények"
                                        value={
                                            props.currentActiveHeater
                                                .placementType
                                        }
                                        onChange={(e) =>
                                            props.handleActiveHeaterChange(
                                                'placementType',
                                                e.target
                                                    .value as ElectricCalcInstallation
                                            )
                                        }
                                    >
                                        {Object.values(
                                            ElectricCalcInstallation
                                        ).map((v) => (
                                            <MenuItem key={v} value={v}>
                                                {v}
                                            </MenuItem>
                                        ))}
                                    </Select>
                                </FormControl>

                                <FormControl fullWidth size="small">
                                    <InputLabel id="ambient-medium-label">
                                        Kültéri berendezés hőforrása
                                    </InputLabel>
                                    <Select
                                        labelId="ambient-medium-label"
                                        label="Kültéri berendezés hőforrása"
                                        value={
                                            props.currentActiveHeater
                                                .ambientMedium
                                        }
                                        onChange={(e) =>
                                            props.handleActiveHeaterChange(
                                                'ambientMedium',
                                                e.target
                                                    .value as ElectricCalcMedium
                                            )
                                        }
                                    >
                                        {Object.values(ElectricCalcMedium).map(
                                            (v) => (
                                                <MenuItem key={v} value={v}>
                                                    {v}
                                                </MenuItem>
                                            )
                                        )}
                                    </Select>
                                </FormControl>

                                <FormControl fullWidth size="small">
                                    <InputLabel id="heat-transfer-label">
                                        Hőleadás közege
                                    </InputLabel>
                                    <Select
                                        labelId="heat-transfer-label"
                                        label="Hőleadás közege"
                                        value={
                                            props.currentActiveHeater
                                                .heatTransfer
                                        }
                                        onChange={(e) =>
                                            props.handleActiveHeaterChange(
                                                'heatTransfer',
                                                e.target
                                                    .value as ElectricCalcSource
                                            )
                                        }
                                    >
                                        {Object.values(ElectricCalcSource).map(
                                            (v) => (
                                                <MenuItem key={v} value={v}>
                                                    {v}
                                                </MenuItem>
                                            )
                                        )}
                                    </Select>
                                </FormControl>

                                <FormControl fullWidth size="small">
                                    <InputLabel id="refrigerant-label">
                                        Hűtőközeg
                                    </InputLabel>
                                    <Select
                                        labelId="refrigerant-label"
                                        label="Hűtőközeg"
                                        value={
                                            props.currentActiveHeater
                                                .refrigerant
                                        }
                                        onChange={(e) =>
                                            props.handleActiveHeaterChange(
                                                'refrigerant',
                                                e.target
                                                    .value as ElectricCalcRefrigerant
                                            )
                                        }
                                    >
                                        {Object.values(
                                            ElectricCalcRefrigerant
                                        ).map((v) => (
                                            <MenuItem key={v} value={v}>
                                                {v}
                                            </MenuItem>
                                        ))}
                                    </Select>
                                </FormControl>
                            </>
                        )}
                    </>
                )
            })()}
            {(props.systemPurpose === SystemPurpose.COOL ||
                props.systemPurpose === SystemPurpose.BOTH) && (
                <>
                    <label
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 8,
                            fontSize: '0.9rem',
                        }}
                    >
                        <input
                            type="checkbox"
                            checked={props.currentActiveHeater.heatLoss}
                            onChange={(e) =>
                                props.handleActiveHeaterChange(
                                    'heatLoss',
                                    e.target.checked
                                )
                            }
                        />
                        Van hulladékhő hasznosítás
                    </label>
                    <label
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 8,
                            fontSize: '0.9rem',
                        }}
                    >
                        <input
                            type="checkbox"
                            checked={props.currentActiveHeater.couldHeatLoss}
                            onChange={(e) =>
                                props.handleActiveHeaterChange(
                                    'couldHeatLoss',
                                    e.target.checked
                                )
                            }
                        />
                        Van lehetőség hulladékhő hasznosításra
                    </label>
                </>
            )}

            <label
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: 8,
                    fontSize: '0.9rem',
                }}
            >
                <input
                    type="checkbox"
                    checked={props.currentActiveHeater.oversized}
                    onChange={(e) =>
                        props.handleActiveHeaterChange(
                            'oversized',
                            e.target.checked
                        )
                    }
                />
                Hőtermelő/hűtőberendezés túlméretezett
            </label>

            {props.currentActiveHeater.oversized && (
                <FormControl
                    fullWidth
                    error={
                        props.heaterErrors !== null &&
                        !!props.heaterErrors.oversizeRatio
                    }
                >
                    <TextField
                        label="Túlméretezettség mértéke (%)"
                        type="number"
                        variant="outlined"
                        size="small"
                        value={props.currentActiveHeater.oversizeRatio || ''}
                        onChange={(e) =>
                            props.handleActiveHeaterChange(
                                'oversizeRatio',
                                Number(e.target.value)
                            )
                        }
                    />
                    {!!props.heaterErrors &&
                        props.heaterErrors.oversizeRatio && (
                            <FormHelperText>
                                {props.heaterErrors.oversizeRatio}
                            </FormHelperText>
                        )}
                </FormControl>
            )}
            <Button
                component="label"
                variant="contained"
                color="inherit"
                startIcon={<CloudUploadIcon />}
                sx={{ mt: 1 }}
            >
                Fotó a hőtermelőről
                <input
                    type="file"
                    accept="image/*"
                    style={{ display: 'none' }}
                    onChange={(e) => {
                        if (e.target.files && e.target.files[0]) {
                            props.handleActiveHeaterChange(
                                'imageFile',
                                e.target.files[0]
                            )
                        }
                    }}
                />
            </Button>
            {!!props.heaterErrors && props.heaterErrors.imageFile && (
                <Typography
                    variant="caption"
                    color="error"
                    sx={{ mt: -2, pl: 2 }}
                >
                    {props.heaterErrors.imageFile}
                </Typography>
            )}
            {props.currentActiveHeater &&
                props.currentActiveHeater.imageFile && (
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
                            {props.currentActiveHeater.imageFile.name}
                        </strong>
                    </Typography>
                )}
        </Box>
    )
}
