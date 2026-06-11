import { Container, Box } from "@mui/material";
import { Outlet } from "react-router-dom";
import Header from "./Header";

export default function Layout() {
  return (
    <>
      <Header />
      <Container maxWidth="sm">
        <Box
          component="main"
          sx={{
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
            gap: 3,
            mt: 6,
            width: "100%",
          }}
        >
          <Outlet />
        </Box>
      </Container>
    </>
  );
}
