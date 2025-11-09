'use client';

import { useEffect, useState } from 'react';
import {
    Typography,
    Container,
    Box,
    Paper,
    Table,
    TableHead,
    TableBody,
    TableCell,
    TableRow,
    CircularProgress,
    Alert,
    Divider,
} from '@mui/material';
import ErrorOutlineIcon from '@mui/icons-material/ErrorOutline';
import Header from '../components/Header';

// Define la URL base de la API, leída de .env.local
const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8080';

// Interfaces (Tipado) para la estructura de los datos
interface Usuario {
    id: number;
    email: string;
    nombre: string | null;
    apellido: string | null;
    codigo: number;
    created_at: string;
}

interface DataResponse {
    activos: Usuario[];
    inactivos: Usuario[];
    espera: Usuario[];
}

// COMPONENTE MODULAR PARA CADA TABLA
function UsuariosTable({ title, users }: { title: string; users: Usuario[] }) {
    return (
        <Paper
            elevation={0}
            sx={{
                p: 3,
                mb: 4,
                borderRadius: 3,
                border: '1px solid #e5e7eb',
                backgroundColor: 'white',
            }}
        >
            {/* Muestra el título y el contador */}
            <Typography variant="h6" sx={{ fontWeight: 600, mb: 2 }}>
                {title} ({users.length})
            </Typography>
            <Divider sx={{ mb: 2 }} />
            <Table size="small">
                <TableHead>
                    <TableRow sx={{ backgroundColor: '#f9fafb' }}>
                        <TableCell>Email</TableCell>
                        <TableCell>Nombre</TableCell>
                        <TableCell>Apellido</TableCell>
                        <TableCell>Código</TableCell>
                        <TableCell>Fecha</TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {users.length > 0 ? (
                        users.map((u) => (
                            <TableRow key={u.id} hover>
                                <TableCell>{u.email}</TableCell>
                                {/* Muestra '-' si el campo es nulo */}
                                <TableCell>{u.nombre || '-'}</TableCell>
                                <TableCell>{u.apellido || '-'}</TableCell>
                                <TableCell>{u.codigo}</TableCell>
                                {/* Formato de fecha legible */}
                                <TableCell>{new Date(u.created_at).toLocaleString()}</TableCell>
                            </TableRow>
                        ))
                    ) : (
                        // Mensaje cuando no hay registros
                        <TableRow>
                            <TableCell colSpan={5} align="center" sx={{ py: 3 }}>
                                <Typography variant="body2" color="text.secondary">
                                    Sin registros disponibles
                                </Typography>
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
            </Table>
        </Paper>
    );
}

export default function ResultsPage() {
    // Estado para almacenar los datos agrupados por la API
    const [data, setData] = useState<DataResponse>({
        activos: [],
        inactivos: [],
        espera: [],
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    //Hook para cargar el componente (Método GET)
    useEffect(() => {
        async function fetchData() {
            try {
                // Llamada al endpoint
                const res = await fetch(`${API_URL}/list.php`);

                if (!res.ok) throw new Error('Error al obtener los datos');

                // Los datos vienen ya agrupados en tres arrays (activos, inactivos, espera)
                const json = await res.json();
                setData(json);
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
            } catch (err: any) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        }
        fetchData();
    }, []); // Dependencia vacía para ejecutar solo al montar

    return (
        <Box sx={{ minHeight: '100vh', backgroundColor: '#f9f9f9' }}>
            <Header buttonText="Subir Nuevo Archivo" buttonAction="/" />
            <Container maxWidth="lg" sx={{ py: 8 }}>
                {loading ? (
                    <Box display="flex" justifyContent="center" alignItems="center" height="50vh">
                        <CircularProgress sx={{ color: '#2563eb' }} />
                        <Typography sx={{ ml: 2 }} color="text.secondary">
                            Cargando usuarios...
                        </Typography>
                    </Box>
                ) : error ? (
                    <Alert
                        icon={<ErrorOutlineIcon fontSize="inherit" />}
                        severity="error"
                        sx={{
                            mt: 4,
                            borderRadius: 2,
                            backgroundColor: '#fee2e2',
                            color: '#b91c1c',
                        }}
                    >
                        {error}
                    </Alert>
                ) : (
                    <>
                        {/* Resumen de contadores */}
                        <Box sx={{ mb: 6 }}>
                            <Typography variant="h4" fontWeight={700} sx={{ mb: 1 }}>
                                📊 Gestión de Activos
                            </Typography>
                            <Typography color="text.secondary">
                                Activos: {data?.activos?.length ?? 0} · Inactivos:{' '}
                                {data?.inactivos?.length ?? 0} · En Espera:{' '}
                                {data?.espera?.length ?? 0}
                            </Typography>
                        </Box>

                        {/* Renderizado de las TRES tablas separadas */}
                        <UsuariosTable title="Usuarios Activos" users={data.activos} />
                        <UsuariosTable title="Usuarios Inactivos" users={data.inactivos} />
                        <UsuariosTable title="Usuarios en Espera" users={data.espera} />
                    </>
                )}
            </Container>
        </Box>
    );
}
