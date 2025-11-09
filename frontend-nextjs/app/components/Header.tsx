'use client';

import { AppBar, Toolbar, Typography, Box, Button } from '@mui/material';
import { useRouter } from 'next/navigation';

interface HeaderProps {
    buttonText: string;
    buttonAction?: string; // ruta o función personalizada
}

export default function Header({ buttonText, buttonAction = '/results' }: HeaderProps) {
    const router = useRouter();

    return (
        <AppBar
            position="static"
            sx={{
                backgroundColor: 'white',
                color: 'black',
                boxShadow: '0 1px 4px rgba(0,0,0,0.08)',
            }}
        >
            <Toolbar sx={{ display: 'flex', justifyContent: 'space-between' }}>
                {/* Logo */}
                <Box display="flex" alignItems="center" gap={1}>
                    <Box sx={{ width: 20, height: 20, borderRadius: '50%', backgroundColor: '#2563eb' }} />
                    <Typography fontWeight={600} variant="h6">
                        Gema SAS
                    </Typography>
                </Box>

                {/* Botón derecho */}
                <Button
                    onClick={() => router.push(buttonAction)}
                    variant="contained"
                    sx={{
                        backgroundColor: '#2563eb',
                        textTransform: 'none',
                        borderRadius: 2,
                        px: 3,
                        '&:hover': { backgroundColor: '#1e40af' },
                    }}
                >
                    {buttonText}
                </Button>
            </Toolbar>
        </AppBar>
    );
}
