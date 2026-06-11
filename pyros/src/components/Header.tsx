import { AppBar, Toolbar } from "@mui/material";
import Logo from "../assets/logo.jpg"

export default function Header(){
    return (        
        <AppBar position="static" color="primary">
            <Toolbar>
                <img src={Logo} alt="Balajti Energetika logója"/>
                <h1>Pyros 1.0</h1>
            </Toolbar>
        </AppBar>
    );
}