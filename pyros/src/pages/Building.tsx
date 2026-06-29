import { Box } from "@mui/material";

export default function Building() {
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
      <h1>Épület rögzítése</h1>
    </Box>
  );
}
