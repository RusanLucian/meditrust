<?php

use App\Controllers\AdminController;
use App\Controllers\AppointmentController;
use App\Controllers\AuthController;
use App\Controllers\DoctorController;
use App\Controllers\HomeController;
use App\Controllers\ProfileController;
use App\Controllers\ReviewController;
use App\Core\Router;

// ============================================================
// RUTE PUBLICE
// ============================================================
Router::get('/', [HomeController::class, 'index'])->name('home');

// ============================================================
// AUTENTIFICARE & GUEST (Etapa 2)
// ============================================================
Router::group(['middleware' => ['guest']], function () {
    Router::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Router::post('/login', [AuthController::class, 'login']);
    Router::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Router::post('/register', [AuthController::class, 'register']);
});

Router::get('/logout', [AuthController::class, 'logout'])->name('logout');

// ============================================================
// RUTE UTILIZATORI AUTENTIFICAȚI (Etapa 2)
// ============================================================
Router::group(['middleware' => ['auth']], function () {
    Router::get('/dashboard', [ProfileController::class, 'dashboard'])->name('dashboard');
    Router::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Router::post('/profile', [ProfileController::class, 'update']);
});

// ============================================================
// MEDICI & RECENZII (Etapa 3)
// ============================================================
Router::get('/medici', [DoctorController::class, 'index'])->name('doctors.index');
Router::get('/medici/{id}', [DoctorController::class, 'show'])->name('doctors.show');
Router::post('/medici/{id}/reviews/delete', [DoctorController::class, 'deleteReview'])->name('doctors.reviews.delete');

Router::group(['middleware' => ['patient']], function () {
    Router::get('/medici/{id}/review', [ReviewController::class, 'showAdd'])->name('reviews.add');
    Router::post('/medici/{id}/review', [ReviewController::class, 'store'])->name('reviews.store');
});

// ============================================================
// PROGRAMĂRI PACIENT & MEDIC (Etapa 4)
// ============================================================
Router::group(['middleware' => ['patient']], function () {
    Router::get('/programare', [AppointmentController::class, 'showBook'])->name('appointments.book');
    Router::post('/programare', [AppointmentController::class, 'book'])->name('appointments.store');
    Router::get('/programarile-mele', [AppointmentController::class, 'patientAppointments'])->name('patient.appointments');
    Router::post('/programarile-mele/{id}/anuleaza', [AppointmentController::class, 'cancelByPatient'])->name('patient.appointments.cancel');
});

Router::group(['middleware' => ['doctor']], function () {
    Router::get('/programari-doctor', [AppointmentController::class, 'doctorAppointments'])->name('doctor.appointments');
    Router::post('/programari-doctor/{id}/status', [AppointmentController::class, 'updateStatus'])->name('doctor.appointments.status');
});

// ============================================================
// RUTE ADMIN - PROTEJATE CU MIDDLEWARE auth=admin (Etapa 5)
// ============================================================
Router::group(['prefix' => '/admin', 'middleware' => ['auth=admin']], function () {
    // Dashboard
    Router::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Users
    Router::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Router::get('/users/create', [AdminController::class, 'showAddUser'])->name('admin.users.create');
    Router::post('/users/create', [AdminController::class, 'addUser'])->name('admin.users.store');
    Router::get('/users/{id}/edit', [AdminController::class, 'showEditUser'])->name('admin.users.edit');
    Router::post('/users/{id}/edit', [AdminController::class, 'editUser'])->name('admin.users.update');
    Router::post('/users/{id}/delete', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

    // Doctors
    Router::get('/doctors', [AdminController::class, 'doctors'])->name('admin.doctors');
    Router::post('/doctors/{id}/delete', [AdminController::class, 'deleteDoctor'])->name('admin.doctors.delete');

    // Appointments
    Router::get('/appointments', [AdminController::class, 'appointments'])->name('admin.appointments');
    Router::post('/appointments/{id}/status', [AdminController::class, 'updateAppointmentStatus'])->name('admin.appointments.status');

    // Settings
    Router::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Router::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
});

// ============================================================
// REDIRECȚIONĂRI COMPATIBILITATE URL-URI LEGACY (.php)
// ============================================================
Router::get('/auth/login.php', fn() => redirect(route('login'), 301));
Router::get('/auth/register.php', fn() => redirect(route('register'), 301));
Router::get('/auth/dashboard.php', fn() => redirect(route('dashboard'), 301));
Router::get('/auth/profile.php', fn() => redirect(route('profile'), 301));
Router::get('/auth/logout.php', fn() => redirect(route('logout'), 301));

Router::get('/medici/lista.php', fn() => redirect(route('doctors.index'), 301));
Router::get('/medici/detalii.php', function ($req) {
    $id = (int)$req->input('id', 0);
    return $id > 0 ? redirect(route('doctors.show', ['id' => $id]), 301) : redirect(route('doctors.index'), 301);
});
Router::get('/medici/appointments.php', fn() => redirect(route('doctor.appointments'), 301));

Router::get('/pacient/book.php', function ($req) {
    $docId = (int)$req->input('doctor_id', 0);
    return $docId > 0
        ? redirect(route('appointments.book') . '?doctor_id=' . $docId, 301)
        : redirect(route('appointments.book'), 301);
});
Router::get('/pacient/my-appointments.php', fn() => redirect(route('patient.appointments'), 301));

Router::get('/reviews/add.php', function ($req) {
    $docId = (int)$req->input('doctor_id', 0);
    return $docId > 0
        ? redirect(route('reviews.add', ['id' => $docId]), 301)
        : redirect(route('doctors.index'), 301);
});

Router::get('/admin/dashboard.php', fn() => redirect(route('admin.dashboard'), 301));
Router::get('/admin/users.php', fn() => redirect(route('admin.users'), 301));
Router::get('/admin/doctors.php', fn() => redirect(route('admin.doctors'), 301));
Router::get('/admin/appointments.php', fn() => redirect(route('admin.appointments'), 301));
Router::get('/admin/settings.php', fn() => redirect(route('admin.settings'), 301));
Router::get('/admin/login.php', fn() => redirect(route('login'), 301));
Router::get('/admin/logout.php', fn() => redirect(route('logout'), 301));
