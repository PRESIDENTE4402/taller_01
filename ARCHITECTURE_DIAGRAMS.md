# 🏗️ ARQUITECTURA VISUAL - DIAGRAMAS SISTEMA SaaS

## 1. Capas de la Aplicación

```
┌─────────────────────────────────────────────────────────────────┐
│                    CAPA PRESENTACIÓN                            │
│  Blade Templates (HTML + Tailwind)                              │
│  ├─ atributos_dinamicos/index.blade.php (CRUD dinámico)         │
│  ├─ reportes/inventario-por-sucursal.blade.php (Dashboard)      │
│  └─ API Docs (HTML en /api-docs.html)                           │
└────────────┬────────────────────────────────────────────────────┘
             │ HTTP Request
┌────────────▼────────────────────────────────────────────────────┐
│                    CAPA MIDDLEWARE                              │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ VerifySucursalAccess                                    │   │
│  │ └─ ¿Usuario tiene acceso a sucursal_id?                │   │
│  │    Bloquea si: NO → 403 Forbidden                       │   │
│  │    Permite si: YES → Siguiente                          │   │
│  └──────────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ SetCurrentSucursal                                      │   │
│  │ ├─ Leer ?sucursal_id de request                         │   │
│  │ ├─ Validar que usuario tenga acceso                     │   │
│  │ └─ Guardar en session['sucursal_id']                    │   │
│  └──────────────────────────────────────────────────────────┘   │
└────────────┬────────────────────────────────────────────────────┘
             │ Request filtrado + sesión activa
┌────────────▼────────────────────────────────────────────────────┐
│                    CAPA ROUTING                                 │
│  Route::get('/panel/repuestos', [RepuestoController@index])     │
│  Route::post('/atributos-dinamicos', [SchemaController@store])  │
│  Route::get('/reportes/...', [ReporteController@...]])          │
└────────────┬────────────────────────────────────────────────────┘
             │ Route matched → Controller method
┌────────────▼────────────────────────────────────────────────────┐
│                    CAPA CONTROLLERS                             │
│  ┌──────────────────────────┐  ┌──────────────────────────┐    │
│  │ DynamicAttributeSchema   │  │ ReporteController        │    │
│  │ Controller               │  │                          │    │
│  │ ├─ listByCategoria()     │  │ ├─ inventarioPorSucursal│    │
│  │ ├─ store()               │  │ ├─ stockBajo()           │    │
│  │ ├─ update()              │  │ ├─ comparativaPreciosS()│    │
│  │ ├─ delete()              │  │ └─ auditAtributosdinám │    │
│  │ └─ reorder()             │  │                          │    │
│  └──────────────────────────┘  └──────────────────────────┘    │
│  ┌──────────────────────────┐  ┌──────────────────────────┐    │
│  │ RepuestoController       │  │ CategoriaController      │    │
│  │ ├─ index() ← forCurrent  │  │ ├─ index() ← forCurrent  │    │
│  │ ├─ store() ← validar     │  │ ├─ store()               │    │
│  │ └─ update()              │  │ └─ update()              │    │
│  └──────────────────────────┘  └──────────────────────────┘    │
└────────────┬────────────────────────────────────────────────────┘
             │ Interacción con Modelos
┌────────────▼────────────────────────────────────────────────────┐
│                    CAPA MODELOS                                 │
│  ┌────────────────────────────────────────────────────────┐    │
│  │ Trait: BelongsToSucursal                               │    │
│  │ ├─ scopeForCurrentUser() → WHERE sucursal_id IN (...)  │    │
│  │ ├─ scopeForSucursal($id) → WHERE sucursal_id = $id     │    │
│  │ └─ sucursal() → hasOne(Sucursal)                       │    │
│  └────────────────────────────────────────────────────────┘    │
│  Aplicado a: Cliente, Vehiculo, Repuesto, Categoria, Cita      │
│  ┌────────────────────────────────────────────────────────┐    │
│  │ DynamicAttributeSchema                                 │    │
│  │ ├─ categoria() → belongsTo(Categoria)                  │    │
│  │ ├─ translations() → hasMany(Translation)               │    │
│  │ ├─ getValidationRules() → generar reglas Laravel       │    │
│  │ └─ getLabel($locale) → traducción por idioma           │    │
│  └────────────────────────────────────────────────────────┘    │
│  ┌────────────────────────────────────────────────────────┐    │
│  │ Repuesto                                               │    │
│  │ ├─ validateAttributes(cat_id, attrs) → Valida JSON     │    │
│  │ ├─ atributos (casts JSON) → Array dinámico             │    │
│  │ └─ categoria() → belongsTo(Categoria)                  │    │
│  └────────────────────────────────────────────────────────┘    │
└────────────┬────────────────────────────────────────────────────┘
             │ Queries ejecutadas
┌────────────▼────────────────────────────────────────────────────┐
│                    CAPA DATA (MySQL)                            │
│  ┌──────────────────────────────────────────────────────┐      │
│  │ dynamic_attribute_schemas                            │      │
│  │ ├─ id, categoria_id, sucursal_id, attribute_name     │      │
│  │ ├─ data_type, is_required, options (JSON)            │      │
│  │ ├─ validation_regex, min/max_value, help_text        │      │
│  │ └─ display_order, timestamps                         │      │
│  └──────────────────────────────────────────────────────┘      │
│  ┌──────────────────────────────────────────────────────┐      │
│  │ dynamic_attribute_translations                        │      │
│  │ ├─ id, dynamic_attribute_schema_id (FK)               │      │
│  │ ├─ locale (es, en, fr, pt, de, it)                   │      │
│  │ ├─ label, description                                │      │
│  │ └─ timestamps                                        │      │
│  └──────────────────────────────────────────────────────┘      │
│  ┌──────────────────────────────────────────────────────┐      │
│  │ repuestos                                            │      │
│  │ ├─ id, categoria_id, sucursal_id                     │      │
│  │ ├─ nombre, codigo, atributos (JSON) ← DINÁMICO       │      │
│  │ ├─ precio_costo, precio_venta                        │      │
│  │ └─ timestamps                                        │      │
│  └──────────────────────────────────────────────────────┘      │
│  Otros tables: clientes, vehiculos, categorias, sucursales...   │
│  Todos con: sucursal_id para aislamiento multitenant            │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Flujo de Autenticación y Sesión

```
┌─────────────────┐
│ Usuario abre    │
│ http://localhost│
└────────┬────────┘
         │
    ┌────▼─────────────────────┐
    │ /login (sin autenticar)   │
    │ → Auth::check() = false   │
    └────┬─────────────────────┘
         │ Ingresa credenciales
    ┌────▼─────────────────────┐
    │ Auth Middleware           │
    │ → Usuario autenticado ✓   │
    │ → session['user_id'] = X  │
    └────┬─────────────────────┘
         │ Accede a /panel/repuestos
    ┌────▼───────────────────────────────┐
    │ SetCurrentSucursal Middleware       │
    │                                    │
    │ Prioridad:                         │
    │ 1. ?sucursal_id=X (si tiene acceso)│
    │ 2. user()->sucursal_por_defecto_id │
    │ 3. user()->sucursales->first()     │
    │                                    │
    │ session['sucursal_id'] = X         │
    └────┬───────────────────────────────┘
         │
    ┌────▼───────────────────────────────┐
    │ VerifySucursalAccess Middleware     │
    │                                    │
    │ ¿Usuario tiene acceso a sucursal X?│
    │ → SELECT * FROM user_sucursal      │
    │   WHERE user_id = auth()->id()     │
    │   AND sucursal_id = session(...)   │
    │                                    │
    │ SI: permitir acceso ✓              │
    │ NO: 403 Forbidden ✗                │
    └────┬───────────────────────────────┘
         │
    ┌────▼───────────────────────────────┐
    │ Controller                          │
    │ RepuestoController@index()          │
    │                                    │
    │ $repuestos = Repuesto::            │
    │   forCurrentUser()  ← Scopeautomático
    │   ->paginate(20)                   │
    └────┬───────────────────────────────┘
         │
    ┌────▼───────────────────────────────┐
    │ forCurrentUser() SCOPE:             │
    │                                    │
    │ SELECT * FROM repuestos            │
    │ WHERE sucursal_id IN (             │
    │   SELECT sucursal_id               │
    │   FROM user_sucursal               │
    │   WHERE user_id = $user_id         │
    │ )                                  │
    │                                    │
    │ ✓ Datos aislados por usuario       │
    │ ✓ No puede ver otras sucursales    │
    └────┬───────────────────────────────┘
         │
    ┌────▼───────────────────────────────┐
    │ Vista (Blade) recibe:              │
    │ - $repuestos (solo su sucursal)   │
    │ - session('sucursal_id')          │
    │ - auth()->user()                  │
    └────┬───────────────────────────────┘
         │
    ┌────▼───────────────────────────────┐
    │ Usuario ve:                        │
    │ ✓ Sus productos                    │
    │ ✓ Sus reportes                     │
    │ ✗ Datos de otras sucursales NO     │
    └─────────────────────────────────────┘
