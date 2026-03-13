<?php

use App\Http\Controllers\CitizenHomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\CitizenReportController;
use App\Http\Controllers\Coordinator\IncidentManagementController;
use App\Http\Controllers\Coordinator\RouteManagementController;
use App\Http\Controllers\Operator\ContainerManagementController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Rutas de inicio de sesión
// ---------------------------------------------------------------------------
Route::get('/', [CitizenHomeController::class, 'publicHome'])->name('home-public');
Route::get('/login', [LoginController::class, 'mostrarFormulario'])->name('login');
Route::post('/login', [LoginController::class, 'iniciarSesion'])->name('login.store');
Route::post('/logout', [LoginController::class, 'cerrarSesion'])->name('logout');

// ---------------------------------------------------------------------------
// Rutas para registro de usuarios (Rol Ciudadano)
// ---------------------------------------------------------------------------
Route::get('/register', [RegistroController::class, 'mostrarFormulario'])->name('register');
Route::post('/register', [RegistroController::class, 'registrar'])->name('register.store');

// ---------------------------------------------------------------------------
// Ruta global para actualizar telefono de perfil (roles autenticados)
// ---------------------------------------------------------------------------
Route::middleware('role:1,2,3,4,5')
    ->post('/profile/change-phone', [PerfilController::class, 'cambiarTelefono'])
    ->name('profile.change-phone');

// ---------------------------------------------------------------------------
// Rutas para la página de inicio del ciudadano
// ---------------------------------------------------------------------------
Route::middleware('role:4')->group(function () {
    Route::get('/citizen/home', [CitizenHomeController::class, 'citizenHome'])
        ->name('home-citizen');

    // ---------------------------------------------------------------------------
    // Rutas para el perfil de los usuarios
    // ---------------------------------------------------------------------------
    Route::view('/citizen/profile', 'citizen.profile')->name('profile-citizen');

    // ---------------------------------------------------------------------------
    // Rutas para la página de reportes del ciudadano
    // ---------------------------------------------------------------------------
    Route::get('/citizen/reports', [CitizenReportController::class, 'index'])->name('report-citizen');
    Route::post('/citizen/reports', [CitizenReportController::class, 'store'])->name('report-citizen.store');

});

// ---------------------------------------------------------------------------
// Ruta pública de estadísticas
// ---------------------------------------------------------------------------
Route::view('/citizen/public-statistics', 'citizen.stats')->name('citizen.public-statistics');

// ---------------------------------------------------------------------------
// Rutas para la página de inicio del operador de punto verde
// ---------------------------------------------------------------------------
Route::middleware('role:3')->group(function () {
    Route::get('/operator/containers', [ContainerManagementController::class, 'index'])
        ->name('operator.containers');

    // ---------------------------------------------------------------------------
    // Rutas para el perfil de los usuarios (Rol operador de punto verde)
    // ---------------------------------------------------------------------------
    Route::view('/operator/profile', 'operator.profile')->name('profile-operator');

    Route::post('/operator/containers/{contenedor}/empty-request', [ContainerManagementController::class, 'storeEmptyRequest'])
        ->name('operator.containers.empty-request');

    Route::post('/operator/containers/{contenedor}/deliveries', [ContainerManagementController::class, 'storeDelivery'])
        ->name('operator.containers.deliveries.store');
});

// ---------------------------------------------------------------------------
// Rutas para la página de inicio del administrador
// ---------------------------------------------------------------------------
Route::middleware('role:1')->group(function () {
    Route::view('/admin/home', 'admin.home')->name('home-admin');

    // ---------------------------------------------------------------------------
    // Rutas para la página de gestión de usuarios (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::view('/admin/users', 'admin.users-management')->name('admin.users');

    // ---------------------------------------------------------------------------
    // Rutas para la página de gestión de reportes (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::view('/admin/reports', 'admin.reports-stats')->name('admin.reports');

    // ---------------------------------------------------------------------------
    // Rutas para la página de configuración del sistema (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::view('/admin/system-settings', 'admin.system-settings')->name('admin.system-settings');

    // ---------------------------------------------------------------------------
    // Rutas para ver perfil de usuario (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::view('/admin/user-profile', 'admin.profile')->name('admin.user-profile');
});

// ---------------------------------------------------------------------------
// Rutas para la página de inicio (Rol auditor)
// ---------------------------------------------------------------------------
Route::middleware('role:5')->group(function () {
    Route::view('/auditor/home', 'auditor.home')->name('home-auditor');

    // ---------------------------------------------------------------------------
    // Rutas para la página del perfil del auditor
    // ---------------------------------------------------------------------------
    Route::view('/auditor/profile', 'auditor.profile')->name('profile-auditor');
});

// ---------------------------------------------------------------------------
// Rutas para la página de inicio (Rol coordinador de rutas)
// ---------------------------------------------------------------------------
Route::middleware('role:2')->group(function () {
    Route::view('/coordinator/home', 'coordinator.home')->name('home-coordinator');

    // ---------------------------------------------------------------------------
    // Rutas para la página de incidentes del coordinador de rutas
    // ---------------------------------------------------------------------------
    Route::get('/coordinator/incidents', [IncidentManagementController::class, 'index'])
        ->name('coordinator.incidents');

    Route::post('/coordinator/incidents/{denuncia}/status', [IncidentManagementController::class, 'updateStatus'])
        ->name('coordinator.incidents.status.update');

    // ---------------------------------------------------------------------------
    // Rutas para la página de planificación de rutas del coordinador de rutas
    // ---------------------------------------------------------------------------
    Route::get('/coordinator/routes', [RouteManagementController::class, 'index'])
        ->name('coordinator.routes');

    Route::post('/coordinator/routes', [RouteManagementController::class, 'store'])
        ->name('coordinator.routes.store');

    Route::put('/coordinator/routes/{ruta}', [RouteManagementController::class, 'update'])
        ->name('coordinator.routes.update');

    Route::delete('/coordinator/routes/{ruta}', [RouteManagementController::class, 'destroy'])
        ->name('coordinator.routes.delete');

    Route::post('/coordinator/zones', [RouteManagementController::class, 'storeZone'])
        ->name('coordinator.zones.store');

    // ---------------------------------------------------------------------------
    // Rutas para la página de perfil del coordinador de rutas
    // ---------------------------------------------------------------------------
    Route::view('/coordinator/profile', 'coordinator.profile')->name('coordinator.profile');

    // ---------------------------------------------------------------------------
    // Rutas para la gestión de camiones (Rol coordinador de rutas)
    // ---------------------------------------------------------------------------
    Route::view('/coordinator/trucks', 'coordinator.trucks')->name('coordinator.trucks');

    // ---------------------------------------------------------------------------
    // Rutas para la gestión de proceso de recolección
    Route::view('/coordinator/collection-process', 'coordinator.collection-process')
        ->name('coordinator.collection-process');
});

// ---------------------------------------------------------------------------
// Rutas para páginas de error personalizadas
// ---------------------------------------------------------------------------
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

