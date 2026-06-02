# Plan de Pruebas - Santa Cruz Segura Predictiva

## 1. Pruebas unitarias
- `DuplicateDetectionService`: detección por radio/tiempo, fusión con testigos
- `FalseReportService`: inhabilitación tras 10 falsos/mes
- `LoginSecurityService`: bloqueo tras 5 intentos
- `PredictionService`: heurística y umbral 70%
- `AuditService`: registro append-only

## 2. Pruebas de integración
- Flujo registro → verificación email → login → reporte incidente
- Webhook WhatsApp → creación incidente → fusión duplicados
- Generación alertas → notificación suscriptores
- Reportes mensuales/semanales con datos reales

## 3. Pruebas de rendimiento
- 100 req/s con `throttle:global`
- Dashboard < 2 s con 10.000 usuarios simulados
- Predicciones < 5 s (`scsp:generate-predictions`)

## 4. Pruebas de seguridad
- Cifrado teléfono (`encrypted` cast)
- Roles: vecino no puede validar incidentes
- Auditoría login fallido
- CSRF en formularios web

## 5. Pruebas de aceptación (UAT)
- Adulto mayor reporta por voz en < 30 s
- Junta vecinal descarga PDF mensual
- Policía consulta mapa predictivo
- Vecino recibe alerta de barrio nocturno

## Ejecución
```bash
php artisan test
php artisan scsp:generate-predictions
php artisan schedule:work
```
