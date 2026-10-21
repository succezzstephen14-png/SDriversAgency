<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminVehicleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ServiceCustomerController;
use App\Http\Controllers\ServiceRequestAdminController;

Route::domain(config('admin.domain'))->group(function () {
    Route::get('/admin/login', [AuthController::class, 'adminLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.post');
    Route::get('/cms/login', [AuthController::class, 'cmsLoginForm'])->name('cms.login');
    Route::post('/cms/login', [AuthController::class, 'cmsLogin'])->name('cms.login.post');
    Route::get('/admin', fn () => auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('admin.login'))->name('admin.home');
});

// Public routes
Route::middleware('dashboard.only')->group(function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::post('/contact', [App\Http\Controllers\HomeController::class, 'contact'])->name('contact.store');
    Route::get('/privacy-policy', [App\Http\Controllers\HomeController::class, 'privacyPolicy'])->name('privacy.policy');
    Route::get('/terms-and-conditions', [App\Http\Controllers\HomeController::class, 'termsAndConditions'])->name('terms.conditions');
});

Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/request-a-driver', [ServiceCustomerController::class, 'registerForm'])->defaults('requestType', 'driver')->name('service.register.driver');
Route::get('/request-a-vehicle', [ServiceCustomerController::class, 'registerForm'])->defaults('requestType', 'vehicle')->name('service.register.vehicle');
Route::get('/request-services/register', [ServiceCustomerController::class, 'registerForm'])->name('service.register');
Route::post('/request-services/register', [ServiceCustomerController::class, 'register'])->name('service.register.post');
Route::get('/request-services/login', [AuthController::class, 'serviceCustomerLoginForm'])->name('service.login');
Route::post('/request-services/login', [AuthController::class, 'serviceCustomerLogin'])->name('service.login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/email/verify', [AuthController::class, 'verificationNotice'])
    ->middleware('auth')
    ->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'forgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'resetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::middleware(['auth', 'verified', 'service_customer'])->prefix('service-dashboard')->name('service.')->group(function () {
    Route::get('/', [ServiceCustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/requests', [ServiceCustomerController::class, 'requests'])->name('requests.index');
    Route::get('/requests/create/{type}', [ServiceCustomerController::class, 'createRequest'])
        ->whereIn('type', ['driver', 'vehicle'])
        ->name('requests.create');
    Route::post('/requests', [ServiceCustomerController::class, 'storeRequest'])->name('requests.store');
    Route::post('/requests/{serviceRequest}/pay', [ServiceCustomerController::class, 'pay'])
        ->whereNumber('serviceRequest')
        ->name('requests.pay');
    Route::get('/account', [ServiceCustomerController::class, 'profile'])->name('profile');
});

// Social Authentication Routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/apple', [AuthController::class, 'redirectToApple'])->name('auth.apple');
Route::get('/auth/apple/callback', [AuthController::class, 'handleAppleCallback']);

Route::get('/profile', [AuthController::class, 'profile'])->name('profile')->middleware(['auth', 'verified']);
Route::get('/profile/edit', [AuthController::class, 'profileEdit'])->name('profile.edit')->middleware(['auth', 'verified']);
Route::put('/profile', [AuthController::class, 'profileUpdate'])->name('profile.update')->middleware(['auth', 'verified']);
Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password.update')->middleware(['auth', 'verified']);
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard')->middleware(['auth', 'verified']);
Route::get('/onboarding', [AuthController::class, 'onboarding'])->name('onboarding')->middleware(['auth', 'verified']);

Route::middleware('dashboard.only')->group(function () {
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/search', [VehicleController::class, 'search'])->name('vehicles.search');
    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
    Route::get('/drivers/search', [DriverController::class, 'search'])->name('drivers.search');
    Route::get('/recruitments', [RecruitmentController::class, 'index'])->name('recruitments.index');
    Route::get('/recruitments/search', [RecruitmentController::class, 'search'])->name('recruitments.search');
    Route::get('/recruitments/request', [RecruitmentController::class, 'requestForm'])->name('recruitments.request-form');
    Route::post('/recruitments/request', [RecruitmentController::class, 'storeRequest'])->name('recruitments.request.store');
    Route::get('/book', [BookingController::class, 'index'])->name('book.index');
});

// Guest-accessible booking and application routes
Route::get('/book/{type}/{id}', [BookingController::class, 'showBookingPage'])
    ->where('type', 'driver|vehicle')
    ->name('booking.page');

Route::get('/guest/book/{type}/{id}', [BookingController::class, 'showBookingPage'])
    ->where('type', 'driver|vehicle')
    ->name('guest.booking.page');

Route::get('/apply/{role}/{recruitment}', [BookingController::class, 'showApplicationPage'])
    ->where('role', '[A-Za-z_-]+')
    ->name('application.page');

Route::get('/guest/apply/{role}/{recruitment}', [BookingController::class, 'showApplicationPage'])
    ->where('role', '[A-Za-z_-]+')
    ->name('guest.application.page');

Route::post('/apply/{role}/{recruitment}', [BookingController::class, 'storeApplication'])
    ->where('role', '[A-Za-z_-]+')
    ->name('application.store');

Route::post('/guest/apply/{role}/{recruitment}', [BookingController::class, 'storeApplication'])
    ->where('role', '[A-Za-z_-]+')
    ->name('guest.application.store');

Route::match(['get', 'post'], '/bookings/payment/initiate', [PaymentController::class, 'initiateBookingPayment'])
    ->name('booking.payment.initiate');

Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/cancelled', [PaymentController::class, 'paymentCancelled'])->name('payment.cancelled');
Route::post('/payment/webhook', [PaymentController::class, 'handlePaystackCallback'])->name('payment.webhook');

// Authenticated user routes
Route::middleware(['auth', 'verified', 'not_service_customer'])->group(function () {
    // User vehicles
    Route::get('/my-vehicles', [VehicleController::class, 'myVehicles'])->name('vehicles.my-vehicles');
    Route::get('/my-vehicles/pending', [VehicleController::class, 'myPendingVehicles'])->name('vehicles.pending-vehicles');
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

    // User driver profile
    Route::get('/drivers/profile', [DriverController::class, 'myProfile'])->name('drivers.profile');
    Route::get('/drivers/create', [DriverController::class, 'create'])->name('drivers.create');
    Route::post('/drivers', [DriverController::class, 'store'])->name('drivers.store');
    Route::get('/drivers/edit', [DriverController::class, 'edit'])->name('drivers.edit');
    Route::put('/drivers', [DriverController::class, 'update'])->name('drivers.update');
    Route::post('/drivers/{driver}/rate', [DriverController::class, 'rateDriver'])->name('drivers.rate');

    // Payment routes for driver, vehicle, and recruitment registration
    Route::match(['get', 'post'], '/drivers/payment/initiate', [PaymentController::class, 'initiateDriverPayment'])->name('driver.payment.initiate');
    Route::match(['get', 'post'], '/vehicles/payment/initiate', [PaymentController::class, 'initiateVehiclePayment'])->name('vehicle.payment.initiate');
    Route::match(['get', 'post'], '/recruitments/payment/initiate', [PaymentController::class, 'initiateRecruitmentPayment'])->name('recruitment.payment.initiate');

    // User notifications
    Route::get('/notifications', [AuthController::class, 'notifications'])->name('notifications.index');

    // User recruitment posts
    Route::get('/my-recruitments', [RecruitmentController::class, 'myPosts'])->name('recruitments.my-posts');
    Route::get('/recruitments/create', [RecruitmentController::class, 'create'])->name('recruitments.create');
    Route::post('/recruitments', [RecruitmentController::class, 'store'])->name('recruitments.store');
    Route::get('/recruitments/{recruitment}/edit', [RecruitmentController::class, 'edit'])->name('recruitments.edit');
    Route::put('/recruitments/{recruitment}', [RecruitmentController::class, 'update'])->name('recruitments.update');
    Route::delete('/recruitments/{recruitment}', [RecruitmentController::class, 'destroy'])->name('recruitments.destroy');

    // Booking routes
    Route::get('/bookings/my-bookings', [BookingController::class, 'myBookings'])->name('bookings.my-bookings');
    Route::get('/bookings/applications', [BookingController::class, 'myApplications'])->name('bookings.my-applications');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');

    // Notifications
    Route::post('/notifications/{notification}/read', [AuthController::class, 'markNotificationRead'])->name('notifications.read');
    
    // Book driver
    Route::get('/drivers/{driver}/book', [BookingController::class, 'bookDriverForm'])->name('drivers.book-form');
    Route::post('/drivers/{driver}/book', [BookingController::class, 'storeDriverBooking'])->name('drivers.book');
    
    // Book vehicle
    Route::get('/vehicles/{vehicle}/book', [BookingController::class, 'bookVehicleForm'])->name('vehicles.book-form');
    Route::post('/vehicles/{vehicle}/book', [BookingController::class, 'storeVehicleBooking'])->name('vehicles.book');
    
    // Apply for recruitment
    Route::get('/recruitments/{recruitment}/apply', [BookingController::class, 'bookRecruitmentForm'])->name('recruitments.apply-form');
    Route::post('/recruitments/{recruitment}/apply', [BookingController::class, 'storeRecruitmentBooking'])->name('recruitments.apply');
});

// Vehicles public show route after fixed routes
Route::get('/vehicles/{vehicle}/images/{index}', [VehicleController::class, 'showImage'])
    ->whereNumber('index')
    ->name('vehicles.images.show');
Route::middleware('dashboard.only')->get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');

// Drivers public show route after fixed routes
Route::middleware('dashboard.only')->get('/drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');

// Recruitments public show route after fixed routes
Route::middleware('dashboard.only')->get('/recruitments/{recruitment}', [RecruitmentController::class, 'show'])->name('recruitments.show');

// Admin routes
Route::domain(config('admin.domain'))->middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/service-requests', [ServiceRequestAdminController::class, 'index'])->name('service-requests.index');
    Route::get('/service-requests/{serviceRequest}', [ServiceRequestAdminController::class, 'show'])->whereNumber('serviceRequest')->name('service-requests.show');
    Route::put('/service-requests/{serviceRequest}', [ServiceRequestAdminController::class, 'review'])->whereNumber('serviceRequest')->name('service-requests.review');
    // Recruitment request review (approve/reject guest-submitted requests)
    Route::post('/recruitment-requests/{request}/approve', [\App\Http\Controllers\AdminController::class, 'approveRecruitmentRequest'])->name('recruitment-requests.approve');
    Route::post('/recruitment-requests/{request}/reject', [\App\Http\Controllers\AdminController::class, 'rejectRecruitmentRequest'])->name('recruitment-requests.reject');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/chart-data', [AdminController::class, 'chartData'])->name('chart-data');
    Route::get('/application/{booking}', [AdminController::class, 'applicationDetail'])->name('application.detail');
    Route::get('/documents/{type}/{id}/{field}/{index?}', [AdminController::class, 'viewSubmittedDocument'])
        ->where('type', 'driver|vehicle|recruitment|booking')
        ->where('index', '[0-9]+')
        ->name('documents.show');
    Route::get('/pending-application-documents/{type}/{id}/{field}/{index?}', [AdminController::class, 'viewPendingApplicationDocument'])
        ->where('type', 'driver|vehicle')
        ->where('index', '[0-9]+')
        ->name('application-changes.documents.show');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    
    // Admin user management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}/details', [AdminController::class, 'userDetails'])->name('users.details');
    Route::post('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::post('/users/{user}/profile-changes/approve', [AdminController::class, 'approveUserProfileChanges'])->name('users.profile-changes.approve');
    Route::post('/users/{user}/profile-changes/reject', [AdminController::class, 'rejectUserProfileChanges'])->name('users.profile-changes.reject');
    Route::post('/users/{user}/password-reset-link', [AdminController::class, 'createUserPasswordResetLink'])->name('users.password-reset-link');
    Route::get('/users/{user}', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/{user}/notify', [AdminController::class, 'notifyUser'])->name('users.notify');
    Route::post('/users/bulk-notify', [AdminController::class, 'bulkNotifyUsers'])->name('users.bulk-notify');
    Route::post('/users/bulk-role', [AdminController::class, 'bulkChangeRole'])->name('users.bulk-role');
    Route::post('/users/bulk-toggle-status', [AdminController::class, 'bulkToggleStatus'])->name('users.bulk-toggle-status');
    Route::post('/users/bulk-delete', [AdminController::class, 'bulkDeleteUsers'])->name('users.bulk-delete');

    // Export routes
    Route::get('/bookings/export', [ExportController::class, 'exportBookings'])->name('bookings.export');
    Route::get('/drivers/export', [ExportController::class, 'exportDrivers'])->name('drivers.export');
    Route::get('/users/export', [ExportController::class, 'exportUsers'])->name('users.export');
    Route::get('/users/bulk-export', [ExportController::class, 'bulkExportUsers'])->name('users.bulk-export');
    
    // Unified recruitment review system
    Route::get('/recruitment-review', [AdminController::class, 'recruitmentReview'])->name('recruitment-review');
    Route::post('/ajax/approve', [AdminController::class, 'ajaxApprove'])->name('ajax.approve');
    Route::post('/ajax/reject', [AdminController::class, 'ajaxReject'])->name('ajax.reject');
    
    // Legacy pending approval routes (keeping for backward compatibility)
    Route::get('/pending-drivers', [AdminController::class, 'pendingDrivers'])->name('pending-drivers');
    Route::get('/drivers/{driver}', [AdminController::class, 'driverDetail'])->name('drivers.detail');
    Route::put('/drivers/{driver}/vetting-checks', [AdminController::class, 'saveDriverVettingChecks'])->name('drivers.vetting-checks.update');
    Route::post('/drivers/{driver}/application-changes/approve', [AdminController::class, 'approveDriverApplicationChanges'])->name('drivers.application-changes.approve');
    Route::post('/drivers/{driver}/application-changes/reject', [AdminController::class, 'rejectDriverApplicationChanges'])->name('drivers.application-changes.reject');
    Route::post('/drivers/{driver}/approve', [AdminController::class, 'approveDriver'])->name('drivers.approve');
    Route::post('/drivers/{driver}/reject', [AdminController::class, 'rejectDriver'])->name('drivers.reject');
    
    Route::get('/pending-vehicles', [AdminController::class, 'pendingVehicles'])->name('pending-vehicles');
    Route::get('/vehicles/{vehicle}', [AdminController::class, 'vehicleDetail'])->name('vehicles.detail');
    Route::post('/vehicles/{vehicle}/application-changes/approve', [AdminController::class, 'approveVehicleApplicationChanges'])->name('vehicles.application-changes.approve');
    Route::post('/vehicles/{vehicle}/application-changes/reject', [AdminController::class, 'rejectVehicleApplicationChanges'])->name('vehicles.application-changes.reject');
    Route::post('/vehicles/{vehicle}/approve', [AdminController::class, 'approveVehicle'])->name('vehicles.approve');
    Route::post('/vehicles/{vehicle}/reject', [AdminController::class, 'rejectVehicle'])->name('vehicles.reject');
    
    Route::get('/pending-recruitments', [AdminController::class, 'pendingRecruitments'])->name('pending-recruitments');
    Route::get('/recruitments/{recruitment}', [AdminController::class, 'recruitmentDetail'])->name('recruitments.detail');
    Route::post('/recruitments/{recruitment}/approve', [AdminController::class, 'approveRecruitment'])->name('recruitments.approve');
    Route::post('/recruitments/{recruitment}/reject', [AdminController::class, 'rejectRecruitment'])->name('recruitments.reject');
    
    Route::get('/pending-bookings', [AdminController::class, 'pendingBookings'])->name('pending-bookings');
    Route::post('/bookings/{booking}/approve', [AdminController::class, 'approveBooking'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [AdminController::class, 'rejectBooking'])->name('bookings.reject');
    
    Route::get('/pending-payments', [AdminController::class, 'pendingPayments'])->name('pending-payments');
    Route::post('/payments/{payment}/approve', [AdminController::class, 'approvePayment'])->name('payments.approve');
    Route::post('/payments/{payment}/reject', [AdminController::class, 'rejectPayment'])->name('payments.reject');

    Route::put('/drivers/{driver}/moderate', [AdminController::class, 'moderateDriver'])->name('drivers.moderate');
    Route::put('/vehicles/{vehicle}/moderate', [AdminController::class, 'moderateVehicle'])->name('vehicles.moderate');
    Route::put('/recruitments/{recruitment}/moderate', [AdminController::class, 'moderateRecruitment'])->name('recruitments.moderate');

    Route::get('/analytics', [AdminController::class, 'analyticsPage'])->name('analytics');
    Route::get('/analytics-data', [AdminController::class, 'analytics'])->name('analytics.data');
    Route::get('/analytics-data-advanced', [AdminController::class, 'analyticsDataAdvanced'])->name('analytics.data-advanced');
    Route::get('/analytics/export/csv', [AdminController::class, 'exportAnalyticsCSV'])->name('analytics.export-csv');
    Route::get('/analytics/export/pdf', [AdminController::class, 'exportAnalyticsPDF'])->name('analytics.export-pdf');
    Route::get('/analytics/export/excel', [AdminController::class, 'exportAnalyticsExcel'])->name('analytics.export-excel');
    Route::get('/report-settings', [AdminController::class, 'reportSettings'])->name('report-settings');
    Route::put('/report-settings', [AdminController::class, 'updateReportSettings'])->name('report-settings.update');
    
    // All bookings and applications management
    Route::get('/bookings', [AdminController::class, 'allBookings'])->name('bookings');
    Route::put('/bookings/{booking}/tracking', [AdminController::class, 'updateBookingTracking'])->name('bookings.tracking');
    Route::get('/applications', [AdminController::class, 'allApplications'])->name('applications');
    
    // All drivers and vehicles management
    Route::get('/drivers', [AdminController::class, 'drivers'])->name('drivers');
    Route::get('/drivers/{driver}/edit-admin', [AdminController::class, 'editDriverAdmin'])->name('drivers.edit-admin');
    Route::put('/drivers/{driver}/update-admin', [AdminController::class, 'updateDriverAdmin'])->name('drivers.update-admin');
    
    Route::get('/vehicles', [AdminController::class, 'vehicles'])->name('vehicles');
    Route::get('/vehicles/{vehicle}/edit-admin', [AdminController::class, 'editVehicleAdmin'])->name('vehicles.edit-admin');
    Route::put('/vehicles/{vehicle}/update-admin', [AdminController::class, 'updateVehicleAdmin'])->name('vehicles.update-admin');
    
    Route::get('/recruitments', [AdminController::class, 'recruitments'])->name('recruitments');
    Route::get('/recruitments/create', [AdminController::class, 'createRecruitmentAdmin'])->name('recruitments.create');
    Route::post('/recruitments', [AdminController::class, 'storeRecruitmentAdmin'])->name('recruitments.store');
    Route::get('/recruitments/{recruitment}/edit-admin', [AdminController::class, 'editRecruitmentAdmin'])->name('recruitments.edit-admin');
    Route::put('/recruitments/{recruitment}/update-admin', [AdminController::class, 'updateRecruitmentAdmin'])->name('recruitments.update-admin');
    Route::delete('/recruitments/{recruitment}', [AdminController::class, 'destroyRecruitment'])->name('recruitments.destroy');
    
    Route::get('/bookings/{booking}', [AdminController::class, 'showBookingAdmin'])->name('bookings.show-admin');
    Route::get('/bookings/{booking}/edit-admin', [AdminController::class, 'editBookingAdmin'])->name('bookings.edit-admin');
    Route::put('/bookings/{booking}/update-admin', [AdminController::class, 'updateBookingAdmin'])->name('bookings.update-admin');
    
    // New Vehicle Management System
    Route::prefix('vehicles-new')->name('vehicles.')->group(function () {
        Route::get('/', [AdminVehicleController::class, 'index'])->name('index');
        Route::get('/{vehicle}', [AdminVehicleController::class, 'show'])->name('show');
        Route::post('/{vehicle}/approve', [AdminVehicleController::class, 'approve'])->name('approve');
        Route::post('/{vehicle}/reject', [AdminVehicleController::class, 'reject'])->name('reject');
        Route::post('/{vehicle}/update-availability', [AdminVehicleController::class, 'updateAvailability'])->name('update-availability');
        Route::post('/{vehicle}/suspend', [AdminVehicleController::class, 'suspend'])->name('suspend');
        Route::post('/{vehicle}/resume', [AdminVehicleController::class, 'resume'])->name('resume');
    });
    
    // Location tracker
    Route::get('/location-tracker', [AdminController::class, 'locationTracker'])->name('location-tracker');
    Route::get('/location-data', [AdminController::class, 'locationData'])->name('location-data');
});

Route::middleware(['auth', 'verified'])->post('/location/update', [App\Http\Controllers\LocationController::class, 'update'])->name('location.update');

// CMS - Content Management System for cms_admin
use App\Http\Controllers\CmsController;
use App\Http\Controllers\WebsiteController;
Route::domain(config('admin.domain'))->middleware(['auth', 'cms_admin'])->prefix('cms')->name('cms.')->group(function () {
    // CMS Dashboard
    Route::get('/', [CmsController::class, 'dashboard'])->name('dashboard');
    
    // Pages Management
    Route::get('/pages', [CmsController::class, 'pages'])->name('pages');
    Route::get('/pages/create', [CmsController::class, 'createPage'])->name('create-page');
    Route::post('/pages', [CmsController::class, 'storePage'])->name('store-page');
    Route::get('/pages/{page}/edit', [CmsController::class, 'editPage'])->name('edit-page');
    Route::put('/pages/{page}', [CmsController::class, 'updatePage'])->name('update-page');
    Route::delete('/pages/{page}', [CmsController::class, 'deletePage'])->name('delete-page');
    Route::post('/pages/{page}/publish', [CmsController::class, 'publishPage'])->name('publish-page');
    Route::post('/pages/{page}/unpublish', [CmsController::class, 'unpublishPage'])->name('unpublish-page');
    Route::get('/pages/{page}/preview', [CmsController::class, 'preview'])->name('preview-page');
    
    // Sections Management
    Route::post('/pages/{page}/sections', [CmsController::class, 'addSection'])->name('add-section');
    Route::put('/sections/{section}', [CmsController::class, 'updateSection'])->name('update-section');
    Route::delete('/sections/{section}', [CmsController::class, 'deleteSection'])->name('delete-section');
    Route::post('/pages/{page}/sections/reorder', [CmsController::class, 'reorderSections'])->name('reorder-sections');
    
    // Blocks Management (Reusable Components)
    Route::get('/blocks', [CmsController::class, 'blocks'])->name('blocks');
    Route::get('/blocks/create', [CmsController::class, 'createBlock'])->name('create-block');
    Route::post('/blocks', [CmsController::class, 'storeBlock'])->name('store-block');
    Route::get('/blocks/{block}/edit', [CmsController::class, 'editBlock'])->name('edit-block');
    Route::put('/blocks/{block}', [CmsController::class, 'updateBlock'])->name('update-block');
    Route::delete('/blocks/{block}', [CmsController::class, 'deleteBlock'])->name('delete-block');
    
    // Menus Management
    Route::get('/menus', [CmsController::class, 'menus'])->name('menus');
    Route::get('/menus/{menu}/edit', [CmsController::class, 'editMenu'])->name('edit-menu');
    Route::put('/menus/{menu}', [CmsController::class, 'updateMenu'])->name('update-menu');
    
    // Blog/Posts Management
    Route::get('/posts', [CmsController::class, 'posts'])->name('posts');
    Route::get('/posts/create', [CmsController::class, 'createPost'])->name('create-post');
    Route::post('/posts', [CmsController::class, 'storePost'])->name('store-post');
    Route::get('/posts/{post}/edit', [CmsController::class, 'editPost'])->name('edit-post');
    Route::put('/posts/{post}', [CmsController::class, 'updatePost'])->name('update-post');
    Route::delete('/posts/{post}', [CmsController::class, 'deletePost'])->name('delete-post');
    Route::post('/posts/{post}/publish', [CmsController::class, 'publishPost'])->name('publish-post');
    
    // Categories Management
    Route::get('/categories', [CmsController::class, 'categories'])->name('categories');
    Route::post('/categories', [CmsController::class, 'storeCategory'])->name('store-category');
    Route::put('/categories/{category}', [CmsController::class, 'updateCategory'])->name('update-category');
    Route::delete('/categories/{category}', [CmsController::class, 'deleteCategory'])->name('delete-category');
    
    // Tags Management
    Route::get('/tags', [CmsController::class, 'tags'])->name('tags');
    Route::post('/tags', [CmsController::class, 'storeTag'])->name('store-tag');
    Route::put('/tags/{tag}', [CmsController::class, 'updateTag'])->name('update-tag');
    Route::delete('/tags/{tag}', [CmsController::class, 'deleteTag'])->name('delete-tag');
    
    // Media Library
    Route::get('/media', [CmsController::class, 'media'])->name('media');
    Route::post('/media/upload', [CmsController::class, 'uploadMedia'])->name('upload-media');
    Route::put('/media/{media}', [CmsController::class, 'updateMedia'])->name('update-media');
    Route::delete('/media/{media}', [CmsController::class, 'deleteMedia'])->name('delete-media');
    Route::get('/media/folder', [CmsController::class, 'getMediaByFolder'])->name('media-by-folder');
    
    // Website Settings Management (moved from admin)
    Route::get('/website-settings', [WebsiteController::class, 'index'])->name('settings');
    Route::get('/website-appearance', [WebsiteController::class, 'appearance'])->name('appearance');
    Route::post('/website-appearance', [WebsiteController::class, 'updateAppearance'])->name('appearance.update');
    Route::get('/website-header-footer', [WebsiteController::class, 'headerFooter'])->name('header-footer');
    Route::post('/website-header-footer', [WebsiteController::class, 'updateHeaderFooter'])->name('header-footer.update');
    Route::get('/website-menus', [WebsiteController::class, 'menus'])->name('menus');
    Route::post('/website-menus/save', [WebsiteController::class, 'saveMenus'])->name('menus.save');
    Route::get('/website-global-settings', [WebsiteController::class, 'globalSettings'])->name('global-settings');
    Route::put('/website-settings/{key}', [WebsiteController::class, 'updateSetting'])->name('settings.update');
    Route::get('/website-settings/group/{group}', [WebsiteController::class, 'getSettingsByGroup'])->name('settings.group');
    Route::post('/website-settings/bulk-update', [WebsiteController::class, 'bulkUpdate'])->name('settings.bulk-update');
    Route::post('/website-settings/reset/{group}', [WebsiteController::class, 'resetDefaults'])->name('settings.reset');
});

// Site settings (kept for backward compatibility, redirects to CMS dashboard)
Route::domain(config('admin.domain'))->middleware(['auth', 'cms_admin'])->group(function () {
    Route::get('/site-settings', [AdminController::class, 'siteSettings'])->name('site-settings');
    Route::post('/site-settings', [AdminController::class, 'updateSiteSettings'])->name('site-settings.update');
});

// Roles and Permissions Management (Super Admin only)
use App\Http\Controllers\RoleController;
Route::domain(config('admin.domain'))->middleware(['auth'])->prefix('admin/roles')->name('roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::get('/create', [RoleController::class, 'create'])->name('create');
    Route::post('/', [RoleController::class, 'store'])->name('store');
    Route::get('/{role}', [RoleController::class, 'show'])->name('show');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
    Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    Route::post('/{role}/permissions', [RoleController::class, 'assignPermissions'])->name('assign-permissions');
});

// Website Settings Management - Moved to CMS section (see above)
// These routes are now under /cms/website-* with cms_admin middleware

// CMS Dynamic Page Route - MUST BE LAST (matches after all specific routes)
Route::middleware('dashboard.only')->get('/{page}', [App\Http\Controllers\HomeController::class, 'showPage'])->name('cms.page');
