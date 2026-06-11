import { useState } from "react";
import type { LoginCredentials } from "../model/LoginData.model";
import { TextField } from "@mui/material";

export default function Login(props: {
  loginFunc: (payload: LoginCredentials) => void;
}) {
  const [formData, setFormData] = useState<LoginCredentials | null>(null);

  return (
    <>
      <TextField label="Felhasználónév" variant="standard" />
      <TextField label="Jelszó" variant="standard" type="password" />
    </>
  );
}
