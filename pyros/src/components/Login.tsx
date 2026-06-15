import { useState } from "react";
import type { LoginCredentials } from "../model/LoginData.model";
import { Button, TextField } from "@mui/material";

export default function Login(props: {
  loginFunc: (payload: LoginCredentials) => void;
}) {
  const [formData, setFormData] = useState<LoginCredentials>({userName: "", password: ""});
  return (
    <>
      <TextField label="Felhasználónév" variant="standard" onChange={(e) => {setFormData({...formData, userName: e.target.value})}}/>
      <TextField label="Jelszó" variant="standard" type="password" onChange={(e) => {setFormData({...formData, password: e.target.value})}}/>
      <Button variant="contained" onClick={() => {console.log(formData); props.loginFunc(formData)}}>Bejelentkezés</Button>
    </>
  );
}
