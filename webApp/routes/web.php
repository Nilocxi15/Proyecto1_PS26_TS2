<?php

use App\Http\Controllers\CitizenHomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\CitizenReportController;
use App\Http\Controllers\Coordinator\IncidentManagementController;
use App\Http\Controllers\Coordinator\CollectionProcessController;
use App\Http\Controllers\Coordinator\RouteManagementController;
use App\Http\Controllers\Coordinator\TruckManagementController;
use App\Http\Controllers\Operator\ContainerManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\ReportsStatsController;
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
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{usuario}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{usuario}/status', [UserManagementController::class, 'updateStatus'])->name('admin.users.update-status');
    Route::delete('/admin/users/{usuario}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');

    // ---------------------------------------------------------------------------
    // Rutas para la página de gestión de reportes (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::get('/admin/reports', [ReportsStatsController::class, 'index'])->name('admin.reports');
    Route::get('/admin/reports/export', [ReportsStatsController::class, 'exportCsv'])->name('admin.reports.export');

    // ---------------------------------------------------------------------------
    // Rutas para la página de configuración del sistema (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::get('/admin/system-settings', [SystemSettingsController::class, 'index'])->name('admin.system-settings');

    // Roles
    Route::post('/admin/system-settings/roles', [SystemSettingsController::class, 'storeRole'])->name('admin.settings.roles.store');
    Route::put('/admin/system-settings/roles/{role}', [SystemSettingsController::class, 'updateRole'])->name('admin.settings.roles.update');
    Route::delete('/admin/system-settings/roles/{role}', [SystemSettingsController::class, 'destroyRole'])->name('admin.settings.roles.destroy');

    // Tipos de material
    Route::post('/admin/system-settings/materiales', [SystemSettingsController::class, 'storeMaterial'])->name('admin.settings.materiales.store');
    Route::put('/admin/system-settings/materiales/{material}', [SystemSettingsController::class, 'updateMaterial'])->name('admin.settings.materiales.update');
    Route::delete('/admin/system-settings/materiales/{material}', [SystemSettingsController::class, 'destroyMaterial'])->name('admin.settings.materiales.destroy');

    // Puntos verdes
    Route::post('/admin/system-settings/puntos-verdes', [SystemSettingsController::class, 'storePuntoVerde'])->name('admin.settings.puntos.store');
    Route::put('/admin/system-settings/puntos-verdes/{puntoVerde}', [SystemSettingsController::class, 'updatePuntoVerde'])->name('admin.settings.puntos.update');
    Route::delete('/admin/system-settings/puntos-verdes/{puntoVerde}', [SystemSettingsController::class, 'destroyPuntoVerde'])->name('admin.settings.puntos.destroy');

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
    Route::get('/auditor/reports', [ReportsStatsController::class, 'auditorIndex'])->name('auditor.reports');
    Route::get('/auditor/reports/export', [ReportsStatsController::class, 'exportCsv'])->name('auditor.reports.export');

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
    Route::get('/coordinator/trucks', [TruckManagementController::class, 'index'])
        ->name('coordinator.trucks');

    Route::post('/coordinator/trucks', [TruckManagementController::class, 'store'])
        ->name('coordinator.trucks.store');

    Route::post('/coordinator/trucks/assignments', [TruckManagementController::class, 'assignRoute'])
        ->name('coordinator.trucks.assign-route');

    Route::put('/coordinator/trucks/{camion}/driver', [TruckManagementController::class, 'updateDriver'])
        ->name('coordinator.trucks.update-driver');

    Route::put('/coordinator/trucks/{camion}/state', [TruckManagementController::class, 'updateState'])
        ->name('coordinator.trucks.update-state');

    Route::delete('/coordinator/trucks/{camion}', [TruckManagementController::class, 'destroy'])
        ->name('coordinator.trucks.delete');

    // ---------------------------------------------------------------------------
    // Rutas para la gestión de proceso de recolección
    Route::get('/coordinator/collection-process', [CollectionProcessController::class, 'index'])
        ->name('coordinator.collection-process');

    Route::put('/coordinator/collection-process/{programacion}/state', [CollectionProcessController::class, 'updateState'])
        ->name('coordinator.collection-process.update-state');

    Route::put('/coordinator/collection-process/{programacion}/start-time', [CollectionProcessController::class, 'setStartTime'])
        ->name('coordinator.collection-process.set-start-time');

    Route::put('/coordinator/collection-process/{programacion}/end-time', [CollectionProcessController::class, 'setEndTime'])
        ->name('coordinator.collection-process.set-end-time');

    Route::put('/coordinator/collection-process/{programacion}/waste', [CollectionProcessController::class, 'setCollectedWaste'])
        ->name('coordinator.collection-process.set-waste');

    Route::put('/coordinator/collection-process/{programacion}/observations', [CollectionProcessController::class, 'setObservations'])
        ->name('coordinator.collection-process.set-observations');

    Route::post('/coordinator/collection-process/{programacion}/incidents', [CollectionProcessController::class, 'storeIncident'])
        ->name('coordinator.collection-process.store-incident');
});

// ---------------------------------------------------------------------------
// Rutas para páginas de error personalizadas
// ---------------------------------------------------------------------------
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