```

---

## 3. Flujo de Creación de Atributo Dinámico

```
┌──────────────────────────────────────┐
│ Admin abre:                          │
│ /panel/mantenimientos/               │
│   configurar-atributos               │
└────────────┬─────────────────────────┘
             │
        ┌────▼──────────────────────┐
        │ frontend carga categorías │
        │ AJAX GET /atributos-     │
        │   dinamicos?categoria_id=X│
        └────┬───────────────────────┘
             │
        ┌────▼───────────────────────────────────┐
        │ DynamicAttributeSchemaController       │
        │ @listByCategoria($id)                  │
        │                                        │
        │ $schemas = DynamicAttributeSchema::    │
        │   forCurrentUser()         ← MidYwarw! │
        │   ->where('categoria_id', $id)        │
        │   ->orderBy('display_order')          │
        │   ->get()                             │
        │                                        │
        │ return $schemas->with('translations') │
        └────┬───────────────────────────────────┘
             │ JSON response
        ┌────▼──────────────────────┐
        │ JavaScript mostrar datos  │
        │ e inyectar en formulario  │
        └────┬───────────────────────┘
             │ Admin completa formulario:
             │ ├─ attribute_name: "rin"
             │ ├─ data_type: "select"
             │ ├─ is_required: true
             │ ├─ data_type: "select"
             │ └─ options: ["13","14","15",...]
             │
        ┌────▼───────────────────────────────┐
        │ AJAX POST /atributos-dinamicos      │
        │ Content-Type: application/json      │
        │                                    │
        │ {                                  │
        │   "categoria_id": 5,               │
        │   "attribute_name": "rin",         │
        │   "data_type": "select",           │
        │   "options": [...],                │
        │   "is_required": true,             │
        │   ...                              │
        │ }                                  │
        └────┬───────────────────────────────┘
             │
        ┌────▼───────────────────────────────────┐
        │ DynamicAttributeSchemaController       │
        │ @store()                               │
        │                                        │
        │ // Validar                            │
        │ $validated = $request->validate([     │
        │   'categoria_id' => 'required|int',   │
        │   'attribute_name' => 'required|str', │
        │   'data_type' => 'in:text,number,...',│
        │   'options' => 'sometimes|array',     │
        │ ])                                    │
        │                                        │
        │ // Crear                              │
        │ DynamicAttributeSchema::create([      │
        │   ...$validated,                      │
        │   'sucursal_id' => auth()->           │
        │     user()->sucursal_id,              │
        │ ])                                    │
        └────┬───────────────────────────────────┘
             │
        ┌────▼────────────────────────────┐
        │ MySQL INSERT                    │
        │ dynamic_attribute_schemas       │
        │ (categoria_id=5, sucursal_id=1,│
        │ attribute_name='rin', ...)      │
        └────┬────────────────────────────┘
             │
        ┌────▼────────────────────────────┐
        │ Response JSON                   │
        │ { success: true, data: {...} }  │
        └────┬────────────────────────────┘
             │
        ┌────▼────────────────────────┐
        │ JavaScript refresca datos    │
        │ Atributo nuevo aparece       │
        │ en lista sin reload          │
        └─────────────────────────────┘
