<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\DashboardController as UserDashboard;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\ConsultantBrowseController;
use App\Http\Controllers\User\ScheduleBrowseController;
use App\Http\Controllers\User\FavoriteController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\Consultant\DashboardController as ConsultantDashboard;
use App\Http\Controllers\Consultant\ScheduleController;
use App\Http\Controllers\Consultant\BookingManageController;
use App\Http\Controllers\Consultant\ProfileController as ConsultantProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserManageController;
use App\Http\Controllers\Admin\ConsultantStatsController;
use App\Http\Controllers\Admin\UserStatsController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\ChatworkController as AdminChatworkController;
use App\Http\Controllers\Admin\GuestEmailController as AdminGuestEmailController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\GoogleAuthController;
use App\Http\Controllers\Guest\ConsultationController;
use App\Http\Controllers\Api\ChatworkMemberController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest consultation routes (no auth required)
Route::prefix('consultation')->name('consultation.')->group(function () {
    Route::get('/', [ConsultationController::class, 'index'])->name('index');
    Route::get('/book/{schedule}', [ConsultationController::class, 'create'])->name('create');
    Route::post('/book', [ConsultationController::class, 'store'])->name('store');
    Route::get('/complete/{booking}', [ConsultationController::class, 'complete'])->name('complete');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Google OAuth callback (needs auth + admin, but outside prefix since redirect_uri is fixed)
Route::get('/google/callback', [GoogleAuthController::class, 'callback'])
    ->middleware(['auth', 'role:admin'])
    ->name('google.callback');

// Chatwork API (authenticated users)
Route::middleware('auth')->get('/api/chatwork/members/{roomId}', [ChatworkMemberController::class, 'index'])->name('api.chatwork.members');

// User routes
Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboard::class, 'index'])->name('dashboard');

    // Available schedules
    Route::get('/schedules', [ScheduleBrowseController::class, 'index'])->name('schedules.index');

    // Consultant browsing
    Route::get('/consultants', [ConsultantBrowseController::class, 'index'])->name('consultants.index');
    Route::get('/consultants/{consultant}', [ConsultantBrowseController::class, 'show'])->name('consultants.show');

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create/{schedule}', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Reviews
    Route::post('/bookings/{booking}/review', [ReviewController::class, 'store'])->name('reviews.store');

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{consultant}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    // Profile
    Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.password');
});

// Consultant routes
Route::middleware(['auth', 'role:consultant'])->prefix('consultant')->name('consultant.')->group(function () {
    Route::get('/dashboard', [ConsultantDashboard::class, 'index'])->name('dashboard');

    // Schedules
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::post('/schedules/bulk', [ScheduleController::class, 'bulkStore'])->name('schedules.bulk');
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

    // Booking management
    Route::get('/bookings', [BookingManageController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/approve', [BookingManageController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [BookingManageController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{booking}/complete', [BookingManageController::class, 'complete'])->name('bookings.complete');
    Route::post('/bookings/{booking}/cancel', [BookingManageController::class, 'cancel'])->name('bookings.cancel');

    // Profile
    Route::get('/profile', [ConsultantProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ConsultantProfileController::class, 'update'])->name('profile.update');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // User management
    Route::get('/users', [UserManageController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserManageController::class, 'create'])->name('users.create');
    Route::post('/users', [UserManageController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserManageController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserManageController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle-active', [UserManageController::class, 'toggleActive'])->name('users.toggle-active');
    Route::post('/users/{user}/chatwork', [AdminChatworkController::class, 'send'])->name('users.chatwork.send');

    // Bookings
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/approve', [AdminBookingController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [AdminBookingController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/guest-email', [AdminGuestEmailController::class, 'send'])->name('bookings.guest-email.send');

    // Available schedules
    Route::get('/schedules', [AdminScheduleController::class, 'index'])->name('schedules.index');

    // Consultant stats
    Route::get('/stats', [ConsultantStatsController::class, 'index'])->name('stats.index');
    Route::get('/stats/{consultant}', [ConsultantStatsController::class, 'show'])->name('stats.show');

    // User stats
    Route::get('/user-stats', [UserStatsController::class, 'index'])->name('user-stats.index');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Google Calendar OAuth
    Route::get('/google/auth', [GoogleAuthController::class, 'redirect'])->name('google.auth');
    Route::post('/google/disconnect', [GoogleAuthController::class, 'disconnect'])->name('google.disconnect');
});
