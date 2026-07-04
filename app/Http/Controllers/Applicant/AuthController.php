<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\LeiPricingPlan;
use App\Models\User;
use App\Services\ApplicantAuthService;
use App\Services\ApplicantPortalRedirect;
use App\Services\GleifRegistrationPrefillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected ApplicantAuthService $authService,
        protected ApplicantPortalRedirect $portalRedirect,
        protected GleifRegistrationPrefillService $registrationPrefill,
    ) {}

    public function showLogin()
    {
        if ($redirect = $this->redirectPendingVerification()) {
            return $redirect;
        }

        if (auth()->check() && auth()->user()->isApplicant()) {
            if (! auth()->user()->is_active) {
                return redirect()->route('applicant.verify-otp');
            }

            return $this->redirectAfterApplicantAuth();
        }

        return view('public.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $credentials['email'])
            ->where('role', 'applicant')
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        $passwordValid = Hash::check($credentials['password'], $user->password);

        if (! $passwordValid && Hash::check(Hash::make($credentials['password']), $user->password)) {
            $user->forceFill(['password' => $credentials['password']])->save();
            $passwordValid = true;
        }

        if (! $passwordValid) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        if (! $user->is_active) {
            $otp = $this->authService->generateOtp($user, 'registration');
            session([
                'otp_user_id' => $user->id,
                'otp_code_dev' => $otp->code,
                'otp_last_sent_at' => now()->toIso8601String(),
            ]);

            return redirect()->route('applicant.verify-otp')
                ->with('info', 'Please verify your account. Check your email for the verification code.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectAfterApplicantAuth('Signed in successfully.');
    }

    public function showRegister()
    {
        if ($redirect = $this->redirectPendingVerification()) {
            return $redirect;
        }

        if (auth()->check() && auth()->user()->isApplicant()) {
            if (! auth()->user()->is_active) {
                return redirect()->route('applicant.verify-otp');
            }

            return $this->redirectAfterApplicantAuth();
        }

        $selectedPlan = session('intended_plan_id')
            ? LeiPricingPlan::find(session('intended_plan_id'))
            : null;

        $registrationPrefill = $this->registrationPrefill->get();

        return view('public.auth.register', compact('selectedPlan', 'registrationPrefill'));
    }

    public function register(Request $request)
    {
        if ($planId = $request->integer('plan') ?: session('intended_plan_id')) {
            session(['intended_plan_id' => $planId]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'organization_name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:32'],
            'country_of_incorporation' => ['nullable', 'string', 'max:64'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->numbers()->mixedCase()->symbols()],
            'terms' => ['accepted'],
            'privacy' => ['accepted'],
        ]);

        $existing = User::query()
            ->where('email', $data['email'])
            ->where('role', 'applicant')
            ->first();

        if ($existing) {
            if ($existing->is_active) {
                throw ValidationException::withMessages([
                    'email' => 'This email is already registered. Please sign in instead.',
                ]);
            }

            if (! Hash::check($data['password'], $existing->password)) {
                throw ValidationException::withMessages([
                    'email' => 'This email is awaiting verification. Sign in with your password or use forgot password.',
                ]);
            }

            $existing = $this->authService->assignLeiIfMissing($existing, $data['organization_name'] ?? null);
            $otp = $this->authService->generateOtp($existing);
            session([
                'otp_user_id' => $existing->id,
                'otp_code_dev' => $otp->code,
                'assigned_lei_number' => $existing->lei_number,
                'otp_last_sent_at' => now()->toIso8601String(),
            ]);

            return redirect()->route('applicant.verify-otp')
                ->with('info', 'Your account is pending verification. Check your email for the code.');
        }

        $user = $this->authService->createApplicant($data);
        $otp = $this->authService->generateOtp($user);
        session([
            'otp_user_id' => $user->id,
            'otp_code_dev' => $otp->code,
            'assigned_lei_number' => $user->lei_number,
            'otp_last_sent_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('applicant.verify-otp')
            ->with('info', 'Account created. Check your email for the verification code.');
    }

    public function showVerifyOtp()
    {
        if (! session('otp_user_id')) {
            return redirect()->route('applicant.login');
        }

        return view('public.auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $user = User::find(session('otp_user_id'));
        if (! $user) {
            return redirect()->route('applicant.login');
        }

        if (! $this->authService->verifyOtp($user, $request->code)) {
            throw ValidationException::withMessages(['code' => 'Invalid or expired verification code.']);
        }

        $user->refresh();
        $planId = session('intended_plan_id');

        session()->forget(['otp_user_id', 'otp_code_dev', 'otp_last_sent_at']);
        session(['lei_show_on_dashboard' => true]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($planId) {
            session(['intended_plan_id' => $planId]);
        }

        return redirect()->route('applicant.dashboard');
    }

    public function resendOtp(Request $request)
    {
        $userId = session('otp_user_id');

        if (! $userId) {
            return redirect()->route('applicant.login');
        }

        $user = User::query()
            ->where('id', $userId)
            ->where('role', 'applicant')
            ->first();

        if (! $user || $user->is_active) {
            session()->forget(['otp_user_id', 'otp_code_dev', 'otp_last_sent_at']);

            return redirect()->route('applicant.login');
        }

        $cooldownSeconds = 60;
        $lastSent = session('otp_last_sent_at');

        if ($lastSent) {
            $elapsed = now()->diffInSeconds(\Illuminate\Support\Carbon::parse($lastSent));

            if ($elapsed < $cooldownSeconds) {
                $wait = $cooldownSeconds - $elapsed;

                return back()->with('error', 'Please wait '.$wait.' second'.($wait === 1 ? '' : 's').' before requesting another code.');
            }
        }

        $otp = $this->authService->generateOtp($user, 'registration');

        session([
            'otp_code_dev' => $otp->code,
            'otp_last_sent_at' => now()->toIso8601String(),
            'assigned_lei_number' => $user->lei_number ?? session('assigned_lei_number'),
        ]);

        return back()->with('success', 'A new verification code has been sent to '.$user->email.'.');
    }

    public function sessionExpired()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('applicant.login')
            ->with('info', 'Your session expired due to inactivity. Please sign in again.');
    }

    public function showForgotPassword()
    {
        return view('public.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::query()
            ->where('email', $request->email)
            ->where('role', 'applicant')
            ->first();

        if (! $user) {
            return back()->withErrors(['email' => __('passwords.user')]);
        }

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('public.auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->numbers()->mixedCase()->symbols()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                if ($user->role !== 'applicant') {
                    return;
                }
                $user->forceFill(['password' => $password])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('applicant.login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function redirectPendingVerification()
    {
        $userId = session('otp_user_id');

        if (! $userId) {
            return null;
        }

        $user = User::query()
            ->where('id', $userId)
            ->where('role', 'applicant')
            ->first();

        if (! $user || $user->is_active) {
            session()->forget(['otp_user_id', 'otp_code_dev']);

            return null;
        }

        return redirect()->route('applicant.verify-otp')
            ->with('info', 'Please verify your account before continuing.');
    }

    private function redirectAfterApplicantAuth(?string $message = null)
    {
        if ($planId = session('intended_plan_id')) {
            $plan = LeiPricingPlan::query()->where('id', $planId)->where('is_active', true)->first();

            if ($plan && (float) $plan->price > 0) {
                session()->forget('intended_plan_id');

                return redirect()
                    ->route('applicant.plans.subscribe', $plan)
                    ->with('success', $message);
            }
        }

        return $this->portalRedirect->redirect(auth()->user(), $message ?? 'Welcome to the Applicant Portal.');
    }
}
