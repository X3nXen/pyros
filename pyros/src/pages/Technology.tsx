import { Box, Button } from "@mui/material";
import { Link } from "react-router-dom";

export default function Technology(){
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
      <h1>Technológia rögzítés</h1>

      <Box sx={{ display: "flex", flexDirection: "column", gap: 1.5 }}>
        <Button
          variant="contained"
          component={Link}
          to="/technology/compressed"
          color="primary"
          fullWidth
        >
          Sűrített levegős hálózat
        </Button>

        <Button
          variant="contained"
          component={Link}
          to="/technology/steam"
          color="primary"
          fullWidth
        >
          Gőzrendszer
        </Button>

        <Button
          variant="contained"
          component={Link}
          to="/technology/cooling"
          color="primary"
          fullWidth
        >
          Technológiai hűtés
        </Button>

        <Button
          variant="contained"
          component={Link}
          to="/technology/other"
          color="primary"
          fullWidth
        >
          Egyéb technológiai hőhasználat
        </Button>
      </Box>
    </Box>
  );
}