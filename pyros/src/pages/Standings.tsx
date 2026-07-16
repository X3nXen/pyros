import {
  Box,
  Button,
  CircularProgress,
  FormControl,
  FormHelperText,
  InputLabel,
  MenuItem,
  Select,
  TextField,
} from "@mui/material";
import {
  EnergyMeasurements,
  EnergySources,
  MeasurementTypes,
  type StandingsFormData,
  type StandingsShort,
} from "../model/Standings.model";
import { useState } from "react";
import { DatePicker } from "@mui/x-date-pickers";
import type { Dayjs } from "dayjs";
import Typography from "@mui/material/Typography";
import CloudUploadIcon from "@mui/icons-material/CloudUpload";
import { useNavigate } from "react-router-dom";
import FormSendProtocol from "../controllers/Forms.control";
import type { StandingsErrors } from "../model/Validation.model";
import { useAppDispatch, useAppSelector } from "../store";
import { addMainStandingLocally, addSubStandingLocally } from "../store/projectSlice";

export default function Standings() {
  const dispatch = useAppDispatch();
  const [formData, setFormData] = useState<StandingsFormData>({
    id: null,
    name: "",
    measurementType: 'MAIN' as MeasurementTypes.MAIN,
    subTo: null,
    source: 'COAL' as EnergySources.COAL,
    measurement: 'MCUBE' as EnergyMeasurements.MCUBE,
    dateFrom: null,
    dateTo: null,
    file: null,
  });

  const navigate = useNavigate();
  const [loading, setLoading] = useState<boolean>(false);
  const [errorMessage, setErrorMessage] = useState<StandingsErrors | null>(
    {} as StandingsErrors,
  );
  const mainMeasurements = useAppSelector((state) => state.project.mainStandings);

  const handleSave = async () => {
    const result = await FormSendProtocol.handleMeasurementForm(formData, setLoading, setErrorMessage);
    if (result && result.success){
      const savedMeasurement: StandingsShort = {id: formData.id!, name: formData.name};

      if(formData.measurementType === MeasurementTypes.MAIN){
        dispatch(addMainStandingLocally(savedMeasurement));
      } else {
        dispatch(addSubStandingLocally(savedMeasurement));
      }
      navigate("/");
    }
  }

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
      <h1>Mérés rögzítés</h1>
      <FormControl fullWidth>
        <TextField label="Megnevezés" variant="standard" onChange={(e) => {setFormData({...formData, name: e.target.value})}}></TextField>
      </FormControl>
      <FormControl fullWidth>
        <InputLabel id="measurement-type-select">Mérés típusa</InputLabel>
        <Select
          labelId="measurement-type-select"
          value={formData.measurementType}
          label="Mérés típusa"
          onChange={(e) => {
            setFormData({ ...formData, measurementType: e.target.value});
            console.log(formData);
          }}
        >
          {
          Object.keys(MeasurementTypes).map((e: string) => {
            return (
              <MenuItem value={e}>
                {MeasurementTypes[e as keyof typeof MeasurementTypes]}
              </MenuItem>
            );
          })}
        </Select>
        {errorMessage?.measurementType && (
          <FormHelperText>{errorMessage.measurementType}</FormHelperText>
        )}
      </FormControl>
      {formData.measurementType !== 'MAIN' as MeasurementTypes.MAIN ? (
        <FormControl>
          <InputLabel id="non-main-sub-to-select">
            Melyik főmérőhöz tartozik
          </InputLabel>
          <Select
            labelId="non-main-sub-to-select"
            value={formData.subTo}
            label="Melyik főmérőhöz tartozik"
            onChange={(e) => {
              setFormData({ ...formData, subTo: e.target.value });
            }}
          >
            {mainMeasurements.map(
              (measurement: StandingsShort) => {
                return (
                  <MenuItem value={measurement.id!}>{measurement.name}</MenuItem>
                );
              },
            )}
          </Select>
          {errorMessage?.subTo && (
            <FormHelperText>{errorMessage.subTo}</FormHelperText>
          )}
        </FormControl>
      ) : (
        <></>
      )}
      <FormControl fullWidth>
        <InputLabel id="energy-source-select">Mért jellemző</InputLabel>
        <Select
          labelId="energy-source-select"
          value={formData.source}
          label="Mért jellemző"
          onChange={(e) => {
            setFormData({ ...formData, source: e.target.value });
          }}
        >
          {Object.keys(EnergySources).map((e: string) => {
            return (
              <MenuItem value={e}>
                {EnergySources[e as keyof typeof EnergySources]}
              </MenuItem>
            );
          })}
        </Select>
        {errorMessage?.source && (
          <FormHelperText>{errorMessage.source}</FormHelperText>
        )}
      </FormControl>
      <FormControl fullWidth>
        <InputLabel id="energy-measurement-select">Mértékegység</InputLabel>
        <Select
          labelId="energy-measurement-select"
          value={formData.measurement}
          label="Mértékegység"
          onChange={(e) => {
            setFormData({ ...formData, measurement: e.target.value });
          }}
        >
          {Object.keys(EnergyMeasurements).map((e: string) => {
            return (
              <MenuItem value={e}>
                {EnergyMeasurements[e as keyof typeof EnergyMeasurements]}
              </MenuItem>
            );
          })}
        </Select>
        {errorMessage?.measurement && (
          <FormHelperText>{errorMessage.measurement}</FormHelperText>
        )}
      </FormControl>
      <DatePicker
        label="Időszak kezdete (Mettől)"
        value={formData.dateFrom}
        onChange={(newValue: Dayjs | null) => {
          setFormData({ ...formData, dateFrom: newValue });
        }}
        maxDate={formData.dateTo || undefined}
        slotProps={{
          textField: {
            error: !!errorMessage?.dateFrom,
            helperText: errorMessage?.dateFrom,
          },
        }}
      />
      <DatePicker
        label="Időszak vége (Meddig)"
        value={formData.dateTo}
        onChange={(newValue: Dayjs | null) => {
          setFormData({ ...formData, dateTo: newValue });
        }}
        minDate={formData.dateFrom || undefined}
        slotProps={{
          textField: {
            error: !!errorMessage?.dateTo,
            helperText: errorMessage?.dateTo,
          },
        }}
      />
      <Button
        component="label"
        variant="contained"
        color="inherit"
        startIcon={<CloudUploadIcon />}
        sx={{ mt: 1 }}
      >
        Adatok feltöltése
        <input
          type="file"
          accept=".xlsx"
          style={{
            clip: "rect(0 0 0 0)",
            clipPath: "inset(50%)",
            height: 1,
            overflow: "hidden",
            position: "absolute",
            bottom: 0,
            left: 0,
            whiteSpace: "nowrap",
            width: 1,
          }}
          onChange={(e) => {
            if (e.target.files && e.target.files[0]) {
              setFormData({ ...formData, file: e.target.files[0] });
            }
          }}
        />
      </Button>
      {errorMessage?.file && (
        <Typography variant="caption" color="error" sx={{ mt: -2, pl: 2 }}>
          {errorMessage.file}
        </Typography>
      )}
      {formData.file && (
        <Typography
          variant="body2"
          sx={{ textAlign: "center", color: "text.secondary", mt: -1 }}
        >
          Kiválasztott fájl: <strong>{formData.file.name}</strong>
        </Typography>
      )}
      <Button
        variant="contained"
        disabled={loading}
        startIcon={
          loading ? <CircularProgress size={20} color="inherit" /> : null
        }
        onClick={handleSave}>
        Rögzítés
      </Button>
    </Box>
  );
}
