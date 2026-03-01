<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
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
use App\Http\Controllers\Consultant\GoogleAuthController as ConsultantGoogleAuthController;
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

// Public storage file serving via /media/ path to avoid symlink 403 on Xserver
// (public/storage symlink causes Apache 403 regardless of .htaccess settings)
Route::get('/media/{path}', function ($path) {
    // Prevent path traversal
    $path = str_replace('..', '', $path);
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404);
    }

    // Detect MIME type explicitly for reliable image serving
    $mimeType = null;
    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $mimeMap = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'pdf' => 'application/pdf',
    ];

    if (isset($mimeMap[$extension])) {
        $mimeType = $mimeMap[$extension];
    } elseif (function_exists('mime_content_type')) {
        $mimeType = mime_content_type($fullPath);
    }

    $headers = [
        'Cache-Control' => 'public, max-age=86400',
    ];
    if ($mimeType) {
        $headers['Content-Type'] = $mimeType;
    }

    return response()->file($fullPath, $headers);
})->where('path', '.*');

// Public routes
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isConsultant()) {
            return redirect()->route('consultant.dashboard');
        }
        return redirect()->route('user.dashboard');
    }
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

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Google OAuth callback (shared by admin and consultant, outside prefix since redirect_uri is fixed)
Route::get('/google/callback', [GoogleAuthController::class, 'callback'])
    ->middleware(['auth'])
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
    Route::post('/bookings/{booking}/complete', [BookingManageController::class, 'complete'])->name('bookings.complete');
    Route::post('/bookings/{booking}/cancel', [BookingManageController::class, 'cancel'])->name('bookings.cancel');
    Route::put('/bookings/{booking}/consultation-record', [BookingManageController::class, 'updateConsultationRecord'])->name('bookings.consultation-record.update');
    Route::put('/bookings/{booking}/user-notes', [BookingManageController::class, 'updateUserNotes'])->name('bookings.user-notes.update');
    Route::post('/bookings/{booking}/send-email', [BookingManageController::class, 'sendEmail'])->name('bookings.send-email');

    // Profile
    Route::get('/profile', [ConsultantProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ConsultantProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/photo', [ConsultantProfileController::class, 'deletePhoto'])->name('profile.photo.delete');

    // Google Calendar OAuth
    Route::get('/google/auth', [ConsultantGoogleAuthController::class, 'redirect'])->name('google.auth');
    Route::post('/google/disconnect', [ConsultantGoogleAuthController::class, 'disconnect'])->name('google.disconnect');
    Route::get('/google/calendars', [ConsultantGoogleAuthController::class, 'calendars'])->name('google.calendars');
    Route::put('/google/calendar', [ConsultantGoogleAuthController::class, 'updateCalendar'])->name('google.calendar.update');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // User management
    Route::get('/users', [UserManageController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserManageController::class, 'create'])->name('users.create');
    Route::get('/users/guest/{booking}/edit', [UserManageController::class, 'editGuest'])->name('users.guest.edit');
    Route::put('/users/guest/{booking}', [UserManageController::class, 'updateGuest'])->name('users.guest.update');
    Route::post('/users', [UserManageController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserManageController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserManageController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle-active', [UserManageController::class, 'toggleActive'])->name('users.toggle-active');
    Route::post('/users/{user}/chatwork', [AdminChatworkController::class, 'send'])->name('users.chatwork.send');

    // Bookings
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/export-csv', [AdminBookingController::class, 'exportCsv'])->name('bookings.export-csv');
    Route::get('/bookings/create', [AdminBookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [AdminBookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/schedules', [AdminBookingController::class, 'getSchedules'])->name('bookings.schedules');
    Route::post('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::put('/bookings/{booking}/consultation-record', [AdminBookingController::class, 'updateConsultationRecord'])->name('bookings.consultation-record.update');
    Route::put('/bookings/{booking}/notes', [AdminBookingController::class, 'updateNotes'])->name('bookings.notes.update');
    Route::post('/bookings/{booking}/guest-email', [AdminGuestEmailController::class, 'send'])->name('bookings.guest-email.send');

    // Available schedules
    Route::get('/schedules', [AdminScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [AdminScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [AdminScheduleController::class, 'store'])->name('schedules.store');

    // Consultant stats
    Route::get('/stats', [ConsultantStatsController::class, 'index'])->name('stats.index');
    Route::get('/stats/{consultant}', [ConsultantStatsController::class, 'show'])->name('stats.show');

    // User stats
    Route::get('/user-stats', [UserStatsController::class, 'index'])->name('user-stats.index');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/booking', [SettingController::class, 'updateBooking'])->name('settings.update.booking');
    Route::put('/settings/reminder', [SettingController::class, 'updateReminder'])->name('settings.update.reminder');
    Route::put('/settings/templates', [SettingController::class, 'updateTemplates'])->name('settings.update.templates');
    Route::put('/settings/integration', [SettingController::class, 'updateIntegration'])->name('settings.update.integration');

    // Google Calendar OAuth
    Route::get('/google/auth', [GoogleAuthController::class, 'redirect'])->name('google.auth');
    Route::post('/google/disconnect', [GoogleAuthController::class, 'disconnect'])->name('google.disconnect');
    Route::get('/google/calendars', [GoogleAuthController::class, 'calendars'])->name('google.calendars');
    Route::put('/google/calendar', [GoogleAuthController::class, 'updateCalendar'])->name('google.calendar.update');
});
