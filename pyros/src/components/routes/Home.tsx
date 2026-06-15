import { useState } from "react";
import type { LoginCredentials, LoginData } from "../../model/LoginData.model";
import Login from "../Login";
import { Button } from "@mui/material";
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
        <nav>
          <Button variant="contained">
            <Link to="/standings">Mérés</Link>
          </Button>
          <Button variant="contained">
            <Link to="/complex">Telephely</Link>
          </Button>
          <Button variant="contained">
            <Link to="/building">Épület</Link>
          </Button>
          <Button variant="contained">
            <Link to="/system">Rendszer</Link>
          </Button>
          <Button variant="contained">
            <Link to="/transport">Járművek</Link>
          </Button>
          <Button variant="contained">
            <Link to="/technology">Technológia</Link>
          </Button>
          <Button variant="contained">
            <Link to="/specific">Fajlagos</Link>
          </Button>
          <Button variant="contained">
            <Link to="/create">Dokumentáció</Link>
          </Button>
        </nav>
      ) : (
        <Login loginFunc={login} />
      )}
    </>
  );
}
