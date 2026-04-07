# Softlinkia Security Monitor - Prueba Técnica

Este proyecto es una aplicación web monolítica desarrollada en **Laravel 11** para la gestión de operaciones y monitoreo de eventos de seguridad, realizada como parte de la prueba técnica para **Softlinkia S.A. de C.V.**

## 🚀 Descripción del Proyecto

El sistema permite la gestión de dispositivos de seguridad simulados, el monitoreo de eventos en tiempo real y la gestión automática/manual de incidencias basadas en reglas de negocio. Incluye un control de acceso robusto basado en roles (RBAC) y un panel administrativo para supervisar la operación.

### Requerimientos Implementados
-   **Autenticación y RBAC**: Roles diferenciados para Administrador, Operador y Cliente.
-   **Módulo de Dispositivos**: CRUD completo con estados, ubicación y metadatos JSON.
-   **Simulación de Eventos**: Generación automática de eventos externos (desconexiones, alertas).
-   **Gestión de Incidencias**: Creación automática por reglas de negocio o manual.
-   **Dashboard Operativo**: Visualización de incidencias, dispositivos alertas y estadísticas.
-   **Auditoría (Audit Log)**: Registro completo de acciones críticas y sesiones.

## 🛠️ Stack Tecnológico

-   **Backend**: Laravel 11 (PHP 8.2+)
-   **Frontend**: Blade + TailwindCSS + Livewire (para reactividad en dashboard).
-   **Base de Datos**: MySQL (8.0+)
-   **Seguridad**: Spatie Permissions, Middleware de autenticación y protección CSRF/XSS/SQLi.

## 📦 Instalación

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/Darioantonio20/softlinkia-security-monitor.git
    cd softlinkia-security-monitor
    ```

2.  **Instalar dependencias:**
    ```bash
    composer install
    npm install && npm run build
    ```

3.  **Configurar entorno:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Base de Datos (MySQL):**
    Asegúrate de tener un servidor MySQL corriendo y crea la base de datos `softlinkia_security`:
    ```bash
    php artisan migrate --seed
    ```

5.  **Ejecutar localmente:**
    ```bash
    php artisan serve
    ```

## 🔐 Accesos de Prueba (Próximamente)

Una vez completada la implementación, el sistema contará con los siguientes accesos predefinidos:
-   **Admin**: admin@softlinkia.com
-   **Operador**: operador@softlinkia.com
-   **Cliente**: cliente@softlinkia.com

---
*Desarrollado con ❤️ para Softlinkia S.A. de C.V. - Abril 2026*
