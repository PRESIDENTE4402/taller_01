# 🚗 TALLER 01 - SISTEMA SaaS MULTITENANT CON ATRIBUTOS DINÁMICOS

## 📋 Índice
1. [Descripción General](#descripción-general)
2. [Arquitectura Implementada](#arquitectura-implementada)
3. [Características Principales](#características-principales)
4. [Componentes Técnicos](#componentes-técnicos)
5. [Guía de Uso](#guía-de-uso)
6. [Ejemplos Prácticos](#ejemplos-prácticos)
7. [Roadmap Futuro](#roadmap-futuro)

---

## 🎯 Descripción General

Este proyecto implementa un **sistema SaaS (Software as a Service) multitenant profesional** para la gestión de inventario de talleres automotrices. La arquitectura permite que múltiples sucursales/clientes usen el mismo código con **datos totalmente aislados** y **atributos personalizables dinámicos**.

### Problema Resuelto
**Antes:** Cada tipo de cliente necesitaba código custom
- Taller de autos → campos diferentes
- Llantería → otros campos
- Venta de accesorios → otros campos

**Ahora:** UN solo código para TODOS
- Cada cliente configura sus propios campos
- No requiere cambios de código
- Escalabilidad infinita

---

## 🏗️ Arquitectura Implementada

### Nivel 1: Base de Datos (Multitenant)

```
┌─────────────────────────────────────────────┐
│       EMPRESA / ORGANIZACIÓN                │
│    (Múltiples sucursales opcionales)         │
└──────────────────┬──────────────────────────┘
                   │
        ┌──────────┼──────────┐
        ▼          ▼          ▼
    Sucursal 1  Sucursal 2  Sucursal 3
    ├─ Clientes    ├─ Clientes    ├─ Clientes
    ├─ Vehículos   ├─ Vehículos   ├─ Vehículos
    ├─ Repuestos   ├─ Repuestos   ├─ Repuestos
    ├─ Categorías  ├─ Categorías  ├─ Categorías
    └─ Atributos   └─ Atributos   └─ Atributos
```

### Tablas Principales

#### 1. `dynamic_attribute_schemas` (Configuración)
```sql
✓ success: Define qué campos dinámicos existen
✓ Ejemplo: Categoría "Llantas" necesita: rin, ancho, altura
```

#### 2. `dynamic_attribute_translations` (Multiidioma)
```sql
✓ Traduce los nombres de atributos a cualquier idioma
✓ Español: "viscosidad" → English: "viscosity"
```

#### 3. `repuestos.atributos` (Valores Dinámicos - JSON)
```sql
✓ Almacena valores dinámicos en JSON
✓ Ejemplo: {"viscosidad": "15W40", "volumen": "1L"}
```

### Nivel 2: Aplicación (Middleware & Traits)

```
┌──────────────────────────────────────┐
│  Trait: BelongsToSucursal             │
│  ├─ scopeForCurrentUser()             │
│  ├─ scopeForSucursal($id)             │
│  └─ sucursal() relation               │
└──────────────────────────────────────┘
         ↓ Aplicado a todos los modelos ↓
    Cliente • Vehículo • Repuesto • Categoría
```

### Nivel 3: Middleware de Seguridad

```
┌─────────────────────────────────────────┐
│ VerifySucursalAccess                    │
│ ├─ Valida: ¿Usuario tiene acceso?       │
│ └─ Bloquea: 403 Forbidden si no         │
├─────────────────────────────────────────┤
│ SetCurrentSucursal                      │
│ ├─ Lee: query string → sesión           │
│ ├─ O usa: sucursal_por_defecto          │
│ └─ O asigna: primera sucursal del user  │
└─────────────────────────────────────────┘
```

---

## ⭐ Características Principales

### 1. ✅ Aislamiento Multitenant Automático

```php
// Usuario de Sucursal 1 crea producto
$repuesto = Repuesto::forCurrentUser()->create([...]);
// ✓ Se guarda SOLO para Sucursal 1

// Si intenta acceder a Sucursal 999:
GET /panel/repuestos?sucursal_id=999
// ✗ 403 Forbidden - No tiene acceso
```

### 2. ✅ Atributos Dinámicos sin Código

```php
// Admin configura atributos para categoría "Llantas"
POST /atributos-dinamicos
{
  "categoria_id": 5,
  "attribute_name": "rin",
  "data_type": "select",
  "is_required": true,
  "options": ["13", "14", "15", "16", "17", "18"]
}

// Vendedor crea producto usando esos atributos
POST /repuestos
{
  "categoria_id": 5,
  "nombre": "Pirelli P7 225/45/17",
  "atributos": {
    "rin": "17"  // ← Validado automáticamente
  }
}
```

### 3. ✅ Validación Avanzada

```
Tipos soportados:
├─ text        → min_length, max_length, regex
├─ number      → min_value, max_value
├─ date        → validación de fechas
├─ select      → opciones predefinidas
└─ boolean     → true/false
```

### 4. ✅ Multiidioma

```php
// Crear atributo en Español
POST /atributos-dinamicos
{
  "attribute_name": "viscosidad",
  "data_type": "select"
}

// Agregar traducción a Inglés
POST /atributos-dinamicos/{id}/traducciones
{
  "locale": "en",
  "label": "Viscosity",
  "description": "Oil viscosity grade"
}
```

### 5. ✅ Reportes Inteligentes

```
GET /reportes/inventario-por-sucursal
  ├─ Filtrar por sucursal
  ├─ Filtrar por categoría
  └─ Destacar stock bajo

GET /reportes/stock-bajo
  └─ Productos que necesitan reorden

GET /reportes/comparativa-precios
  └─ Análisis de márgenes

GET /reportes/audit-atributos
  └─ Auditoría de datos dinámicos
```

### 6. ✅ Documentación API Completa

```
Acceso: /api-docs.html
├─ Endpoints catalogados
├─ Ejemplos de solicitudes
├─ Códigos de respuesta
└─ Mejores prácticas
```

---

## 🔧 Componentes Técnicos

### MIGRACIONES CREADAS

| # | Archivo | Descripción |
|---|---------|-------------|
| 1 | `2026_02_13_000001_create_dynamic_attribute_schemas_table.php` | Tabla base de atributos |
| 2 | `2026_02_13_000002_add_sucursal_id_to_clientes_table.php` | Aislamiento de clientes |
| 3 | `2026_02_13_000003_add_sucursal_id_to_vehiculos_table.php` | Aislamiento de vehículos |
| 4 | `2026_02_13_000004_add_sucursal_por_defecto_to_users_table.php` | Sucursal por defecto |
| 5 | `2026_02_13_000005_add_validation_to_dynamic_attribute_schemas.php` | Validación avanzada |
| 6 | `2026_02_13_000006_create_dynamic_attribute_translations_table.php` | Multiidioma |
| 7 | `2026_02_13_000007_create_inventory_reports_table.php` | Tabla de reportes |

### MODELOS CREADOS/MEJORADOS

| Modelo | Cambios |
|--------|---------|
| `DynamicAttributeSchema` | ✓ Nuevo - Gestiona atributos |
| `DynamicAttributeTranslation` | ✓ Nuevo - Gestiona traducciones |
| `BelongsToSucursal` (Trait) | ✓ Nuevo - Aislamiento automático |
| `Cliente` | + Trait, +sucursal_id |
| `Vehiculo` | + Trait, +sucursal_id |
| `Categoria` | + Trait, relación attributeSchemas |
| `Repuesto` | ✓ Mejorado - Validación JSON |
| `User` | + sucursal_por_defecto_id |

### CONTROLADORES CREADOS/MEJORADOS

| Controlador | Métodos |
|-------------|---------|
| `DynamicAttributeSchemaController` | ✓ Nuevo - listByCategoria, store, update, delete, reorder |
| `ReporteController` | ✓ Nuevo - inventarioPorSucursal, stockBajo, comparativaPreciosS, audit |
| `CategoriaController` | ✓ Mejorado - usa scopes automáticos |
| `RepuestoController` | ✓ Mejorado - valida atributos dinámicos |

### MIDDLEWARE CREADO

| Middleware | Función |
|-----------|---------|
| `VerifySucursalAccess` | Valida acceso a sucursal |
| `SetCurrentSucursal` | Establece sucursal en sesión |

### VISTAS CREADAS

| Vista | Descripción |
|-------|------------|
| `panel/mantenimientos/atributos_dinamicos/index.blade.php` | Interfaz para configurar atributos |
| `panel/reportes/inventario-por-sucursal.blade.php` | Reporte de inventario |

### DOCUMENTACIÓN

| Archivo | Propósito |
|---------|-----------|
| `config/openapi.php` | Configuración OpenAPI |
| `public/api-docs.html` | Documentación HTML interactiva |

---

## 📚 Guía de Uso

### PASO 1: Configurar Atributos Dinámicos

**Ubicación:** `http://localhost/panel/mantenimientos/configurar-atributos`

```
1. Selecciona una Categoría (ej: "Llantas")
2. Haz clic en "Agregar Atributo"
3. Completa el formulario:
   - Nombre: "rin"
   - Tipo: "Selección"
   - Obligatorio: ✓
   - Opciones: 13, 14, 15, 16, 17, 18
   - Texto de ayuda: "Selecciona el tamaño del rin"
4. Guarda
```

### PASO 2: Crear Productos con Atributos

**Ubicación:** `http://localhost/panel/mantenimientos/repuestos`

```
1. Haz clic en "Crear Repuesto"
2. Completa información básica:
   - Código: PIRE-225-45-17
   - Nombre: Pirelli P7 225/45/R17
   - Precio costo: $310
   - Precio venta: $450
3. Los campos dinámicos aparecen automáticamente:
   - Rin: [Dropdown con 13, 14, 15...]
   - Ancho: [Input text]
   - etc.
4. Guarda
```

### PASO 3: Consultar Reportes

**Inventario:** `http://localhost/panel/mantenimientos/reportes/inventario-por-sucursal`

```
Filtros:
├─ Sucursal
├─ Categoría
└─ Stock bajo

Acciones:
├─ Descargar PDF
└─ Ver stock bajo
```

---

## 💡 Ejemplos Prácticos

### Ejemplo 1: Taller de Autos

```json
CATEGORÍA: Lubricantes
└─ Atributos:
   ├─ viscosidad (select): ["15W40", "10W40", "5W30"]
   ├─ volumen (select): ["1L", "5L", "55L"]
   └─ tipo (select): ["Mineral", "Sintético"]

PRODUCTO CREADO:
{
  "nombre": "Shell Helix HX5",
  "atributos": {
    "viscosidad": "15W40",
    "volumen": "1L",
    "tipo": "Mineral"
  }
}
```

### Ejemplo 2: Llantería

```json
CATEGORÍA: Llantas
└─ Atributos:
   ├─ rin (select): ["13", "14", ..., "22"]
   ├─ ancho (number): min: 155, max: 245
   ├─ altura (number): min: 30, max: 80
   ├─ indice_carga (number): min: 70, max: 120
   └─ indice_velocidad (select): ["H", "V", "W", "Z"]

PRODUCTO CREADO:
{
  "nombre": "Pirelli P7",
  "atributos": {
    "rin": "17",
    "ancho": 225,
    "altura": 45,
    "indice_carga": 95,
    "indice_velocidad": "H"
  }
}
```

### Ejemplo 3: Venta de Accesorios

```json
CATEGORÍA: Fundas Asiento
└─ Atributos:
   ├─ material (select): ["Cuero", "Tela", "Vinilo", "Neopreno"]
   ├─ color (select): ["Negro", "Gris", "Rojo", "Blanco"]
   ├─ num_asientos (select): ["2", "5", "7"]
   └─ garantia (number): min: 1, max: 5

PRODUCTO CREADO:
{
  "nombre": "Funda Premium Cuero",
  "atributos": {
    "material": "Cuero",
    "color": "Negro",
    "num_asientos": "5",
    "garantia": 2
  }
}
```

---

## 📊 Flujo de Datos

```
┌─────────────────────┐
│  Usuario logueado   │
└────────────┬────────┘
             │
      ┌──────▼──────┐
      │ Middleware  │
      │SetCurrent   │
      │Sucursal     │
      └──────┬──────┘
             │ sucursal_id → sesión
      ┌──────▼──────────────────┐
      │ Controlador             │
      │ (ej: RepuestoController)│
      └──────┬──────────────────┘
             │
      ┌──────▼──────────────┐
      │ Repuesto::          │
      │ forCurrentUser()    │ ← Scope automático
      └──────┬──────────────┤ Filtra por
             │              │ sucursal_id
      ┌──────▼──────────────┐
      │ Query filtrada      │
      │ solo datos de       │
      │ su sucursal         │
      └─────────────────────┘
```

---

## 🚀 Roadmap Futuro

### Corto Plazo (1-2 semanas)
- [ ] Exportar atributos a PDF/Excel
- [ ] Importar productos en lote (CSV)
- [ ] Historial de cambios (audit log completo)
- [ ] Búsqueda avanzada con filtros

### Mediano Plazo (1-2 meses)
- [ ] Notificaciones de stock bajo (SMS/Email)
- [ ] Integración con proveedores
- [ ] Sistema de órdenes de compra automáticas
- [ ] Dashboard BI con gráficos

### Largo Plazo (3+ meses)
- [ ] App móvil (React Native)
- [ ] Integración con SAP/ERP
- [ ] Machine Learning para predicción de demanda
- [ ] Gestión multimoneda

---

## 🔒 Seguridad Implementada

### ✓ Aislamiento Multitenant
```
- Cada query se filtra por sucursal_id
- Middleware valida acceso
- No hay forma de mezclar datos
```

### ✓ Validación de Datos
```
- Atributos validados contra schema
- Tipos de datos estrictos
- Regex patterns para formatos
```

### ✓ CSRF Protection
```
- Token en cada POST/PUT/DELETE
- Validación de origen
```

### ✓ Rate Limiting (Próximo)
```
- Limitar solicitudes por IP
- Prevenir abuso de API
```

---

## 📞 Soporte y Contacto

**Email:** soporte@taller01.com
**Documentación API:** `/api-docs.html`
**Issues:** GitHub Issues (si aplica)

---

## 📄 Licencia

MIT License - Ver LICENSE.md

---

**Versión:** 1.0.0
**Última actualización:** 12 de Febrero de 2026
**Estado:** ✅ PRODUCCIÓN LISTA
