import { useState } from "react";
import { type ProductFormData, ProductMetric } from "../model/Product.model";
import {
  Box,
  Button,
  CircularProgress,
  FormControl,
  FormHelperText,
  InputLabel,
  MenuItem,
  Select,
} from "@mui/material";
import CloudUploadIcon from "@mui/icons-material/CloudUpload";
import FormSendProtocol from "../controllers/Forms.control";
import { useNavigate } from "react-router-dom";

export default function Product() {
  const [formData, setFormData] = useState<ProductFormData>({
    id: null,
    metric: ProductMetric[0],
    file: null,
  });
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState<boolean>(false);
  const navigate = useNavigate();

  async function handleSubmit() {
    const result = await FormSendProtocol.handleProduct(
      formData,
      setLoading,
      setError,
    );
    if (result && result.success) {
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
      <FormControl>
        <InputLabel id="metric-select">Gazdálkodási jellemző</InputLabel>
        <Select
          label="Jellemző"
          labelId="metric-select"
          value={formData.metric}
          onChange={(e) => setFormData({ ...formData, metric: e.target.value })}
        >
          {ProductMetric.map((e: string, index: number) => (
            <MenuItem key={index} value={e}>
              {e}
            </MenuItem>
          ))}
        </Select>
      </FormControl>
      <FormControl error={!!error}>
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
        {error !== null && <FormHelperText>{error}</FormHelperText>}
      </FormControl>
      <Button
        variant="contained"
        disabled={loading}
        startIcon={
          loading ? <CircularProgress size={20} color="inherit" /> : null
        }
        onClick={handleSubmit}
      >
        Rögzítés
      </Button>
    </Box>
  );
}
