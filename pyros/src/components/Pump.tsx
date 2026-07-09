import {
  Box,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  TextField,
  Typography,
} from "@mui/material";
import { PumpSetting, PumpTypes, type PumpFormData } from "../model/Pump.model";
import type {
  BuildingShort,
  ServicedBuildingShort,
} from "../model/Building.model";

export default function PumpForm(props: {
  currentActivePump: PumpFormData;
  handleActivePumpChange: (
    field: keyof PumpFormData,
    value:
      | string
      | number
      | null
      | string[]
      | boolean
      | ServicedBuildingShort[],
  ) => void;
  buildings: Array<BuildingShort>;
}) {
  return (
    <Box sx={{ display: "flex", flexDirection: "column", gap: 1.5 }}>
      <Typography
        variant="h6"
        sx={{ borderBottom: "1px solid", borderColor: "divider", pb: 1 }}
      >
        {props.currentActivePump.name || "Névtelen berendezés"} részletes adatai
      </Typography>
      <FormControl>
        <TextField
          variant="outlined"
          label="Megnevezés"
          value={props.currentActivePump.name}
          onChange={(e) => props.handleActivePumpChange("name", e.target.value)}
        ></TextField>
      </FormControl>
      <FormControl fullWidth size="small">
        <InputLabel id="pump-building-label">Épület</InputLabel>
        <Select
          labelId="pump-building-label"
          label="Épület"
          value={props.currentActivePump.building || ""}
          onChange={(e) =>
            props.handleActivePumpChange("building", e.target.value)
          }
        >
          {props.buildings.map((e: BuildingShort) => {
            return <MenuItem value={e.id}>{e.name}</MenuItem>;
          })}
        </Select>
      </FormControl>
      <FormControl>
        <InputLabel id="pump-serviced-label">Kiszolgált épületek</InputLabel>
        <Select
          labelId="pump-serviced-label"
          multiple
          value={props.currentActivePump.servicedBuilding.map(
            (s) => s.buildingId,
          )}
          label="Kiszolgált épületek"
          onChange={(e) => {
            const selectedIds = e.target.value as string[];
            const objects: Array<ServicedBuildingShort> = props.buildings.filter((e) => selectedIds.includes(e.id)).map((e: BuildingShort) => ({buildingId: e.id, name: e.name, servicedSize: 0}))
            props.handleActivePumpChange("servicedBuilding", objects);
          }}
        >
          {props.buildings.map((s) => (
            <MenuItem key={s.id as string} value={s.id as string}>
              {s.name}
            </MenuItem>
          ))}
        </Select>
      </FormControl>
      {props.currentActivePump.servicedBuilding.map((e: ServicedBuildingShort) => {
        return (
            <FormControl>
                <TextField
                    label={e.name + "-ben kiszolgált terület"}
                    type="number"
                    variant="outlined"
                    value={e.servicedSize}
                    onChange={(event) => {
                        e.servicedSize = Number(event.target.value)
                        props.handleActivePumpChange("servicedBuilding", props.currentActivePump.servicedBuilding)
                    }}
                />
            </FormControl>
        );
      })}
      <FormControl>
        <TextField
            label="Gyártó"
            variant="outlined"
            value={props.currentActivePump.manufacturor}
            onChange={(e) => {
                props.handleActivePumpChange("manufacturor", e.target.value);
            }}
        />
      </FormControl>
      <FormControl>
        <TextField
            label="Típus"
            variant="outlined"
            value={props.currentActivePump.type}
            onChange={(e) => {
                props.handleActivePumpChange("type", e.target.value)
            }}
        />
      </FormControl>
      <FormControl>
        <InputLabel id="pump-archetype-label">Szivattyú jellege</InputLabel>
        <Select
            labelId="pump-archetype-label"
            label="Szivattyú jellege"
            value={props.currentActivePump.archetype}
            onChange={(e) => {
                props.handleActivePumpChange("archetype", e.target.value as PumpTypes)
            }}
        >
            {
            Object.values(PumpTypes).map((e) => (
                <MenuItem key={e} value={e}>{e}</MenuItem>
            ))
            }
        </Select>
      </FormControl>
      <FormControl>
        <InputLabel id="pump-archetype-setting-label">Szivattyú beállítása</InputLabel>
        <Select
            labelId="pump-archetype-setting-label"
            label="Szivattyú beállítása"
            value={props.currentActivePump.archetypeSetting}
            onChange={(e) => {
                props.handleActivePumpChange("archetypeSetting", e.target.value as PumpSetting)
            }}
        >
            {
                Object.values(PumpSetting).map((e) => (
                    <MenuItem key={e} value={e}>{e}</MenuItem>
                ))
            }
        </Select>
      </FormControl>
      <FormControl>
        <TextField
            label="Gyári szám"
            variant="outlined"
            value={props.currentActivePump.serialNumber}
            onChange={(e) => {
                props.handleActivePumpChange("serialNumber", e.target.value)
            }}
        />
      </FormControl>
      <FormControl>
        <TextField
            label="Villamos energia felvétel (W)"
            variant="outlined"
            type="number"
            value={props.currentActivePump.powerUsage}
            onChange={(e) => {
                props.handleActivePumpChange("powerUsage", e.target.value)
            }}
        />
      </FormControl>
      {
        /**
         * TODO: Image upload
         */
      }
    </Box>
  );
}
