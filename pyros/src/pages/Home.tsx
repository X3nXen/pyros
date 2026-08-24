import { useEffect } from 'react'
import {
    Box,
    Button,
    CircularProgress,
    FormControl,
    InputLabel,
    MenuItem,
    Select,
    type SelectChangeEvent,
} from '@mui/material'
import { Link } from 'react-router-dom'
import { useAppDispatch, useAppSelector } from '../store'
import {
    changeProject,
    fetchClickupData,
    fetchProjectData,
} from '../store/projectSlice'

export default function Home() {
    const dispatch = useAppDispatch()
    const currentTaskId = useAppSelector((state) => state.project.currentTaskId)
    const clickupTasks = useAppSelector((state) => state.project.clickupTasks)
    const isStoreLoading = useAppSelector((state) => state.project.loading)

    useEffect(() => {
        if (clickupTasks.length === 0) {
            dispatch(fetchClickupData())
        }
    }, [clickupTasks.length, dispatch])

    const handleTaskChange = (event: SelectChangeEvent<string>) => {
        const taskId = event.target.value

        if (taskId) {
            dispatch(changeProject(taskId))
            dispatch(fetchProjectData())
        } else {
            dispatch(changeProject(''))
        }
    }

    return (
        <Box
            component="nav"
            sx={{
                display: 'flex',
                flexDirection: 'column',
                gap: 2,
                width: '100%',
                maxWidth: 360,
                mt: 3,
                mx: 'auto',
            }}
        >
            {isStoreLoading && clickupTasks.length === 0 ? (
                <Box sx={{ display: 'flex', justifyContent: 'center', my: 2 }}>
                    <CircularProgress size={30} color="inherit" />
                </Box>
            ) : (
                <>
                    <FormControl fullWidth variant="outlined" sx={{ mb: 2 }}>
                        <InputLabel id="clickup-select-label">
                            Aktív ClickUp Projekt
                        </InputLabel>
                        <Select
                            labelId="clickup-select-label"
                            value={currentTaskId || ''}
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

                    <Button
                        variant="contained"
                        component={Link}
                        to="/standings"
                        disabled={!currentTaskId}
                    >
                        Mérés
                    </Button>
                    <Button
                        variant="contained"
                        component={Link}
                        to="/complex"
                        disabled={!currentTaskId}
                    >
                        Telephely
                    </Button>
                    <Button
                        variant="contained"
                        component={Link}
                        to="/building"
                        disabled={!currentTaskId}
                    >
                        Épület
                    </Button>
                    <Button
                        variant="contained"
                        component={Link}
                        to="/system"
                        disabled={!currentTaskId}
                    >
                        Rendszer
                    </Button>
                    <Button
                        variant="contained"
                        component={Link}
                        to="/vehicles"
                        disabled={!currentTaskId}
                    >
                        Járművek
                    </Button>
                    <Button
                        variant="contained"
                        component={Link}
                        to="/technology"
                        disabled={!currentTaskId}
                    >
                        Technológia
                    </Button>
                    <Button
                        variant="contained"
                        component={Link}
                        to="/product"
                        disabled={!currentTaskId}
                    >
                        Fajlagos
                    </Button>
                    <Button
                        variant="contained"
                        component={Link}
                        to="/variables"
                        disabled={!currentTaskId}
                    >
                        Változók
                    </Button>
                    <Button
                        variant="contained"
                        component={Link}
                        to="/create"
                        disabled={!currentTaskId}
                    >
                        Dokumentáció
                    </Button>
                </>
            )}
        </Box>
    )
}
