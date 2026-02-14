# 🆘 TROUBLESHOOTING & FAQ

## Problemas Comunes y Soluciones

---

## ❌ "Route [panel.mantenimientos.categorias.index] not defined"

### 🔍 Causa Probable
Estás referenciando una ruta que no existe en `routes/web.php`

### ✅ Solución
1. Verifica que la ruta exista:
```bash
php artisan route:list | grep categorias
```

2. Debería mostrar:
```
GET|HEAD app/panel/mantenimientos/categorias
```

3. Si no existe, revisa `routes/web.php` y asegúrate que incluya:
```php
Route::prefix('panel/mantenimientos')->middleware([...])->group(function () {
    Route::resource('categorias', CategoriaController::class);
});
```

4. Limpia caché:
```bash
php artisan route:cache --clear
php artisan cache:clear
```

---

## ❌ "SQLSTATE[42S02]: Table or view not found"

### 🔍 Causa Probable
Una o más migraciones no se ejecutaron

### ✅ Solución
```bash
# Ver estado
php artisan migrate:status

# Ejecutar una por una
php artisan migrate --step

# O todas de una vez
php artisan migrate
```

### Si persiste:
```bash
# Fresh start (⚠️ BORRA TODO)
php artisan migrate:fresh

# Con seeds
php artisan migrate:fresh --seed
```

### Debug en Tinker
```bash
php artisan tinker

# Verificar tabla existe
>>> DB::select('SHOW TABLES LIKE "dynamic%"');
// Debe mostrar: dynamic_attribute_schemas, dynamic_attribute_translations

exit
```

---

## ❌ "SQLSTATE[HY000]: General error: 2014"

### 🔍 Causa Probable
Conexión MySQL perdida o Tinker sigue abierto

### ✅ Solución
```bash
# Si Tinker está abierto
exit

# Limpia conexiones
php artisan db:wipe

# Reinicia MySQL (depende del entorno)
# Docker:
docker restart mysql

# O reconecta manualmente
php artisan cache:clear
```

---

## ❌ "Class not found: BelongsToSucursal"

### 🔍 Causa Probable
El trait no está importado en el modelo

### ✅ Solución
En tu modelo, agrega:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSucursal;  // ← AGREGAR ESTO

class MiModelo extends Model {
    use BelongsToSucursal;  // ← Y ESTO
}
```

---

## ❌ "Method forCurrentUser does not exist"

### 🔍 Causa Probable
El modelo no tiene el trait o no fue aplicado correctamente

### ✅ Solución
1. Verifica que el modelo use el trait:
```php
class Repuesto extends Model {
    use BelongsToSucursal; // ← Debe estar aquí
}
```

2. El trait debe tener los scopes correctos:
```bash
cat app/Traits/BelongsToSucursal.php | grep "public function scope"
```

3. Debería mostrar:
```
public function scopeForCurrentUser()
public function scopeForSucursal()
```

4. Si faltan, copiar desde documentación anterior

---

## ❌ "Atributos no se validan"

### 🔍 Causa Probable
`Repuesto::validateAttributes()` no se llama o el schema es incorrecto

### ✅ Solución
1. En `RepuestoController@store()` asegúrate de:
```php
public function store(Request $request) {
    // Validar atributos dinámicos
    $validated = Repuesto::validateAttributes(
        $request->categoria_id,
        $request->atributos ?? []
    );
    
    if (!$validated['valid']) {
        return response()->json(['errors' => $validated['errors']], 422);
    }
    
    // Continuar guardando
    Repuesto::create($request->validated());
}
```

2. Verifica que el schema exista:
```bash
php artisan tinker

>>> App\Models\DynamicAttributeSchema::where('categoria_id', 5)->get()
// Debe mostrar al menos una fila

>>> exit
```

3. Si no hay schema, créalo manualmente o desde la UI:
- Ve a `/panel/mantenimientos/configurar-atributos`
- Selecciona categoría
- Agrega atributo

---

## ❌ "403 Forbidden - No tienes acceso a esta sucursal"

### 🔍 Causa Probable
El middleware `VerifySucursalAccess` bloqueó tu acceso

### ✅ Solución
1. Verifica tu sucursal actual:
```php
echo session('sucursal_id');          // ¿Qué sucursal estoy usando?
echo auth()->user()->sucursal_id;     // ¿Cuál es mi sucursal?
echo auth()->user()->sucursales;      // ¿A cuáles tengo acceso?
```

2. Si `session('sucursal_id')` es diferente a `auth()->user()->sucursales`:
   - El middleware no te autorizó
   - Contacta admin para agregar acceso a esa sucursal

3. Test en URL:
```bash
# Sin parámetro (usa sucursal por defecto)
GET /panel/repuestos

# Con parámetro (si tienes acceso)
GET /panel/repuestos?sucursal_id=1

