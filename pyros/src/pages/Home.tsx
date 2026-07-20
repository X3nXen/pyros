import { useEffect, useState } from "react";
import type { LoginCredentials, LoginData } from "../model/LoginData.model";
import Login from "../components/Login";
import { Box, Button, CircularProgress, FormControl, InputLabel, MenuItem, Select, type SelectChangeEvent } from "@mui/material";
import { Link } from "react-router-dom";
import { useCookies } from "react-cookie";
import { useAppDispatch, useAppSelector } from "../store";
import { changeProject, fetchClickupData, fetchProjectData } from "../store/projectSlice";

export default function Home() {
  const dispatch = useAppDispatch();
  const [loginData, setLoginData] = useState<LoginData | null>(null);
  const [cookies, setCookie] = useCookies(['iden']);
  const currentTaskId = useAppSelector((state) => state.project.currentTaskId);
  const clickupTasks = useAppSelector((state) => state.project.clickupTasks);
  const isStoreLoading = useAppSelector((state) => state.project.loading);

  
  useEffect(() => {
    function readCookie(){
      if(cookies.iden){
        setLoginData(cookies['iden'] as LoginData | null)
      }
    }

    readCookie();
  }, [cookies])

  useEffect(() => {
    if (loginData != null && loginData?.token && clickupTasks.length === 0) {
      dispatch(fetchClickupData());
    }
  }, [loginData, clickupTasks.length, dispatch]);


  function login(payload: LoginCredentials) {
    setLoginData({
      clickupId: "15",
      userName: payload.userName,
      token: "12",
    });
      setCookie('iden', {
      clickupId: "15",
      userName: payload.userName,
      token: "12",
    }, {path: '/'});
  }

  const handleTaskChange = (event: SelectChangeEvent<string>) => {
    const taskId = event.target.value;
    
    if (taskId) {
      dispatch(changeProject(taskId));
      dispatch(fetchProjectData());
    } else {
      dispatch(changeProject(""));
    }
  };

  return (
    <>
      {loginData != null && loginData?.token ? (
        isStoreLoading && clickupTasks.length === 0 ? (
            <Box sx={{ display: 'flex', justifyContent: 'center', my: 2 }}>
              <CircularProgress size={30} color="inherit" />
            </Box>):(
        <Box component="nav" sx={{display: "flex", flexDirection: "column", gap: 2, width: "100%", maxWidth: 360, mt: 3}}>
          <FormControl fullWidth variant="outlined" sx={{ mb: 2 }}>
            <InputLabel id="mock-clickup-select-label">Aktív ClickUp Projekt</InputLabel>
            <Select
              labelId="mock-clickup-select-label"
              value={currentTaskId || ""}
              label="Aktív ClickUp Projekt"
              onChange={handleTaskChange}
            >
              <MenuItem value="">
                <em>-- Válassz egy projektet --</em>
              </MenuItem>
              {clickupTasks.map((task) => (
                <MenuItem key={task.id} value={task.id}>
                  {task.name}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
          <Button variant="contained" component={Link} to="/standings" disabled={!currentTaskId}>Mérés</Button>
          <Button variant="contained" component={Link} to="/complex" disabled={!currentTaskId}>Telephely</Button>
          <Button variant="contained" component={Link} to="/building" disabled={!currentTaskId}>Épület</Button>
          <Button variant="contained" component={Link} to="/system" disabled={!currentTaskId}>Rendszer</Button>
          <Button variant="contained" component={Link} to="/vehicles" disabled={!currentTaskId}>Járművek</Button>
          <Button variant="contained" component={Link} to="/technology" disabled={!currentTaskId}>Technológia</Button>
          <Button variant="contained" component={Link} to="/product" disabled={!currentTaskId}>Fajlagos</Button>
          <Button variant="contained" component={Link} to="/create" disabled={!currentTaskId}>Dokumentáció</Button>
        </Box>)
      ) : (
        <Login loginFunc={login} />
      )}
    </>
  );
}
