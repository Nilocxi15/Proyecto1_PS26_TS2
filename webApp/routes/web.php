<?php

use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Rutas de inicio de sesión
// ---------------------------------------------------------------------------
Route::get('/', function () {
    return view('welcome');
})->name('login');

// ---------------------------------------------------------------------------
// Rutas para registro de usuarios (Rol Ciudadano)
// ---------------------------------------------------------------------------
Route::get('/register', [App\Http\Controllers\RegistroController::class, 'mostrarFormulario'])->name('register');
Route::post('/register', [App\Http\Controllers\RegistroController::class, 'registrar'])->name('register.store');

// ---------------------------------------------------------------------------
// Rutas para la página de inicio del ciudadano
// ---------------------------------------------------------------------------
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

// ---------------------------------------------------------------------------
// Rutas para la página de inicio del operador de punto verde
// ---------------------------------------------------------------------------
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

// ---------------------------------------------------------------------------
// Rutas para la página de inicio del administrador
// ---------------------------------------------------------------------------
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