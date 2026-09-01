import {
    Box,
    Button,
    FormControl,
    FormHelperText,
    InputLabel,
    MenuItem,
    Select,
    TextField,
    Typography,
} from '@mui/material'
import {
    PumpSetting,
    PumpTypes,
    type PumpErrors,
    type PumpFormData,
} from '../model/Pump.model'
import type {
    BuildingShort,
    ServicedBuildingShort,
} from '../model/Building.model'
import CloudUploadIcon from '@mui/icons-material/CloudUpload'

export default function PumpForm(props: {
    currentActivePump: PumpFormData
    handleActivePumpChange: (
        field: keyof PumpFormData,
        value:
            | string
            | number
            | null
            | string[]
            | boolean
            | File
            | ServicedBuildingShort[]
    ) => void
    buildings: Array<BuildingShort>
    pumpErrors: PumpErrors | null
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
                {props.currentActivePump.name || 'Névtelen berendezés'}{' '}
                részletes adatai
            </Typography>
            <FormControl
                error={props.pumpErrors !== null && !!props.pumpErrors.name}
            >
                <TextField
                    variant="outlined"
                    label="Megnevezés"
                    value={props.currentActivePump.name}
                    onChange={(e) =>
                        props.handleActivePumpChange('name', e.target.value)
                    }
                ></TextField>
                {!!props.pumpErrors && props.pumpErrors.name && (
                    <FormHelperText>{props.pumpErrors.name}</FormHelperText>
                )}
            </FormControl>
            <FormControl
                fullWidth
                size="small"
                error={props.pumpErrors !== null && !!props.pumpErrors.building}
            >
                <InputLabel id="pump-building-label">Épület</InputLabel>
                <Select
                    labelId="pump-building-label"
                    label="Épület"
                    value={props.currentActivePump.building || ''}
                    onChange={(e) =>
                        props.handleActivePumpChange('building', e.target.value)
                    }
                >
                    {props.buildings.map((e: BuildingShort) => {
                        return <MenuItem value={e.id}>{e.name}</MenuItem>
                    })}
                </Select>
                {!!props.pumpErrors && props.pumpErrors.building && (
                    <FormHelperText>{props.pumpErrors.building}</FormHelperText>
                )}
            </FormControl>
            <FormControl
                error={
                    props.pumpErrors !== null &&
                    !!props.pumpErrors.servicedBuilding
                }
            >
                <InputLabel id="pump-serviced-label">
                    Kiszolgált épületek
                </InputLabel>
                <Select
                    labelId="pump-serviced-label"
                    multiple
                    value={props.currentActivePump.servicedBuilding.map(
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
                        props.handleActivePumpChange(
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
                {!!props.pumpErrors && props.pumpErrors.servicedBuilding && (
                    <FormHelperText>
                        {props.pumpErrors.servicedBuilding}
                    </FormHelperText>
                )}
            </FormControl>
            {props.currentActivePump.servicedBuilding.map(
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
                                    props.handleActivePumpChange(
                                        'servicedBuilding',
                                        props.currentActivePump.servicedBuilding
                                    )
                                }}
                            />
                        </FormControl>
                    )
                }
            )}
            <FormControl>
                <InputLabel id="pump-archetype-label">
                    Szivattyú jellege
                </InputLabel>
                <Select
                    labelId="pump-archetype-label"
                    label="Szivattyú jellege"
                    value={props.currentActivePump.archetype}
                    onChange={(e) => {
                        props.handleActivePumpChange(
                            'archetype',
                            e.target.value as PumpTypes
                        )
                    }}
                >
                    {Object.values(PumpTypes).map((e) => (
                        <MenuItem key={e} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <FormControl>
                <InputLabel id="pump-archetype-setting-label">
                    Szivattyú beállítása
                </InputLabel>
                <Select
                    labelId="pump-archetype-setting-label"
                    label="Szivattyú beállítása"
                    value={props.currentActivePump.archetypeSetting}
                    onChange={(e) => {
                        props.handleActivePumpChange(
                            'archetypeSetting',
                            e.target.value as PumpSetting
                        )
                    }}
                >
                    {Object.values(PumpSetting).map((e) => (
                        <MenuItem key={e} value={e}>
                            {e}
                        </MenuItem>
                    ))}
                </Select>
            </FormControl>
            <Button
                component="label"
                variant="contained"
                color="inherit"
                startIcon={<CloudUploadIcon />}
                sx={{ mt: 1 }}
            >
                Fotó a szivattyúról
                <input
                    type="file"
                    accept="image/*"
                    style={{ display: 'none' }}
                    onChange={(e) => {
                        if (e.target.files && e.target.files[0]) {
                            props.handleActivePumpChange(
                                'imageFile',
                                e.target.files[0]
                            )
                        }
                    }}
                />
            </Button>
            {!!props.pumpErrors && props.pumpErrors.imageFile && (
                <Typography
                    variant="caption"
                    color="error"
                    sx={{ mt: -2, pl: 2 }}
                >
                    {props.pumpErrors.imageFile}
                </Typography>
            )}
            {props.currentActivePump && props.currentActivePump.imageFile && (
                <Typography
                    variant="body2"
                    sx={{
                        textAlign: 'center',
                        color: 'text.secondary',
                        mt: -1,
                    }}
                >
                    Kiválasztott fájl:{' '}
                    <strong>{props.currentActivePump.imageFile.name}</strong>
                </Typography>
            )}
        </Box>
    )
}
