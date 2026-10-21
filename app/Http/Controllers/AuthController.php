<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login', [
            'loginType' => 'user',
            'title' => 'Login',
            'subtitle' => 'Sign in to continue to your account and manage bookings, applications, and drivers in one place.',
            'actionRoute' => route('login.post'),
            'showAlternateLinks' => true,
        ]);
    }

    public function adminLoginForm()
    {
        return view('auth.login', [
            'loginType' => 'admin',
            'title' => 'Admin Login',
            'subtitle' => 'Sign in to manage the admin dashboard and platform operations.',
            'actionRoute' => route('admin.login.post'),
            'showAlternateLinks' => false,
        ]);
    }

    public function serviceCustomerLoginForm(Request $request)
    {
        $requestType = $request->query('requestType');
        if (in_array($requestType, ['driver', 'vehicle'], true)) {
            $request->session()->put('service.request_type', $requestType);
        }

        return view('auth.login', [
            'loginType' => 'service_customer',
            'title' => 'Service Customer Login',
            'subtitle' => 'Sign in to request drivers and vehicles and track your service requests.',
            'actionRoute' => route('service.login.post'),
            'showAlternateLinks' => false,
            'requestType' => $request->session()->get('service.request_type'),
        ]);
    }

    public function cmsLoginForm()
    {
        return view('auth.login', [
            'loginType' => 'cms',
            'title' => 'CMS Login',
            'subtitle' => 'Sign in to manage website content, pages, posts, and media.',
            'actionRoute' => route('cms.login.post'),
            'showAlternateLinks' => false,
        ]);
    }

    public function login(Request $request, string $loginType = 'user')
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if ($loginType === 'service_customer' && in_array($request->input('request_type'), ['driver', 'vehicle'], true)) {
            $request->session()->put('service.request_type', $request->input('request_type'));
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        if ($loginType === 'service_customer' && !Auth::user()->isServiceCustomer()) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'These credentials are not for a service-request account.',
            ])->onlyInput('email');
        }

        if (
            $loginType === 'user'
            && (
                (method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin())
                || (method_exists(Auth::user(), 'isCmsAdmin') && Auth::user()->isCmsAdmin())
            )
        ) {
            $adminLoginRoute = Auth::user()->isAdmin() ? 'admin.login' : 'cms.login';
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($adminLoginRoute)
                ->with('info', 'Please sign in through the separate administrator portal.');
        }

        $request->session()->regenerate();
        Auth::user()->syncOnboardingStatus();

        if ($loginType === 'admin') {
            if (!(method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin())) {
                Auth::logout();
                return back()->withErrors([ 'email' => 'You do not have administrator access.' ])->onlyInput('email');
            }
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($loginType === 'cms') {
            if (!(method_exists(Auth::user(), 'isCmsAdmin') && Auth::user()->isCmsAdmin())) {
                Auth::logout();
                return back()->withErrors([ 'email' => 'You do not have CMS access.' ])->onlyInput('email');
            }
            return redirect()->intended(route('cms.dashboard'));
        }

        if (method_exists(Auth::user(), 'isCmsAdmin') && Auth::user()->isCmsAdmin()) {
            return redirect()->intended(route('cms.dashboard'));
        }

        if (method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if (Auth::user()->isServiceCustomer()) {
            if (!Auth::user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            $requestType = $request->session()->pull('service.request_type');
            if (in_array($requestType, ['driver', 'vehicle'], true)) {
                return redirect()->intended(route('service.requests.create', $requestType));
            }

            return redirect()->intended(route('service.dashboard'));
        }

        if (!Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function adminLogin(Request $request)
    {
        return $this->login($request, 'admin');
    }

    public function cmsLogin(Request $request)
    {
        return $this->login($request, 'cms');
    }

    public function serviceCustomerLogin(Request $request)
    {
        return $this->login($request, 'service_customer');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'onboarding_completed' => false,
        ]);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function verificationNotice()
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return $this->verifiedDestination(Auth::user());
        }

        return view('auth.verify-email');
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->verifiedDestination($request->user());
        }

        $request->fulfill();

        return $this->verifiedDestination($request->user())
            ->with('status', 'email-verified');
    }

    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->verifiedDestination($request->user());
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    private function verifiedDestination(User $user): \Illuminate\Http\RedirectResponse
    {
        if ($user->isServiceCustomer()) {
            $requestType = request()->session()->pull('service.request_type');
            if (in_array($requestType, ['driver', 'vehicle'], true)) {
                return redirect()->route('service.requests.create', $requestType);
            }

            return redirect()->route('service.dashboard');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function dashboard()
    {
        $user = Auth::user();
        if ($user->isServiceCustomer()) {
            return redirect()->route('service.dashboard');
        }

        $user->syncOnboardingStatus();

        // Redirect to admin dashboard only for admin/super_admin roles
        if (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin'])) {
            return redirect()->route('admin.dashboard');
        }

        $driverProfile = $user->driver;
        $dashboardVehicles = $user->vehicles()->latest()->take(5)->get();
        $recentBookings = $user->bookings()
            ->where('booking_type', '!=', 'recruitment')
            ->with(['driver.user', 'vehicle'])
            ->latest()
            ->take(5)
            ->get();
        $notifications = $user->alerts()->take(6)->get();
        $unreadNotifications = $user->alerts()->where('status', 'unread')->count();

        $stats = [
            'vehicles' => $user->vehicles()->where('approval_status', 'approved')->count(),
            'drivers' => $user->driver()->where('approval_status', 'approved')->count(),
            'recruitments' => $user->recruitments()->count(),
            'bookings' => $user->bookings()->where('booking_type', '!=', 'recruitment')->count(),
        ];

        return view('dashboard', compact('user', 'stats', 'driverProfile', 'dashboardVehicles', 'recentBookings', 'notifications', 'unreadNotifications'));
    }

    public function notifications()
    {
        $user = Auth::user();
        $notifications = $user->alerts()->paginate(12);
        $unreadNotifications = $user->alerts()->where('status', 'unread')->count();

        return view('notifications', compact('user', 'notifications', 'unreadNotifications'));
    }

    public function profile()
    {
        return view('profile', ['user' => Auth::user()]);
    }

    public function profileEdit()
    {
        return view('profile-edit', ['user' => Auth::user()]);
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();
        if ($user->pending_profile_changes) {
            return redirect()->route($user->isServiceCustomer() ? 'service.profile' : 'profile.edit')
                ->withErrors(['profile' => 'Your profile changes are awaiting admin review. You can submit another update after they are reviewed.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $changes = array_filter(
            $validated,
            fn ($value, $field) => $value !== $user->{$field},
            ARRAY_FILTER_USE_BOTH
        );

        if ($changes === []) {
            return redirect()->route($user->isServiceCustomer() ? 'service.profile' : 'profile.edit')
                ->with('success', 'No profile changes were submitted.');
        }

        $user->pending_profile_changes = $changes;
        $user->save();

        return redirect()->route($user->isServiceCustomer() ? 'service.profile' : 'profile')
            ->with('success', 'Your profile changes have been submitted for admin review. Your current approved details remain active until approval.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->password = $validated['password'];
        $user->save();

        return redirect()->route($user->isServiceCustomer() ? 'service.profile' : 'profile')
            ->with('success', 'Password updated successfully.');
    }

    public function markNotificationRead(Request $request, \App\Models\Notification $notification)
    {
        $user = Auth::user();

        if ($notification->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $notification->update(['status' => 'read']);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function onboarding()
    {
        $user = Auth::user();
        if ($user->isServiceCustomer()) {
            return redirect()->route('service.dashboard');
        }

        $user->syncOnboardingStatus();

        return redirect()->route('dashboard');
    }

    // Password Reset Methods
    public function forgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = PasswordFacade::sendResetLink(
            $request->only('email')
        );

        return $status === PasswordFacade::RESET_LINK_SENT
            ? back()->with('success', 'Password reset link sent to your email.')
            : back()->withErrors(['email' => 'Unable to send reset link. Please try again.']);
    }

    public function resetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $status = PasswordFacade::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status === PasswordFacade::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password reset successfully. Please log in.')
            : back()->withErrors(['email' => 'Invalid reset token or email.']);
    }

    // Social Authentication Methods
    public function redirectToGoogle()
    {
        if (!config('services.google.client_id') || !config('services.google.client_secret')) {
            \Log::error('Google sign-in is unavailable because OAuth credentials are not configured.');

            return redirect()->route('login')->withErrors([
                'social' => 'Google sign-in is not configured. Please use email and password or contact support.',
            ]);
        }

        $callback = config('services.google.redirect');
        $callbackParts = is_string($callback) ? parse_url($callback) : false;
        if (
            is_array($callbackParts) &&
            isset($callbackParts['scheme'], $callbackParts['host']) &&
            in_array($callbackParts['scheme'], ['http', 'https'], true)
        ) {
            $callbackOrigin = $callbackParts['scheme'] . '://' . $callbackParts['host']
                . (isset($callbackParts['port']) ? ':' . $callbackParts['port'] : '');

            if (request()->getSchemeAndHttpHost() !== $callbackOrigin) {
                return redirect()->away($callbackOrigin . '/auth/google');
            }
        }

        try {
            return Socialite::driver('google')->redirect();
        } catch (\Throwable $e) {
            \Log::error('Failed to start Google sign-in.', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'social' => 'Unable to start Google sign-in. Please try again or use email and password.',
            ]);
        }
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $email = $googleUser->getEmail();
            $emailVerified = filter_var(
                data_get($googleUser->user ?? [], 'email_verified'),
                FILTER_VALIDATE_BOOLEAN
            );

            if (!is_string($email) || trim($email) === '') {
                throw new \UnexpectedValueException('Google did not return an email address.');
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                $name = $googleUser->getName();
                $user = User::create([
                    'name' => is_string($name) && trim($name) !== '' ? $name : strtok($email, '@'),
                    'email' => $email,
                    'password' => Hash::make(Str::random(24)),
                    'role' => 'user',
                    'onboarding_completed' => false,
                    'email_verified_at' => $emailVerified ? now() : null,
                ]);
                if (!$emailVerified) {
                    event(new Registered($user));
                }
            } elseif ($emailVerified && !$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            Auth::login($user);
            request()->session()->regenerate();

            $user->syncOnboardingStatus();

            // Redirect based on role
            if (method_exists($user, 'isCmsAdmin') && $user->isCmsAdmin()) {
                return redirect()->intended(route('cms.dashboard'));
            }
            if (method_exists($user, 'hasRole') && $user->hasRole(['super_admin','admin'])) {
                return redirect()->intended(route('admin.dashboard'));
            }
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
            return redirect()->intended(route('dashboard'));
        } catch (\Throwable $e) {
            \Log::error('Google sign-in callback failed.', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'google_error' => request()->query('error'),
            ]);

            return redirect()->route('login')->withErrors([
                'social' => 'Unable to authenticate with Google. Please try again or use email and password.',
            ]);
        }
    }

    public function redirectToApple()
    {
        return Socialite::driver('apple')->redirect();
    }

    public function handleAppleCallback()
    {
        try {
            $appleUser = Socialite::driver('apple')->user();
            $emailVerified = filter_var(
                data_get($appleUser->user ?? [], 'email_verified'),
                FILTER_VALIDATE_BOOLEAN
            );

            $user = User::where('email', $appleUser->getEmail())->first();

            if ($user) {
                if ($emailVerified && !$user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }
                Auth::login($user);
            } else {
                $user = User::create([
                    'name' => $appleUser->getName() ?? 'Apple User',
                    'email' => $appleUser->getEmail(),
                    'password' => Hash::make(Str::random(24)),
                    'role' => 'user',
                    'onboarding_completed' => false,
                    'email_verified_at' => $emailVerified ? now() : null,
                ]);
                Auth::login($user);
                if (!$user->hasVerifiedEmail()) {
                    $user->sendEmailVerificationNotification();
                }
            }

            $user->syncOnboardingStatus();

            if (method_exists($user, 'isCmsAdmin') && $user->isCmsAdmin()) {
                return redirect()->intended(route('cms.dashboard'));
            }
            if (method_exists($user, 'hasRole') && $user->hasRole(['super_admin','admin'])) {
                return redirect()->intended(route('admin.dashboard'));
            }
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['social' => 'Unable to authenticate with Apple.']);
        }
    }
}