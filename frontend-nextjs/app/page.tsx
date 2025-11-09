'use client';

import { useState, FormEvent } from 'react';
import { useRouter } from 'next/navigation';
import {
  Typography,
  Container,
  Box,
  Button,
  Paper,
  Alert,
  CircularProgress,
} from '@mui/material';
import { CloudUpload } from '@mui/icons-material';
import Header from './components/Header';

export default function HomePage() {
  const [file, setFile] = useState<File | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const router = useRouter();

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);

    if (!file) {
      setError('Por favor selecciona un archivo .txt');
      return;
    }

    const formData = new FormData();
    formData.append('file', file);

    const API_URL = process.env.NEXT_PUBLIC_API_URL;
    if (!API_URL) {
      throw new Error('Configuración de API no encontrada.');
    }

    try {
      setLoading(true);
      const res = await fetch(`${API_URL}/upload.php`, {
        method: 'POST',
        body: formData,
      });
      const data = await res.json();

      if (!res.ok || data.error) throw new Error(data.error || 'Error desconocido');

      alert(`✅ Archivo procesado correctamente (${data.insertados} registros)`);
      router.push('/results');
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
    } catch (err: any) {
      setError(err.message || 'Error al procesar el archivo.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <Box sx={{ minHeight: '100vh', backgroundColor: '#f9fafb', display: 'flex', flexDirection: 'column' }}>
      <Header buttonText="Vista de Activos" buttonAction="/results" />
      {/* Contenido principal */}
      <Container
        sx={{
          flexGrow: 1,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          flexDirection: 'column',
          py: 8,
        }}
      >
        <Paper
          component="form"
          onSubmit={handleSubmit}
          sx={{
            width: '100%',
            maxWidth: 500,
            textAlign: 'center',
            p: 6,
            border: '2px dashed #d1d5db',
            borderRadius: 3,
            backgroundColor: 'white',
            boxShadow: '0px 2px 6px rgba(0,0,0,0.05)',
          }}
        >
          <CloudUpload sx={{ fontSize: 60, color: '#2563eb', mb: 2 }} />
          <Typography variant="h6" fontWeight={600} sx={{ mb: 1 }}>
            Arrastra y suelta tu archivo .txt aquí
          </Typography>

          <input
            type="file"
            accept=".txt"
            id="fileInput"
            onChange={(e) => setFile(e.target.files?.[0] || null)}
            style={{ display: 'none' }}
          />
          <label htmlFor="fileInput">
            <Button
              variant="outlined"
              component="span"
              sx={{
                mt: 2,
                mb: 3,
                borderColor: '#d1d5db',
                color: 'black',
                textTransform: 'none',
                borderRadius: 2,
                '&:hover': {
                  backgroundColor: '#f3f4f6',
                  borderColor: '#9ca3af',
                },
              }}
            >
              Seleccionar Archivo
            </Button>
          </label>

          <Typography variant="body2" color="text.secondary" sx={{ mb: 4 }}>
            {file ? `📄 ${file.name}` : 'Ningún archivo seleccionado'}
          </Typography>

          <Button
            type="submit"
            variant="contained"
            disabled={loading}
            sx={{
              backgroundColor: '#2563eb',
              textTransform: 'none',
              borderRadius: 2,
              width: '100%',
              py: 1.2,
              fontWeight: 500,
              '&:hover': { backgroundColor: '#1e40af' },
            }}
          >
            {loading ? (
              <Box display="flex" alignItems="center" justifyContent="center">
                <CircularProgress size={22} sx={{ color: 'white', mr: 1 }} />
                Procesando...
              </Box>
            ) : (
              'Subir Archivo'
            )}
          </Button>
        </Paper>

        {/* Mensaje de error */}
        {error && (
          <Alert
            severity="error"
            sx={{
              mt: 4,
              maxWidth: 500,
              borderRadius: 2,
              backgroundColor: '#fee2e2',
              color: '#b91c1c',
              fontWeight: 500,
            }}
          >
            {error}
          </Alert>
        )}
      </Container>
    </Box>
  );
}
