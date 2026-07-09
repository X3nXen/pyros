import { Box, Button } from "@mui/material";
import { Link } from "react-router-dom";

export default function System(){
  return (
    <Box
      sx={{
        display: "flex",
        flexDirection: "column",
        gap: 3,
        width: "100%",
        maxWidth: 360,
        mt: 3,
      }}
    >
      <h1>Rendszer rögzítés</h1>

      <Box sx={{ display: "flex", flexDirection: "column", gap: 1.5 }}>
        <Button
          variant="contained"
          component={Link}
          to="/system/heating"
          color="primary"
          fullWidth
        >
          Fűtő/Hűtő rendszer
        </Button>

        <Button
          variant="contained"
          component={Link}
          to="/system/lighting"
          color="primary"
          fullWidth
        >
          Világítási rendszer
        </Button>

        <Button
          variant="contained"
          component={Link}
          to="/system/ventilation"
          color="primary"
          fullWidth
        >
          Légtechnikai rendszer
        </Button>
      </Box>
    </Box>
  );
}