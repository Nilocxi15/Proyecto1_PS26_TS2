<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegistroController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Rutas de inicio de sesión
// ---------------------------------------------------------------------------
Route::get('/', [LoginController::class, 'mostrarFormulario'])->name('login');
Route::post('/login', [LoginController::class, 'iniciarSesion'])->name('login.store');
Route::post('/logout', [LoginController::class, 'cerrarSesion'])->name('logout');

// ---------------------------------------------------------------------------
// Rutas para registro de usuarios (Rol Ciudadano)
// ---------------------------------------------------------------------------
Route::get('/register', [RegistroController::class, 'mostrarFormulario'])->name('register');
Route::post('/register', [RegistroController::class, 'registrar'])->name('register.store');

// ---------------------------------------------------------------------------
// Rutas para la página de inicio del ciudadano
// ---------------------------------------------------------------------------
Route::middleware('role:4')->group(function () {
    Route::get('/citizen/home', function () {
        return view('citizen.home');
    })->name('home-citizen');

    // ---------------------------------------------------------------------------
    // Rutas para el perfil de los usuarios
    // ---------------------------------------------------------------------------
    Route::get('/citizen/profile', function () {
        return view('citizen.profile');
    })->name('profile-citizen');

    // ---------------------------------------------------------------------------
    // Rutas para la página de reportes del ciudadano
    // ---------------------------------------------------------------------------
    Route::get('/citizen/reports', function () {
        return view('citizen.reports');
    })->name('report-citizen');

    // ---------------------------------------------------------------------------
    // Rutas para las estadísticas públicas (Rol ciudadano)
    // ---------------------------------------------------------------------------
    Route::get('/citizen/public-statistics', function () {
        return view('citizen.stats');
    })->name('citizen.public-statistics');
});

// ---------------------------------------------------------------------------
// Rutas para la página de inicio del operador de punto verde
// ---------------------------------------------------------------------------
Route::middleware('role:3')->group(function () {
    Route::get('/operator/home', function () {
        return view('operator.home');
    })->name('home-operator');

    // ---------------------------------------------------------------------------
    // Rutas para la página de gestión de contenedores (Rol operador de punto verde)
    // ---------------------------------------------------------------------------
    Route::get('/operator/containers', function () {
        return view('operator.container');
    })->name('operator.containers');

    // ---------------------------------------------------------------------------
    // Rutas para el perfil de los usuarios (Rol operador de punto verde)
    // ---------------------------------------------------------------------------
    Route::get('/operator/profile', function () {
        return view('operator.profile');
    })->name('profile-operator');
});

// ---------------------------------------------------------------------------
// Rutas para la página de inicio del administrador
// ---------------------------------------------------------------------------
Route::middleware('role:1')->group(function () {
    Route::get('/admin/home', function () {
        return view('admin.home');
    })->name('home-admin');

    // ---------------------------------------------------------------------------
    // Rutas para la página de gestión de usuarios (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::get('/admin/users', function () {
        return view('admin.users-management');
    })->name('admin.users');

    // ---------------------------------------------------------------------------
    // Rutas para la página de gestión de reportes (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::get('/admin/reports', function () {
        return view('admin.reports-stats');
    })->name('admin.reports');

    // ---------------------------------------------------------------------------
    // Rutas para la página de configuración del sistema (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::get('/admin/system-settings', function () {
        return view('admin.system-settings');
    })->name('admin.system-settings');

    // ---------------------------------------------------------------------------
    // Rutas para ver perfil de usuario (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::get('/admin/user-profile', function () {
        return view('admin.profile');
    })->name('admin.user-profile');
});

// ---------------------------------------------------------------------------
// Rutas para la página de inicio (Rol auditor)
// ---------------------------------------------------------------------------
Route::middleware('role:5')->group(function () {
    Route::get('/auditor/home', function () {
        return view('auditor.home');
    })->name('home-auditor');

    // ---------------------------------------------------------------------------
    // Rutas para la página del perfil del auditor
    // ---------------------------------------------------------------------------
    Route::get('/auditor/profile', function () {
        return view('auditor.profile');
    })->name('profile-auditor');
});

// ---------------------------------------------------------------------------
// Rutas para la página de inicio (Rol coordinador de rutas)
// ---------------------------------------------------------------------------
Route::middleware('role:2')->group(function () {
    Route::get('/coordinator/home', function () {
        return view('coordinator.home');
    })->name('home-coordinator');

    // ---------------------------------------------------------------------------
    // Rutas para la página de incidentes del coordinador de rutas
    // ---------------------------------------------------------------------------
    Route::get('/coordinator/incidents', function () {
        return view('coordinator.incidents');
    })->name('coordinator.incidents');

    // ---------------------------------------------------------------------------
    // Rutas para la página de planificación de rutas del coordinador de rutas
    // ---------------------------------------------------------------------------
    Route::get('/coordinator/routes', function () {
        return view('coordinator.routes');
    })->name('coordinator.routes');

    // ---------------------------------------------------------------------------
    // Rutas para la página de perfil del coordinador de rutas
    // ---------------------------------------------------------------------------
    Route::get('/coordinator/profile', function () {
        return view('coordinator.profile');
    })->name('coordinator.profile');

    // ---------------------------------------------------------------------------
    // Rutas para la gestión de camiones (Rol coordinador de rutas)
    // ---------------------------------------------------------------------------
    Route::get('/coordinator/trucks', function () {
        return view('coordinator.trucks');
    })->name('coordinator.trucks');
});