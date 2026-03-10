# Hyplast Aprobaciones - Sistema de Aprobaciones y Autorizaciones

## Descripción
Sistema de flujos de aprobación para solicitudes de compra, órdenes de trabajo, requisiciones y otros procesos que requieren autorización.

## Características Principales
- 🔐 Flujos de aprobación multinivel
- 📋 Solicitudes de compra (OC)
- ✅ Requisiciones de materiales
- 📊 Dashboard de pendientes
- 🔔 Notificaciones automáticas
- 📝 Auditoría completa
- 👥 Aprobadores personalizables

## Modelos Principales
- **SolicitudOc**: Solicitudes de orden de compra
- **SolicitudOcLinea**: Líneas de la solicitud
- **SolicitudOcAuditoria**: Auditoría de cambios
- **Requisition**: Requisiciones de materiales

## API Endpoints
```
GET    /api/approvals/pending      # Pendientes de aprobación
POST   /api/approvals/{id}/approve # Aprobar solicitud
POST   /api/approvals/{id}/reject  # Rechazar solicitud
```

## Requisitos
- PHP >= 8.1
- Laravel >= 10.x

## Instalación
```bash
composer install
php artisan migrate
```

## Autor y Propietario
**Néstor Serrano**  
Desarrollador Full Stack  
GitHub: [@nestorserrano](https://github.com/nestorserrano)

## Licencia
Propietario - © 2026 Néstor Serrano. Todos los derechos reservados.