# Sin acceso (403)
GET /panel/repuestos?sucursal_id=999
```

---

## ❌ "Columns not visible in Form (Atributos dinámicos no aparecen)"

### 🔍 Causa Probable
JavaScript no se ejecutó o la vista Blade tiene error

### ✅ Solución
1. Verifica que exista la vista:
```bash
ls -la resources/views/panel/mantenimientos/atributos_dinamicos/
# Debe mostrar: index.blade.php
```

2. Abre con developer tools en browser (F12):
   - Networks tab → Verifica que cargue
   - Console tab → ¿Hay errores JavaScript?

3. Verifica que Alpine.js o vanilla JS esté en la página:
```bash
# En vista, debería tener:
<script>
    // JavaScript para manejar formulario
</script>
```

4. Prueba recargando después de `php artisan view:clear`

---

## ❌ "JSON in Database is Corrupted"

### 🔍 Causa Probable
Guardaste datos JSON inválido en columna `atributos`

### ✅ Solución
1. Ver datos corruptos:
```bash
php artisan tinker

>>> $r = App\Models\Repuesto::find(1)
>>> dd($r->atributos) // Ver qué contiene

// Si ves algo como: "{\"rin\": \"17\"" (string en lugar de array)
// Está corrupto

>>> exit
```

2. Arreglar manualmente:
```sql
-- Ver registro corrupto
SELECT id, atributos FROM repuestos WHERE id = 1;

-- Arreglar si es string:
UPDATE repuestos SET atributos = JSON_OBJECT() WHERE id = 1;

-- O restaurar desde backup
```

3. Prevenir en código:
```php
// Asegurar que es array antes de guardar
$repuesto->atributos = is_array($request->atributos) 
    ? $request->atributos 
    : json_decode($request->atributos, true) ?? [];
```

---

## ❌ "Pagination Not Working on Reports"

### 🔍 Causa Probable
`paginate()` no se llama en controller

### ✅ Solución
En `ReporteController`:
```php
public function inventarioPorSucursal(Request $request) {
    $repuestos = Repuesto::forCurrentUser()
        ->with('categoria', 'categoria.attributeSchemas')
        ->paginate(20); // ← IMPORTANTE: paginate(20)
    
    return view('panel.reportes.inventario-por-sucursal', [
        'repuestos' => $repuestos,
    ]);
}
```

Luego en Blade:
```blade
<!-- Tabla aquí -->

<!-- Links de paginación -->
{{ $repuestos->links() }}
```

---

## ❌ "API Returns 500 Error"

### 🔍 Causa Probable
Error en lógica de controller, database o modelo

### ✅ Solución
1. Ver logs:
```bash
tail -f storage/logs/laravel.log

# O especificar fecha:
tail -f storage/logs/laravel-2026-02-13.log
```

2. Habilitar debug mode:
```bash
# .env
APP_DEBUG=true
```

3. Prueba en Tinker:
```bash
php artisan tinker

# Replica el query que hace la API
>>> $r = App\Models\Repuesto::forCurrentUser()->first()
// Si da error aquí, el problema es el query/scope

>>> exit
```

4. Test simple con curl:
```bash
curl -H "Authorization: Bearer TOKEN" \
     http://localhost/api/repuestos
```

---

## ❌ "PDF Export says 'Library not found'"

### 🔍 Causa Probable
Falta instalar librería PDF (ej: DomPDF)

### ✅ Solución
```bash
composer require barryvdh/laravel-dompdf

php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

Luego en controller:
```php
use Barryvdh\DomPDF\Facade\Pdf;

public function exportInventarioPDF(Request $request) {
    $repuestos = Repuesto::forCurrentUser()->get();
    
    $pdf = Pdf::loadView('path.to.view', [
        'repuestos' => $repuestos
    ]);
    
    return $pdf->download('inventario.pdf');
}
```

---

## ❌ "Middleware not blocking requests"

### 🔍 Causa Probable
Middleware no está registrado en `bootstrap/app.php`

### ✅ Solución
1. Verifica que esté en `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'verify_sucursal' => \App\Http\Middleware\VerifySucursalAccess::class,
        'set_current_sucursal' => \App\Http\Middleware\SetCurrentSucursal::class,
    ]);
})
```

2. Verifica que la ruta use el middleware:
```php
Route::middleware(['verify_sucursal', 'set_current_sucursal'])->group(function () {
    Route::get('/panel/repuestos', [RepuestoController::class, 'index']);
});
```

3. Test:
```bash
php artisan route:list | grep verify
# Debe mostrar middleware en la columna
```

---

## ❌ "Translation Not Working"

### 🔍 Causa Probable
Método `getLabel()` no existe o está mal implementado