```

---

## 4. Flujo de Validación de Atributos al Crear Repuesto

```
┌─────────────────────────────────────┐
│ Página de crear repuesto             │
│ Selecciona: Categoría = "Llantas" (ID=5)
└────────────┬──────────────────────────┘
             │
        ┌────▼───────────────────────────────┐
        │ AJAX GET /atributos-dinamicos      │
        │           ?categoria_id=5           │
        │                                    │
        │ Obtiene schemas: rin, ancho, altura│
        └────┬───────────────────────────────┘
             │
        ┌────▼──────────────────────────────┐
        │ JavaScript dinámicamente agrega   │
        │ campos al formulario:             │
        │ ├─ Rin (select) [13...18]         │
        │ ├─ Ancho (number) min:155 max:245│
        │ └─ Altura (number) min:30 max:80 │
        └────┬──────────────────────────────┘
             │ Usuario completa:
             │ - Código: PIRE-17
             │ - Nombre: Pirelli 225/45/17
             │ - Rin: 17
             │ - Ancho: 225
             │ - Altura: 45
             │
        ┌────▼───────────────────────────────┐
        │ POST /repuestos                    │
        │ {                                  │
        │   "nombre": "Pirelli 225/45/17",   │
        │   "categoria_id": 5,               │
        │   "atributos": {                   │
        │     "rin": "17",                   │
        │     "ancho": 225,                  │
        │     "altura": 45                   │
        │   }                                │
        │ }                                  │
        └────┬───────────────────────────────┘
             │
        ┌────▼───────────────────────────────┐
        │ RepuestoController@store()         │
        │                                    │
        │ // VALIDACIÓN DINÁMICA             │
        │ $validated = Repuesto::            │
        │   validateAttributes(              │
        │     $request->categoria_id,  // 5 │
        │     $request->atributos ?? []      │
        │   )                                │
        │                                    │
        │ if (!$validated['valid']) {        │
        │   return response()->json([        │
        │     'errors' => $val['errors']     │
        │   ], 422)                          │
        │ }                                  │
        └────┬───────────────────────────────┘
             │ DENTRO DE validateAttributes():
             │
        ┌────▼───────────────────────┐
        │ Obtener LOS schemas:        │
        │ $schemas =                  │
        │   DynamicAttributeSchema::  │
        │   where('categoria_id', 5)  │
        │   ->get()                   │
        │                             │
        │ Retorna: [{                 │
        │   attribute_name: 'rin',    │
        │   data_type: 'select',      │
        │   options: ['13',...,'18'], │
        │   is_required: true         │
        │ }, ...]                     │
        └────┬───────────────────────┘
             │
        ┌────▼────────────────────────────┐
        │ Para cada schema, validar:       │
        │ 1. Si es_required y no existe   │
        │    → Error                      │
        │ 2. Si data_type='select'        │
        │    → Validar está en options    │
        │ 3. Si data_type='number'        │
        │    → Validar min <= value <= max│
        │ 4. Si validation_regex existe   │
        │    → Validar con regex          │
        └────┬────────────────────────────┘
             │
        ┌────▼──────────────────────────┐
        │ Resultado validación:         │
        │                               │
        │ Si todas OK: ['valid' => true]│
        │ Si error: [                   │
        │   'valid' => false,           │
        │   'errors' => [               │
        │     'rin' => 'Invalid option' │
        │   ]                           │
        │ ]                             │
        └────┬──────────────────────────┘
             │
        ┌────▼──────────────────────────┐
        │ SI ERROR (422):                │
        │ Frontend muestra errores       │
        │ Usuario corrige y reintenta   │
        └────┬──────────────────────────┘
             │
        ┌────▼──────────────────────────┐
        │ SI OK (200):                   │
        │ Guardar en BD:                │
        │ INSERT repuestos (            │
        │   nombre='Pirelli...',        │
        │   { atributos: {              │
        │     rin: '17', ...            │
        │   }}                          │
        │ )                             │
        └────┬──────────────────────────┘
             │
        ┌────▼──────────────────────────┐
        │ Responder 201 Created         │
        │ Redirect a ver detalle        │
        │ o lista                       │
        └──────────────────────────────┘
