import {
  Box,
  Typography,
  FormControl,
  TextField,
  InputLabel,
  Select,
  MenuItem,
  OutlinedInput,
  Divider,
  type Theme,
} from "@mui/material";
import theme from "../assets/theme";
import {
  HeaterDescriptions,
  HeaterCarrier,
  HEATER_CARRIER_TO_TYPE,
  DEVICE_FEATURES,
  HeaterFeature,
  ElectricCalcMode,
  ElectricCalcInstallation,
  ElectricCalcMedium,
  ElectricCalcSource,
  ElectricCalcRefrigerant,
  type HeaterFormData,
} from "../model/Heater.model";
import type { StandingsShort } from "../model/Standings.model";
import { useState } from "react";
import { type BuildingShort } from "../model/Building.model";
import { SystemPurpose } from "../model/System.model";

function getStyles(id: string, selectedIds: string[], theme: Theme) {
  return {
    fontWeight: selectedIds.includes(id)
      ? theme.typography.fontWeightMedium
      : theme.typography.fontWeightRegular,
  };
}

const ITEM_HEIGHT = 48;
const ITEM_PADDING_TOP = 8;
const MenuProps = {
  slotProps: {
    paper: {
      style: {
        maxHeight: ITEM_HEIGHT * 4.5 + ITEM_PADDING_TOP,
        width: 250,
      },
    },
  },
};

