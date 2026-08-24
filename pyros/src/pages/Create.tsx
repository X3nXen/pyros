import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Alert, Box, CircularProgress, Typography } from '@mui/material'
import Calls from '../controllers/Calls.control'

export default function Create() {
    const navigate = useNavigate()
    const [error, setError] = useState<string | null>(null)

    useEffect(() => {
        let isSubscribed = true

        const handleDownload = async () => {
            const response = await Calls.getDocument()

            if (!isSubscribed) return

            if (response.success && response.payload) {
                // A response.payload közvetlenül a Blob objektum
                const url = window.URL.createObjectURL(response.payload)
                const link = document.createElement('a')
                link.href = url
                link.setAttribute('download', 'Energetikai_Audit.docx')
                document.body.appendChild(link)
                link.click()

                // Takarítás és azonnali visszanavigálás
                link.remove()
                window.URL.revokeObjectURL(url)
                navigate('/')
            } else {
                // Kezeljük, ha a backend siker = false értéket adott vissza
                setError(
                    response.message ||
                        'Nem sikerült a dokumentumot előállítani.'
                )

                setTimeout(() => {
                    if (isSubscribed) {
                        navigate('/')
                    }
                }, 3000)
            }
        }

        handleDownload()

        return () => {
            isSubscribed = false
        }
    }, [navigate])

    return (
        <Box
            sx={{
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                minHeight: '60vh',
                gap: 2,
            }}
        >
            {error ? (
                <Alert severity="error">{error}</Alert>
            ) : (
                <>
                    <CircularProgress size={60} />
                    <Typography variant="h6" color="text.secondary">
                        Dokumentum generálása és letöltése folyamatban...
                    </Typography>
                </>
            )}
        </Box>
    )
}