### ✅ Solución
En `DynamicAttributeSchema` debe existir:
```php
public function getLabel($locale = null) {
    $locale = $locale ?? app()->getLocale();
    
    $translation = $this->translations()
        ->where('locale', $locale)
        ->first();
    
    return $translation?->label ?? $this->attribute_name;
}
```

Uso en Blade:
```blade
{{ $schema->getLabel() }}           <!-- Usa locale actual -->
{{ $schema->getLabel('en') }}       <!-- Inglés -->
{{ $schema->getLabel('es') }}       <!-- Español -->
```

---

## ❌ "Timestamp fields not updating"

### 🔍 Causa Probable
Modelo no tiene `timestamps` habilitados

### ✅ Solución
En modelo:
```php
class DynamicAttributeSchema extends Model {
    public $timestamps = true;  // ← Asegurar está true
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}
```

---

## ⚠️ PREGUNTAS FRECUENTES (FAQ)

### P1: ¿Cuáles sucursales puede ver un usuario?
**R:** Las asignadas en la tabla `user_sucursal`. Admin debe:
1. Ir a Usuarios
2. Asignar sucursales al usuario
3. Usuario solo verá esas sucursales

### P2: ¿Puedo cambiar de sucursal sin desloguearme?
**R:** Sí. Usa: `?sucursal_id=X` en cualquier URL
```bash
GET /panel/repuestos?sucursal_id=2
# Cambia sesión a sucursal 2
```

### P3: ¿Qué pasa si elimino un schema?
**R:** Los productos no se rompen. El JSON sigue guardado en `repuestos.atributos`. Simplemente no podrás crear nuevos productos sin ese schema.

### P4: ¿Puedo atrás JSON en time?
**R:** Usa MySQL WHERE JSON:
```php
$repuestos = Repuesto::forCurrentUser()
    ->whereJsonContains('atributos->viscosidad', '15W40')
    ->get();
```

### P5: ¿Qué sucede si agrego una columna físicamente?
**R:** 
1. Pierde automigración (no se actualiza en otros deploys)
2. Mejor: Crea una migración:
```bash
php artisan make:migration add_xxx_to_yyy_table
```

### P6: ¿Puedo desactivar un atributo sin eliminarlo?
**R:** Sí, agrega columna `is_active`:
1. Crea migración:
```bash
php artisan make:migration add_is_active_to_dynamic_attribute_schemas
```
2. Migration:
```php
Schema::table('dynamic_attribute_schemas', function (Blueprint $table) {
    $table->boolean('is_active')->default(true);
});
```
3. Usa en query:
```php
->where('is_active', true)
```

### P7: ¿Cómo limito atributos por plan?
**R:** Agrega validation en schema creation:
```php
$count = DynamicAttributeSchema::forCurrentUser()->count();
$limit = auth()->user()->plan()->max_attributes; // ej: 10

if ($count >= $limit) {
    throw new Exception("Limit alcanzado");
}
```

### P8: ¿Puedo hacer búsqueda full-text?
**R:** Sí con índice FULLTEXT:
```sql
ALTER TABLE repuestos ADD FULLTEXT INDEX ft_nombre (nombre);
```

Luego:
```php
->whereRaw('MATCH(nombre) AGAINST(? IN BOOLEAN MODE)', [$search])
```

### P9: ¿Versionamiento de cambios?
**R:** Implementa audits:
```bash
composer require spatie/laravel-activitylog
php artisan make:activity-log-model
```

### P10: ¿API por sucursal?
**R:** Sí, usa mismo middleware:
```php
Route::middleware('api')->middleware('set_current_sucursal')->group(function () {
    Route::get('/api/repuestos', ...)
});
```

---

## 🔧 ÚTILES COMANDOS PARA DEBUG

```bash
# Ver migraciones pendientes
php artisan migrate:status

# Ejecutar con output
php artisan migrate --verbose

# Ver últimas líneas de log
tail -n 50 storage/logs/laravel.log

# Limpiar caché completamente
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Debug en Tinker
php artisan tinker --execute="dd(Illuminate\Support\Collection::all())"

# Ejecutar comando con timeout
timeout 30 php artisan migrate

# Ver listeners activos
php artisan event:list

# Verificar mailables
php artisan make:mail --list
```

---

## 📞 CUANDO CONTACTAR A SOPORTE

Si después de revisar esto aún tienes problemas:

1. **Copia la información:**
   ```bash
   php artisan --version
   mysql --version
   composer --version
   ```

2. **Ver último error:**
   ```bash
   tail -n 100 storage/logs/laravel.log > error.txt
   ```

3. **Aportar:**
   - Error exacto
   - Pasos para reproducir
   - Comandos ejecutados
   - Versiones (arriba)
   - Screenshot si aplica

---

**Última actualización:** 12 Feb 2026
**Versión:** 1.0.0
