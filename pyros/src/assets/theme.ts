import { createTheme } from "@mui/material";

const theme = createTheme({
    palette: {
        primary: {
            main: '#2e7d32',
            contrastText: '#ffffff'
        },
    },
    components: {
    MuiCssBaseline: {
      styleOverrides: {
        body: {
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          minHeight: '100vh',
          margin: 0,
          padding: 0,
        },
        '#root': {
          width: '100%',
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
        },
      },
    },
  },
});

export default theme;