```

---

## 5. Arquitectura de Reportes

```
┌─────────────────────────────────────────┐
│ Usuario solicita reporte                 │
│ GET /reportes/inventario-por-sucursal   │
└────────────┬────────────────────────────┘
             │
        ┌────▼──────────────────────────┐
        │ ReporteController@            │
        │ inventarioPorSucursal()       │
        │                               │
        │ $repuestos = Repuesto::       │
        │   forCurrentUser() ← FILTRO   │
        │   ->with('categoria')        │
        │   ->with('atributos')        │
        │   ->paginate(20)             │
        └────┬──────────────────────────┘
             │
        ┌────▼───────────────────────────────┐
        │ Calcular agregados:                │
        │ ├─ total_productos = count()       │
        │ ├─ stock_total = sum(stock)        │
        │ ├─ valor_inventario = sum(        │
        │ │   stock * precio_venta)          │
        │ └─ stock_bajo = count(             │
        │    WHERE stock < stock_minimo)    │
        └────┬──────────────────────────────┘
             │
        ┌────▼──────────────────────────────┐
        │ return view('reporte', [          │
        │   'repuestos' => $repuestos,      │
        │   'totales' => $totales,          │
        │   'sucursal' => $sucursal         │
        │ ])                                │
        └────┬──────────────────────────────┘
             │ Blade Template renderiza
        ┌────▼──────────────────────────────┐
        │ Mostrar:                          │
        │ ┌────────────────────────────┐   │
        │ │ 4 KPI Cards               │   │
        │ │ • Total Productos: 150    │   │
        │ │ • Stock Total: 5,230      │   │
        │ │ • Valor Inventario: 2.1M │   │
        │ │ • Stock Bajo Alerta: 12   │   │
        │ └────────────────────────────┘   │
        │ Filtros (Sucursal, Categoría)   │
        │ Tabla con paginación            │
        │ Botón "Exportar PDF"            │
        └────┬──────────────────────────────┘
             │ Click "Exportar PDF"
        ┌────▼──────────────────────────────┐
        │ GET /reportes/inventario-pdf    │
        │                                  │
        │ ReporteController@               │
        │ exportInventarioPDF()            │
        │                                  │
        │ $pdf = PDF::loadView(view, data) │
        │                                  │
        │ return $pdf->download('inv.pdf')│
        └────┬──────────────────────────────┘
             │
        ┌────▼──────────────────────────┐
        │ DomPDF genera PDF             │
        │ con datos y estilos          │
        │ usuariorecibe descarga        │
        └──────────────────────────────┘
