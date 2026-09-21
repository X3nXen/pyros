import { useEffect, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Alert, Box, CircularProgress, Typography } from '@mui/material'
import Calls from '../controllers/Calls.control'
import { useAppSelector } from '../store'
import { Chart as ChartJS, LinearScale } from 'chart.js'
import { Chart } from 'react-chartjs-2'
import { SankeyController, Flow } from 'chartjs-chart-sankey'

ChartJS.register(LinearScale, SankeyController, Flow)

export default function Create() {
    const navigate = useNavigate()
    const [error, setError] = useState<string | null>(null)
    const [sankeyLinks, setSankeyLinks] = useState<Array<{
        from: string
        to: string
        flow: number
    }> | null>(null)
    const projectId = useAppSelector((state) => state.project.currentTaskId)
    const chartRef = useRef<ChartJS<'sankey'> | null>(null)

    useEffect(() => {
        let isSubscribed = true

        const fetchSankeyData = async () => {
            if (!projectId) return

            const res = await Calls.getSankeyData(projectId)
            if (!isSubscribed) return

            if (res.success && res.payload) {
                setSankeyLinks(res.payload)
            } else {
                setSankeyLinks([])
            }
        }

        fetchSankeyData()

        return () => {
            isSubscribed = false
        }
    }, [projectId])

    const processedSankeyLinks = (() => {
        if (!sankeyLinks || sankeyLinks.length === 0) return []

        const total = sankeyLinks.reduce((sum, link) => sum + link.flow, 0)
        if (total === 0) return sankeyLinks

        // Összesítjük az egyes 'to' csomópontokba áramló értékeket
        const destinationTotals: Record<string, number> = {}
        sankeyLinks.forEach((link) => {
            destinationTotals[link.to] =
                (destinationTotals[link.to] || 0) + link.flow
        })

        return sankeyLinks.map((link) => {
            const percentage = (
                (destinationTotals[link.to] / total) *
                100
            ).toFixed(1)
            return {
                ...link,
                to: `${link.to} (${percentage}%)`,
            }
        })
    })()

    useEffect(() => {
        let isSubscribed = true

        if (sankeyLinks === null || !projectId) return

        const handleDownload = async () => {
            // Pici várakozás, hogy a Chart.js canvas biztosan kirajzolódjon a DOM-ban
            await new Promise((resolve) => setTimeout(resolve, 400))

            let base64Image: string | null = null

            if (chartRef.current && sankeyLinks.length > 0) {
                try {
                    base64Image = chartRef.current.toBase64Image()
                } catch (e) {
                    console.error('Kép konvertálási hiba:', e)
                }
            }

            const response = await Calls.getDocument(projectId, base64Image)

            if (!isSubscribed) return

            if (response.success && response.payload) {
                const url = window.URL.createObjectURL(response.payload)
                const link = document.createElement('a')
                link.href = url
                link.setAttribute('download', 'Energetikai_Audit.docx')
                document.body.appendChild(link)
                link.click()

                link.remove()
                window.URL.revokeObjectURL(url)
                navigate('/')
            } else {
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
    }, [sankeyLinks, projectId, navigate])

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
            {sankeyLinks && sankeyLinks.length > 0 && (
                <div
                    style={{
                        position: 'absolute',
                        left: '-9999px',
                        top: '-9999px',
                    }}
                >
                    <Chart
                        ref={chartRef}
                        type="sankey"
                        width={1000}
                        height={500}
                        data={{
                            datasets: [
                                {
                                    label: 'Energiaáramlás',
                                    data: processedSankeyLinks,
                                    colorFrom: '#0055A5',
                                    colorTo: '#28A745',
                                    colorMode: 'gradient',

                                    nodeWidth: 22,
                                    nodePadding: 30,

                                    font: {
                                        size: 14,
                                        family: 'Arial, sans-serif',
                                        weight: 'bold',
                                    },
                                    color: '#111111',
                                },
                            ],
                        }}
                        options={{
                            animation: false,
                            responsive: false,
                            layout: {
                                padding: {
                                    top: 25,
                                    left: 60,
                                    right: 60,
                                    bottom: 25,
                                },
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: { enabled: false },
                            },
                        }}
                    />
                </div>
            )}

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
