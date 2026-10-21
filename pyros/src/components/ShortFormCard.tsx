import type { ShortFormData } from '../model/ShortFormCards.model'
import { Card as MuiCard, Typography } from '@mui/material'

export default function ShortFormCard(props: { data: ShortFormData }) {
    return (
        <MuiCard
            variant="outlined"
            sx={{
                p: 2,
                borderColor: 'divider',
                backgroundColor: 'background.paper',
                transition: 'border-color 0.2s ease-in-out',
                '&:hover': {
                    borderColor: 'primary.main',
                },
            }}
        >
            <Typography variant="body1" sx={{ fontWeight: 'medium' }}>
                {props.data.name}
            </Typography>
        </MuiCard>
    )
}