```

---

## 6. Matriz de Seguridad (Quién ve qué)

```
ESCENARIO: Empresa con 3 sucursales

┌─────────────────────────────────────────┐
│ USUARIOS:                               │
│ • Juan (Sucursal: Taller A)             │
│ • María (Sucursal: Taller A, B)         │
│ • Admin (Sucursal: A, B, C - admin)    │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ TABLA: repuestos                        │
│                                         │
│ Sucursal A (ID=1): 150 productos       │
│ Sucursal B (ID=2): 200 productos       │
│ Sucursal C (ID=3): 100 productos       │
└─────────────────────────────────────────┘

┌──────────────────────────────────────────────────┐
│ CUANDO QUERIEN DATOS:                           │
│                                                 │
│ Juan ejecuta:                                   │
│ Repuesto::forCurrentUser()->count()            │
│                                                 │
│ SQL EJECUTADO:                                  │
│ SELECT COUNT(*) FROM repuestos                  │
│ WHERE sucursal_id IN (                          │
│   SELECT sucursal_id FROM user_sucursal        │
│   WHERE user_id = X  ← Juan                    │
│ )                                              │
│                                                 │
│ RESULTADO: 150 (SOLO Sucursal A)                │
│ ├─ Sucursal A: 150 ✓ (tiene acceso)             │
│ ├─ Sucursal B: NO                               │
│ └─ Sucursal C: NO                               │
│                                                 │
└──────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────┐
│ María ejecuta:                                   │
│ Repuesto::forCurrentUser()->count()             │
│                                                 │
│ RESULTADO: 350 (A + B)                           │
│ ├─ Sucursal A: 150 ✓ (tiene acceso)             │
│ ├─ Sucursal B: 200 ✓ (tiene acceso)             │
│ └─ Sucursal C: NO                               │
│                                                 │
└──────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────┐
│ Admin ejecuta:                                   │
│ Repuesto::forCurrentUser()->count()             │
│                                                 │
│ RESULTADO: 450 (A + B + C)                       │
│ ├─ Sucursal A: 150 ✓ (tiene acceso)             │
│ ├─ Sucursal B: 200 ✓ (tiene acceso)             │
│ └─ Sucursal C: 100 ✓ (tiene acceso)             │
│                                                 │
└──────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────┐
│ INTENTO HACK:                                    │
│ Juan ejecuta:                                    │
│ Repuesto::where('sucursal_id', 2)->count()      │
│ ✗ BLOQUEA SCOPE: El scope NO se aplica          │
│ ✓ Sigue filtrando por forCurrentUser()          │
│                                                 │
│ Pero...:                                         │
│ Juan accede a:                                   │
│ GET /panel/repuestos?sucursal_id=2              │
│                                                 │
│ Middleware VerifySucursalAccess:                │
│ 1. Extrae sucursal_id=2 del request             │
│ 2. Query: ¿Juan tiene acceso a Sucursal 2?      │
│    SELECT 1 FROM user_sucursal                  │
│    WHERE user_id=X AND sucursal_id=2            │
│ 3. Resultado: 0 filas                            │
│ 4. Response: 403 FORBIDDEN ✓ BLOQUEADO          │
│                                                 │
└──────────────────────────────────────────────────┘
```

---

## 7. Ciclo de Vida de una Migración

```
┌────────────────────┐
│ Nuevo requisito:   │
│ "Agregar campo X"  │
└────────┬───────────┘
         │
    ┌────▼──────────────────────────────┐
    │ php artisan make:migration         │
    │ add_something_to_table             │
    └────┬───────────────────────────────┘
         │
    ┌────▼──────────────────────────────┐
    │ Archivo creado:                    │
    │ 2026_02_13_000008_add_...php       │
    │                                    │
    │ namespace Database\Migrations;     │
    │ use Illuminate\Database\Schema... │
    │                                    │
    │ class AddColumnTable extends ...  │
    │ {                                 │
    │   public function up() {          │
    │     Schema::table('table', fn()..│
    │       $table->string('campo');    │
    │     });                           │
    │   }                               │
    │                                   │
    │   public function down() {        │
    │     Schema::table('table', fn()..│
    │       $table->dropColumn('campo')│
    │     });                           │
    │   }                               │
    │ }                                 │
    └────┬──────────────────────────────┘
         │
    ┌────▼──────────────────────────────┐
    │ php artisan migrate --step         │
    │                                    │
    │ Laravel:                           │
    │ 1. Conecta a BD                   │
    │ 2. Obtiene migrations ejecutadas  │
    │ 3. Compara con archivos nuevos    │
    │ 4. Ejecuta up() de nuevas         │
    │ 5. Guarda en tabla "migrations"   │
    └────┬──────────────────────────────┘
         │
    ┌────▼──────────────────────────────┐
    │ Resultado:                         │
    │ 2026_02_13_000008_add_... [Ran]   │
    │                                    │
    │ BD modificada correctamente        │
    └────┬──────────────────────────────┘
         │ En producción luego:
    ┌────▼──────────────────────────────┐
    │ php artisan migrate:rollback      │
    │                                    │
    │ Ejecuta down() en orden inverso   │
    │ Revierte cambios                  │
    │                                    │
    │ (NO HACER SIN BACKUP)             │
    └─────────────────────────────────────┘
```

---

**Versión:** 1.0.0 | **Última actualización:** 12 Feb 2026
