---
description: Flujo de trabajo para detección, reparación de errores y pruebas de calidad.
---

# Flujo de QA y Depuración

Sigue estos pasos para identificar y solucionar errores o asegurar la estabilidad del proyecto.

## 1. Verificación Inicial
// turbo
1. Ejecuta las pruebas existentes para verificar el estado actual:
   `php artisan test`

## 2. Inspección de Errores
2. Si hay errores reportados, revisa los logs de Laravel:
   `tail -n 100 storage/logs/laravel.log`
3. Si el error es visual o de JavaScript, abre las herramientas de desarrollador en el navegador y verifica la consola.

## 3. Resolución de Bugs
4. Localiza el archivo afectado basándote en la traza del error (Stack Trace).
5. Aplica la corrección siguiendo los estándares de Laravel y PHP 8.x.
6. Limpia la caché si es necesario:
   `php artisan optimize:clear`

## 4. Validación Final
// turbo
7. Ejecuta nuevamente las pruebas para asegurar que no hay regresiones:
   `php artisan test`
8. (Opcional) Crea una nueva prueba unitaria o de integración si el error era crítico para evitar que vuelva a ocurrir.
