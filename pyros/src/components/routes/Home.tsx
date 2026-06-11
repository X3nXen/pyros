import { useState } from "react";
import type { LoginCredentials, LoginData } from "../../model/LoginData.model";
import Header from "../Header";
import Login from "../Login";
import { Box, Container } from "@mui/material";

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
        <nav></nav>
      ) : (
        <Login loginFunc={login} />
      )}
    </>
  );
}
