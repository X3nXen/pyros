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
} from "@mui/material";
import { useEffect, useState } from "react";
import type { ComplexErrors, ComplexFormData } from "../model/Complex.model";
import type { StandingsShort } from "../model/Standings.model";
import Calls from "../controllers/Calls.control";
import settlementData from "../model/zipToCity.json";
import theme from "../assets/theme";
import FormSendProtocol from "../controllers/Forms.control";
import { useNavigate } from "react-router-dom";

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

export default function Complex() {
  const [formData, setFormData] = useState<ComplexFormData>(
    {
      id: null,
      name: "",
      address: "",
      postal: 0,
      city: "",
      parcelNumber: "",
      meterStandings: new Array<string>()
    }
  );
  const [mainStandings, setMainStandings] = useState<Array<StandingsShort>>([]);
  const [settlements, setSettlements] = useState<
    Array<{ zip: number; city: string }>
  >([]);

  const handleStandingChange = (event: SelectChangeEvent<string[]>) => {
    const {
      target: { value },
    } = event;
    const updatedIds = typeof value === "string" ? value.split(",") : value;
    setFormData({
      ...formData,
      meterStandings: updatedIds,
    });
  };

  const navigate = useNavigate();
  const [loading, setLoading] = useState<boolean>(false);
  const [errorMessage, setErrorMessage] = useState<ComplexErrors | null>(
    {} as ComplexErrors,
  );

  useEffect(() => {
    async function callApi() {
      return await Calls.getMainStandings();
    }
    callApi()
      .then((result) => {
        if (result.success) {
          setMainStandings(result.payload);
        }
        setSettlements(settlementData);
      })
      .catch((error) =>{
        console.error("Probléma a backend hívással a useEffectben", error);
        setSettlements(settlementData);}
      );
  });
  return (
    <Box
      sx={{
        display: "flex",
        flexDirection: "column",
        gap: 2,
        width: "100%",
        maxWidth: 360,
        mt: 3,
      }}
    >
      <h1>Telephely rögzítése</h1>
      <FormControl fullWidth error={!!errorMessage?.name}>
        <TextField
          label="Megnevezés"
          variant="standard"
          onChange={(e) =>
            setFormData({ ...formData, name: e.target.value })
          }
        ></TextField>
        {errorMessage?.name && (
          <FormHelperText>{errorMessage.name}</FormHelperText>
        )}
      </FormControl>
      <FormControl fullWidth error={!!errorMessage?.postal}>
        <Autocomplete
          disablePortal
          options={settlements}
          getOptionLabel={(option) => `${option.zip}, ${option.city}`}
          renderInput={(params) => <TextField {...params} label="Település" />}
          filterOptions={(options, state) => {
            const inputValue = state.inputValue.toLowerCase();
            return options.filter(
              (option) =>
                option.zip.toString().includes(inputValue) ||
                option.city.toLowerCase().includes(inputValue),
            );
          }}
          onChange={(_, newValue) => {
            setFormData({
              ...formData,
              city: newValue ? newValue.city : "",
              postal: newValue ? newValue.zip : 0,
            });
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
        onChange={(e) => setFormData({ ...formData, address: e.target.value })}
      ></TextField>
      {errorMessage?.address && (
          <FormHelperText>{errorMessage.address}</FormHelperText>
        )}
      </FormControl>
      <FormControl fullWidth error={!!errorMessage?.parcelNumber}>
      <TextField
        label="Helyrajzi szám"
        variant="standard"
        onChange={(e) =>
          setFormData({ ...formData, parcelNumber: e.target.value })
        }
      ></TextField>
      {errorMessage?.parcelNumber && (
          <FormHelperText>{errorMessage.parcelNumber}</FormHelperText>
        )}
      </FormControl>
      <FormControl sx={{ width: "100%" }} error={!!errorMessage?.meterStandings}>
        <InputLabel id="main-standings-multiple-label">Főmérők</InputLabel>
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
                  mainStandings.find((standing) => standing.id === id)?.name,
              )
              .filter(Boolean)
              .join(", ");
          }}
        >
          {mainStandings.map((standing) => {
            const standingId = standing.id || "";
            return (
              <MenuItem
                key={standingId}
                value={standingId}
                style={getStyles(
                  standingId,
                  formData.meterStandings || [],
                  theme,
                )}
              >
                {standing.name}
              </MenuItem>
            );
          })}
        </Select>
        {errorMessage?.meterStandings && (
          <FormHelperText>{errorMessage.meterStandings}</FormHelperText>
        )}
      </FormControl>
      <Button
        variant="contained"
        disabled={loading}
        startIcon={
          loading ? <CircularProgress size={20} color="inherit" /> : null
        }
        onClick={() =>
          FormSendProtocol.handleComplexForm(
            formData,
            navigate,
            setLoading,
            setErrorMessage,
          )
        }
      >
        Rögzítés
      </Button>
    </Box>
  );
}