export default function HeaterForm(props: {
  currentActiveHeater: HeaterFormData;
  handleActiveHeaterChange: (field: keyof HeaterFormData, value: string | number | string[] | boolean | null) => void;
  allStandings: Array<StandingsShort>;
  buildings: Array<BuildingShort>;
  systemPurpose: SystemPurpose;
  heaterIndex: number | null;
}) {
  const [currentCarrier, setCurrentCarrier] = useState<HeaterCarrier>(
    HeaterCarrier.NATURAL_GAS,
  );
  const [heatingTypes, setHeatingTypes] = useState<Array<string>>([]);

  return (
    <Box sx={{ display: "flex", flexDirection: "column", gap: 1.5 }}>
      <Typography
        variant="h6"
        sx={{ borderBottom: "1px solid", borderColor: "divider", pb: 1 }}
      >
        {props.currentActiveHeater.name || "Névtelen berendezés"} részletes
        adatai
      </Typography>

      <Box sx={{ display: "flex", flexDirection: "column", gap: 1.5 }}>
        <FormControl fullWidth>
          <TextField
            label="Hőtermelő/hűtőberendezés megnevezése"
            variant="outlined"
            size="small"
            value={props.currentActiveHeater.name}
            onChange={(e) =>
              props.handleActiveHeaterChange("name", e.target.value)
            }
          />
        </FormControl>

        <FormControl>
          <InputLabel id="heater-standings">Hozzárendelt mérő</InputLabel>
          <Select
            labelId="heater-standings"
            value={props.currentActiveHeater.standing}
            label="Hozzárendelt mérő"
            onChange={(e) =>
              props.handleActiveHeaterChange("standing", e.target.value)
            }
          >
            {props.allStandings.map((e: StandingsShort) => {
              return <MenuItem value={e.id as string}>{e.name}</MenuItem>;
            })}
          </Select>
        </FormControl>
        <FormControl fullWidth size="small">
          <InputLabel id="heater-building-label">
            Épület
          </InputLabel>
          <Select
            labelId="heater-building-label"
            label="Épület"
            value={props.currentActiveHeater.building || ""}
            onChange={(e) =>
              props.handleActiveHeaterChange("building", e.target.value)
            }
          >
            {props.buildings.map((e: BuildingShort) => {
              return <MenuItem value={e.id}>{e.name}</MenuItem>;
            })}
            <MenuItem value="-9999">Kültér</MenuItem>
          </Select>
        </FormControl>
        <FormControl>
          <InputLabel id="heater-serviced-label">
            Kiszolgált épületek
          </InputLabel>
          <Select
            labelId="heater-serviced-label"
            id="heater-serviced-multiple"
            multiple
            value={props.currentActiveHeater.servicedBuilding || []}
            onChange={(e) =>
              props.handleActiveHeaterChange("servicedBuilding", e.target.value)
            }
            input={<OutlinedInput label="Kiszolgált épületek" />}
            MenuProps={MenuProps}
            renderValue={(selectedIds) => {
              return selectedIds
                .map(
                  (id) =>
                    props.buildings.find((building) => building.id === id)
                      ?.name,
                )
                .filter(Boolean)
                .join(", ");
            }}
          >
            {props.buildings.map((building) => {
              const buildingId = building.id || "";
              return (
                <MenuItem
                  key={buildingId}
                  value={buildingId}
                  style={getStyles(
                    buildingId,
                    props.currentActiveHeater.servicedBuilding || [],
                    theme,
                  )}
                >
                  {building.name}
                </MenuItem>
              );
            })}
          </Select>
        </FormControl>
      </Box>

      <Box sx={{ display: "flex", flexDirection: "column", gap: 1.5 }}>
        <FormControl fullWidth>
          <TextField
            label="Gyártó"
            variant="outlined"
            size="small"
            value={props.currentActiveHeater.manufacturor}
            onChange={(e) =>
              props.handleActiveHeaterChange("manufacturor", e.target.value)
            }
          />
        </FormControl>

        <FormControl fullWidth>
          <TextField
            label="Gyártási év"
            type="number"
            variant="outlined"
            size="small"
            value={props.currentActiveHeater.year}
            onChange={(e) =>
              props.handleActiveHeaterChange("year", Number(e.target.value))
            }
          />
        </FormControl>

        <FormControl fullWidth>
          <TextField
            label="Gyári szám"
            variant="outlined"
            size="small"
            value={props.currentActiveHeater.serial}
            onChange={(e) =>
              props.handleActiveHeaterChange("serial", e.target.value)
            }
          />
        </FormControl>
      </Box>

      <Box sx={{ display: "flex", flexDirection: "column", gap: 1.5 }}>
        <FormControl fullWidth>
          <TextField
            label="Típus"
            variant="outlined"
            size="small"
            value={props.currentActiveHeater.type}
            onChange={(e) =>
              props.handleActiveHeaterChange("type", e.target.value)
            }
          />
        </FormControl>

        <FormControl fullWidth size="small">
          <InputLabel id="heater-state-label">Állapot</InputLabel>
          <Select
            labelId="heater-state-label"
            label="Állapot"
            value={props.currentActiveHeater.state}
            onChange={(e) =>
              props.handleActiveHeaterChange(
                "state",
                e.target.value as HeaterDescriptions,
              )
            }
          >
            {Object.keys(HeaterDescriptions).map((key) => (
              <MenuItem
                key={key}
                value={
                  HeaterDescriptions[key as keyof typeof HeaterDescriptions]
                }
              >
                {HeaterDescriptions[key as keyof typeof HeaterDescriptions]}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
      </Box>

      <Divider />

      <Box sx={{ display: "flex", flexDirection: "column", gap: 1.5 }}>
        <FormControl fullWidth size="small">
          <InputLabel id="heater-carrier-label">Energiahordozó</InputLabel>
          <Select
            labelId="heater-carrier-label"
            label="Energiahordozó"
            value={props.currentActiveHeater.carrier}
            onChange={(e) => {
              setCurrentCarrier(e.target.value);
              props.handleActiveHeaterChange("carrier", e.target.value);
              const availableHeatingTypes =
                props.systemPurpose === SystemPurpose.BOTH
                  ? HEATER_CARRIER_TO_TYPE[currentCarrier].heat.concat(
                      HEATER_CARRIER_TO_TYPE[currentCarrier].cool,
                    )
                  : props.systemPurpose === SystemPurpose.COOL
                    ? HEATER_CARRIER_TO_TYPE[currentCarrier].cool
                    : HEATER_CARRIER_TO_TYPE[currentCarrier].heat;
              setHeatingTypes(availableHeatingTypes);
            }}
          >
            {Object.values(HeaterCarrier).map((val) => (
              <MenuItem key={val} value={val}>
                {val}
              </MenuItem>
            ))}
          </Select>
        </FormControl>

        <FormControl fullWidth size="small">
          <InputLabel id="heater-heatingtype-label">Jellege</InputLabel>
          <Select
            labelId="heater-heatingtype-label"
            label="Jellege"
            value={props.currentActiveHeater.heatingType}
            onChange={(e) =>
              props.handleActiveHeaterChange("heatingType", e.target.value)
            }
          >
            {heatingTypes.map((opt) => (
              <MenuItem key={opt} value={opt}>
                {opt}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
      </Box>

      <FormControl fullWidth>
        <TextField
          label="Max. bevitt teljesítmény (kW)"
          type="number"
          variant="outlined"
          size="small"
          value={props.currentActiveHeater.maxPower || ""}
          onChange={(e) =>
            props.handleActiveHeaterChange("maxPower", Number(e.target.value))
          }
        />
      </FormControl>

      {(() => {
        const activeFeatures =
          DEVICE_FEATURES[props.currentActiveHeater.heatingType] || [];
        return (
          <>
            {activeFeatures.includes(HeaterFeature.SYSTEM_HEAT) && (
              <Box
                sx={{
                  display: "flex",
                  gap: 2,
                  bgcolor: "action.hover",
                  p: 2,
                  borderRadius: 1,
                }}
              >
                <TextField
                  label="Előremenő hőmérséklet (°C)"
                  type="number"
                  size="small"
                  fullWidth
                  value={props.currentActiveHeater.forwardHeat || ""}
                  onChange={(e) =>
                    props.handleActiveHeaterChange(
                      "forwardHeat",
                      Number(e.target.value),
                    )
                  }
                />
                <TextField
                  label="Visszatérő hőmérséklet (°C)"
                  type="number"
                  size="small"
                  fullWidth
                  value={props.currentActiveHeater.backHeat || ""}
                  onChange={(e) =>
                    props.handleActiveHeaterChange(
                      "backHeat",
                      Number(e.target.value),
                    )
                  }
                />
              </Box>
            )}

            {activeFeatures.includes(HeaterFeature.ELECTRIC_EFFICIENCY) && (
              <Box
                sx={{
                  display: "flex",
                  flexDirection: "column",
                  gap: 2,
                  bgcolor: "action.hover",
                  p: 2,
                  borderRadius: 1,
                }}
              >
                <Typography variant="subtitle2" color="primary">
                  Elektromos energetikai hatékonysági adatok
                </Typography>

                <FormControl fullWidth size="small">
                  <InputLabel id="base-type-label">Működési mód</InputLabel>
                  <Select
                    labelId="base-type-label"
                    label="Működési mód"
                    value={props.currentActiveHeater.baseType}
                    onChange={(e) =>
                      props.handleActiveHeaterChange(
                        "baseType",
                        e.target.value as ElectricCalcMode,
                      )
                    }
                  >
                    {Object.values(ElectricCalcMode).map((v) => (
                      <MenuItem key={v} value={v}>
                        {v}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>

                <FormControl fullWidth size="small">
                  <InputLabel id="placement-type-label">
                    Kültéri telepítési körülmények
                  </InputLabel>
                  <Select
                    labelId="placement-type-label"
                    label="Kültéri telepítési körülmények"
                    value={props.currentActiveHeater.placementType}
                    onChange={(e) =>
                      props.handleActiveHeaterChange(
                        "placementType",
                        e.target.value as ElectricCalcInstallation,
                      )
                    }
                  >
                    {Object.values(ElectricCalcInstallation).map((v) => (
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
                    value={props.currentActiveHeater.ambientMedium}
                    onChange={(e) =>
                      props.handleActiveHeaterChange(
                        "ambientMedium",
                        e.target.value as ElectricCalcMedium,
                      )
                    }
                  >
                    {Object.values(ElectricCalcMedium).map((v) => (
                      <MenuItem key={v} value={v}>
                        {v}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>

                <FormControl fullWidth size="small">
                  <InputLabel id="heat-transfer-label">
                    Hőleadás közege
                  </InputLabel>
                  <Select
                    labelId="heat-transfer-label"
                    label="Hőleadás közege"
                    value={props.currentActiveHeater.heatTransfer}
                    onChange={(e) =>
                      props.handleActiveHeaterChange(
                        "heatTransfer",
                        e.target.value as ElectricCalcSource,
                      )
                    }
                  >
                    {Object.values(ElectricCalcSource).map((v) => (
                      <MenuItem key={v} value={v}>
                        {v}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>

                <FormControl fullWidth size="small">
                  <InputLabel id="refrigerant-label">Hűtőközeg</InputLabel>
                  <Select
                    labelId="refrigerant-label"
                    label="Hűtőközeg"
                    value={props.currentActiveHeater.refrigerant}
                    onChange={(e) =>
                      props.handleActiveHeaterChange(
                        "refrigerant",
                        e.target.value as ElectricCalcRefrigerant,
                      )
                    }
                  >
                    {Object.values(ElectricCalcRefrigerant).map((v) => (
                      <MenuItem key={v} value={v}>
                        {v}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Box>
            )}
          </>
        );
      })()}

      <Box
        sx={{
          bgcolor: "background.default",
          p: 2,
          borderRadius: 1,
          display: "flex",
          flexDirection: "column",
          gap: 1.5,
        }}
      >
        {(props.systemPurpose === SystemPurpose.COOL ||
          props.systemPurpose === SystemPurpose.BOTH) && (
          <Box sx={{ display: "flex", flexDirection: "column", gap: 1 }}>
            <label
              style={{
                display: "flex",
                alignItems: "center",
                gap: 8,
                fontSize: "0.9rem",
              }}
            >
              <input
                type="checkbox"
                checked={props.currentActiveHeater.heatLoss}
                onChange={(e) =>
                  props.handleActiveHeaterChange("heatLoss", e.target.checked)
                }
              />
              Van hulladékhő hasznosítás
            </label>
            <label
              style={{
                display: "flex",
                alignItems: "center",
                gap: 8,
                fontSize: "0.9rem",
              }}
            >
              <input
                type="checkbox"
                checked={props.currentActiveHeater.couldHeatLoss}
                onChange={(e) =>
                  props.handleActiveHeaterChange(
                    "couldHeatLoss",
                    e.target.checked,
                  )
                }
              />
              Van lehetőség hulladékhő hasznosításra
            </label>
          </Box>
        )}

        <label
          style={{
            display: "flex",
            alignItems: "center",
            gap: 8,
            fontSize: "0.9rem",
          }}
        >
          <input
            type="checkbox"
            checked={props.currentActiveHeater.oversized}
            onChange={(e) =>
              props.handleActiveHeaterChange("oversized", e.target.checked)
            }
          />
          Hőtermelő/hűtőberendezés túlméretezett
        </label>

        {props.currentActiveHeater.oversized && (
          <FormControl fullWidth sx={{ mt: 1 }}>
            <TextField
              label="Túlméretezettség mértéke (%)"
              type="number"
              variant="outlined"
              size="small"
              value={props.currentActiveHeater.oversizeRatio || ""}
              onChange={(e) =>
                props.handleActiveHeaterChange(
                  "oversizeRatio",
                  Number(e.target.value),
                )
              }
            />
          </FormControl>
        )}
      </Box>
      {
        /**
         * TODO: Image upload
         */
      }
    </Box>
  );
}
