import { AppBar, Box, Button, Toolbar } from '@mui/material'
import Logo from '../assets/logo.jpg'
import { Link, useLocation } from 'react-router-dom'

export default function Header() {
    const location = useLocation()
    return (
        <AppBar position="static" color="primary">
            <Toolbar sx={{ display: 'flex', alignItems: 'center' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                    <img
                        src={Logo}
                        alt="Balajti Energetika logója"
                        style={{ height: '40px' }}
                    />
                    <h1>Pyros 1.0</h1>
                </Box>

                <Box sx={{ flexGrow: 1 }} />

                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                    {location.pathname !== '/' ? (
                        <Button
                            variant="contained"
                            component={Link}
                            to={'/'}
                            sx={{
                                bgcolor: 'grey.500',
                                '&:hover': {
                                    bgcolor: 'grey.700',
                                },
                            }}
                        >
                            Főoldalra
                        </Button>
                    ) : (
                        <></>
                    )}
                </Box>
            </Toolbar>
        </AppBar>
    )
}
