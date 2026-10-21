import { useEffect, useState } from 'react'
import { useAppSelector } from '../store'
import Calls from '../controllers/Calls.control'
import type {
    ShortFormCollection,
    ShortFormData,
} from '../model/ShortFormCards.model'
import { Box, Typography } from '@mui/material'
import ShortFormCard from '../components/ShortFormCard'

export default function Overview() {
    const taskId = useAppSelector((state) => state.project.currentTaskId)
    const [formData, setFormData] = useState<ShortFormCollection>({
        standing: [],
        complex: [],
        building: [],
        heating_system: [],
        hmv_system: [],
        hvac_system: [],
        lighting_system: [],
        technology: [],
        vehicle: [],
        product: [],
        variable: [],
    })
    const [error, setError] = useState<string | null>(null)

    useEffect(() => {
        async function callApi() {
            const call = await Calls.getAllData(taskId!)
            if (!call.success) {
                setError(call.payload as string)
                return
            }
            setFormData(call.payload as ShortFormCollection)
        }

        callApi()
    }, [taskId])
    return (
        <Box
            sx={{
                display: 'flex',
                flexDirection: 'column',
                gap: 2,
                width: '100%',
                maxWidth: 360,
                mt: 3,
            }}
        >
            <Typography variant="h4" component="h1" sx={{ fontWeight: 'bold' }}>
                Áttekintés
            </Typography>
            {error !== null ? (
                <h1>Hiba az adatok lekérése során: {error}</h1>
            ) : (
                <Box
                    sx={{
                        display: 'flex',
                        flexDirection: 'column',
                        gap: 2,
                        width: '100%',
                        maxWidth: 360,
                        mt: 3,
                    }}
                >
                    <Typography
                        variant="h6"
                        component="h2"
                        sx={{
                            borderBottom: '1px solid',
                            borderColor: 'divider',
                            pb: 1,
                        }}
                    >
                        Mérések
                    </Typography>
                    {formData.standing.map((item: ShortFormData) => (
                        <ShortFormCard data={item} />
                    ))}
                    <Typography
                        variant="h6"
                        component="h2"
                        sx={{
                            borderBottom: '1px solid',
                            borderColor: 'divider',
                            pb: 1,
                        }}
                    >
                        Telephelyek
                    </Typography>
                    {formData.complex.map((item: ShortFormData) => (
                        <ShortFormCard data={item} />
                    ))}
                    <Typography
                        variant="h6"
                        component="h2"
                        sx={{
                            borderBottom: '1px solid',
                            borderColor: 'divider',
                            pb: 1,
                        }}
                    >
                        Épületek
                    </Typography>
                    {formData.building.map((item: ShortFormData) => (
                        <ShortFormCard data={item} />
                    ))}
                    <Typography
                        variant="h6"
                        component="h2"
                        sx={{
                            borderBottom: '1px solid',
                            borderColor: 'divider',
                            pb: 1,
                        }}
                    >
                        Fűtő- és hűtőrendszerek
                    </Typography>
                    {formData.heating_system.map((item: ShortFormData) => (
                        <ShortFormCard data={item} />
                    ))}
                    <Typography
                        variant="h6"
                        component="h2"
                        sx={{
                            borderBottom: '1px solid',
                            borderColor: 'divider',
                            pb: 1,
                        }}
                    >
                        HMV rendszerek
                    </Typography>
                    {formData.hmv_system.map((item: ShortFormData) => (
                        <ShortFormCard data={item} />
                    ))}
                    <Typography
                        variant="h6"
                        component="h2"
                        sx={{
                            borderBottom: '1px solid',
                            borderColor: 'divider',
                            pb: 1,
                        }}
                    >
                        Légtechnikai rendszerek
                    </Typography>
                    {formData.hvac_system.map((item: ShortFormData) => (
                        <ShortFormCard data={item} />
                    ))}
                    <Typography
                        variant="h6"
                        component="h2"
                        sx={{
                            borderBottom: '1px solid',
                            borderColor: 'divider',
                            pb: 1,
                        }}
                    >
                        Világítási rendszerek
                    </Typography>
                    {formData.lighting_system.map((item: ShortFormData) => (
                        <ShortFormCard data={item} />
                    ))}
                    <Typography
                        variant="h6"
                        component="h2"
                        sx={{
                            borderBottom: '1px solid',
                            borderColor: 'divider',
                            pb: 1,
                        }}
                    >
                        Technológia
                    </Typography>
                    {formData.technology.map((item: ShortFormData) => (
                        <ShortFormCard data={item} />
                    ))}
                    <Typography
                        variant="h6"
                        component="h2"
                        sx={{
                            borderBottom: '1px solid',
                            borderColor: 'divider',
                            pb: 1,
                        }}
                    >
                        Járművek
                    </Typography>
                    {formData.vehicle.map((item: ShortFormData) => (
                        <ShortFormCard data={item} />
                    ))}
                    <Typography
                        variant="h6"
                        component="h2"
                        sx={{
                            borderBottom: '1px solid',
                            borderColor: 'divider',
                            pb: 1,
                        }}
                    >
                        Termék
                    </Typography>
                    {formData.product.map((item: ShortFormData) => (
                        <ShortFormCard data={item} />
                    ))}
                    <Typography
                        variant="h6"
                        component="h2"
                        sx={{
                            borderBottom: '1px solid',
                            borderColor: 'divider',
                            pb: 1,
                        }}
                    >
                        Változók
                    </Typography>
                    {formData.variable.map((item: ShortFormData) => (
                        <ShortFormCard data={item} />
                    ))}
                </Box>
            )}
        </Box>
    )
}
