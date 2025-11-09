# Prueba Técnica: Gestión de Activos para GEMA SAS

Este proyecto implementa la solución técnica para la **carga masiva de usuarios** mediante un archivo de texto plano (`.txt`), su validación, almacenamiento en MySQL y posterior visualización organizada en tres tablas (Activos, Inactivos, En Espera).

## 🛠️ Tecnologías Utilizadas

| Componente | Tecnología | Propósito |
| :--- | :--- | :--- |
| **Frontend** | **NextJs** (con MUI) | Interfaz de usuario para carga y visualización de las tres tablas. |
| **Backend** | **PHP** (PDO) | API REST para validación, inserción transaccional y consulta agrupada de datos. |
| **Base de Datos** | **MySQL** | Almacenamiento persistente y seguro de los datos. |
| **Entorno** | **Docker / Docker Compose** | Facilita la configuración y portabilidad del entorno PHP y MySQL. |

---

## 💡 Flujo de la Solución

### Flujo de Carga y Validación
* **Validación Estricta:** El *backend* en PHP verifica que cada línea cumpla con el formato: **4 valores**, *email* válido y `código` de estado (`1`, `2`, o `3`).
* **Manejo de Errores:** En caso de fallar el formato, el proceso se revierte y devuelve el error: **"El formato interno del archivo no es válido..."**.
* **Integridad de Datos:** Se utiliza **`INSERT IGNORE`** en la transacción de la base de datos para manejar silenciosamente los registros duplicados (`email` + `codigo`), asegurando que solo se inserten los registros válidos.

### Flujo de Visualización
* El *backend* consulta todos los usuarios y los **agrupa** en tres *arrays* (`activos`, `inactivos`, `espera`) en el servidor (`list.php`).
* El *frontend* en NextJs consume este único *endpoint* y renderiza los datos en **tres tablas separadas**, cumpliendo con los *mockups* de "Gestión de Activos".

---

## ⚙️ Manual de Instalación y Ejecución

Se requiere tener **Docker Compose** y **Node.js/npm** instalados.

### 1. Clonar el Repositorio
Clona el repositorio en tu editor de texto, usando la terminal y pegando el siguiente comando
```bash
git clone https://github.com/CaptainLevi20/gema-sas-app.git
cd gema-sas-app
```

### 2. Configuración de Variables de Entorno
Crea un archivo .env en la raíz del proyecto para definir las credenciales de la base de datos
```bash
# .env
DB_HOST=db
DB_NAME=gema_sas
DB_USER=user_gema
DB_PASSWORD=secret_password
NEXT_PUBLIC_API_URL=http://localhost:8080
```

### 3. Configuración del Backend (PHP y MySQL):
En la terminal, levanta los servicios de Docker
```bash
docker-compose up -d
```

### 4. Ejecución del Frontend (NextJs):
En la terminal, instala las dependencias y ejecuta el servidor de desarrollo
```bash
cd frontend-nextjs
npm install
npm run dev
```

### 5. Ejecución del Frontend (NextJs):

| La aplicación estará disponible en http://localhost:3000/ |
| El API de PHP estará disponible en http://localhost:8080/ | 
