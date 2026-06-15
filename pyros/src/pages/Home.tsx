import { useState } from "react";
import type { LoginCredentials, LoginData } from "../model/LoginData.model";
import Login from "../components/Login";
import { Box, Button } from "@mui/material";
import { Link } from "react-router-dom";

export default function Home() {
  const [loginData, setLoginData] = useState<LoginData | null>(null);

  function login(payload: LoginCredentials) {
    setLoginData({
      userName: payload.userName,
      token: "12",
    });
  }

  return (
    <>
      {loginData != null && loginData?.token ? (
        <Box component="nav" sx={{display: "flex", flexDirection: "column", gap: 2, width: "100%", maxWidth: 360, mt: 3}}>
          <Button variant="contained" component={Link} to="/standings">Mérés</Button>
          <Button variant="contained" component={Link} to="/complex">Telephely</Button>
          <Button variant="contained" component={Link} to="/building">Épület</Button>
          <Button variant="contained" component={Link} to="/system">Rendszer</Button>
          <Button variant="contained" component={Link} to="/transport">Járművek</Button>
          <Button variant="contained" component={Link} to="/technology">Technológia</Button>
          <Button variant="contained" component={Link} to="/specific">Fajlagos</Button>
          <Button variant="contained" component={Link} to="/create">Dokumentáció</Button>
        </Box>
      ) : (
        <Login loginFunc={login} />
      )}
    </>
  );
}
