<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminTwoFactorCodeMail;
use App\Mail\ForgotPasswordMail;
use App\Models\PasswordResetToken;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Verification;
use App\Traits\Common_trait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    use  Common_trait;

    public function loginAuth(Request $req)
    {
        $req->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $req->input('email');
        $password = $req->input('password');

        $user = User::where('email', $email)->whereIn('role', [1, 2])->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return back()->with('flash-error', __('messages.invalid_credentials'))->withInput();
        }

        // The `$siteSetting` variable is only auto-shared with VIEWS by the
        // `View::composer('*')` block in AppServiceProvider — it's not in
        // scope here. Fetch the row explicitly and tolerate a fresh
        // installation that has no settings row yet.
        $siteSetting = SiteSetting::first();

        if ($siteSetting && $siteSetting->two_factor_enabled) {

            $otp = rand(100000, 999999);

            Verification::where('value', $email)->delete(); // CLEAR OLD OTP

            $verification = new Verification();
            $verification->otp = $otp;
            $verification->type = 1;
            $verification->otp_type = 3;
            $verification->value = $email;
            $verification->expires_at = now()->addMinutes((int) config('constants.OTP_EXPIRY_MINUTES'));
            $verification->save();

            $emailSent = $this->sendDynamicEmail(
                $user->email,
                'admin_2fa_code',
                [
                    'name' => $user->first_name,
                    'otp'  => $otp,
                ]
            );

            if (!$emailSent) {
                return back()->with('flash-error', __('messages.otp_send_failed'))->withInput();
            }

            // Save session so we know who is verifying
            session([
                'admin_2fa_email'   => $email,
            ]);

            return redirect()->route('admin.otpForm')->with('flash-success', __('messages.otp_sent_to_email'));
        }
        Auth::guard('admin')->login($user);

        return (int) $user->role === 2
            ? redirect()->route('user.dashboard')
            : redirect()->route('admin.dashboard');
    }

    public function otpForm()
    {
        if (!session('admin_2fa_email')) {
            return redirect()->route('admin.loginAuth');
        }
        return view('admin.auth.otp_verify');
    }

    public function verifyOtp(Request $req)
    {
        $otp = is_array($req->otp) ? implode('', $req->otp) : $req->otp;

        // Validate OTP
        $req->merge(['otp_combined' => $otp]); // merge into request for validation
        $req->validate([
            'otp_combined' => 'required|digits:6',
        ], [
            'otp_combined.required' => __('messages.verification_code_required'),
            'otp_combined.digits' => __('messages.verification_code_must_be_6_digits'),
        ]);


        $verification = Verification::where('value', session('admin_2fa_email'))
            ->where('otp_type', 3) // FOR 2FA
            ->first();


        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => __('messages.session_expired')
            ], 400);
        }

        if ($verification->otp !== $otp) {
            return response()->json([
                'success' => false,
                'message' => __('messages.invalid_otp')
            ], 400);
        }


        if (now()->greaterThan($verification->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.otp_expired')
            ], 400);
        }

        // OTP verified successfully
        $verification->delete();
        $user = User::where('email', session('admin_2fa_email'))->first();
        session()->forget('admin_2fa_email');

        if ($user) {
            Auth::guard('admin')->loginUsingId($user->id);
        }

        $redirect = ($user && (int) $user->role === 2)
            ? route('user.dashboard')
            : route('admin.dashboard');

        return response()->json([
            'success'      => true,
            'message'      => __('messages.user_logged_in'),
            'redirect_url' => $redirect,
        ]);
    }

    public function resendOtp(Request $request)
    {
        if (!session('admin_2fa_email')) {
            return redirect()->route('admin.loginAuth');
        }

        $email = session('admin_2fa_email');

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => __('messages.session_expired')
            ], 400);
        }

        $user = User::where('email', $email)->whereIn('role', [1, 3])->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('messages.user_not_found')
            ], 400);
        }

        Verification::where('value', $email)->delete(); // DELETE OLD OTP

        $otp = rand(100000, 999999);

        $verification = new Verification();
        $verification->otp = $otp;
        $verification->type = 1;
        $verification->otp_type = 3;
        $verification->value = $email;
        $verification->expires_at = now()->addMinutes((int) config('constants.OTP_EXPIRY_MINUTES'));
        $verification->save();

        try {
            $emailSent = $this->sendDynamicEmail(
                $user->email,
                'admin_2fa_code',
                [
                    'name' => $user->first_name,
                    'otp'  => $otp,
                ]
            );

            if (!$emailSent) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.otp_send_failed')
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.otp_sent_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.otp_send_failed')
            ], 500);
        }
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }

    public function sendResetToken(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => __('messages.email_not_found')]);
        }

        $token = bin2hex(random_bytes(16));
        $createdAt = Carbon::now();

        // Save token
        PasswordResetToken::updateOrCreate(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => $createdAt]
        );

        // Send email
        $resetLink = url('/admin/password/reset/' . $token);

        // MAIL CODE
        //$this->sendEmail($request->email, new ForgotPasswordMail($resetLink, $user->first_name));

        $this->sendDynamicEmail(
            $user->email,
            'forgot_password',
            [
                'name' => $user->first_name,
                'link' => $resetLink,
            ]
        );

        return back()->with('flash-success', __('messages.password_reset_link_sent'));
    }

    public function showResetForm($token)
    {
        $tokenData = PasswordResetToken::where('token', $token)->first();
        if (!$tokenData || Carbon::parse($tokenData->created_at)->addMinutes(15)->isPast()) {
            return redirect()->route('admin.forgotPassword')->with('flash-error', __('messages.invalid_or_expired_token'));
        }

        return view('admin.auth.reset-password', ['token' => $token]);
    }

    // Handle password update
    public function resetPassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|confirmed|min:6|max:15',
        ]);

        $tokenData = PasswordResetToken::where('token', $request->token)->first();

        if (!$tokenData || Carbon::parse($tokenData->created_at)->addMinutes(15)->isPast()) {
            return redirect()->route('admin.forgotPassword')->with('flash-error', __('messages.invalid_or_expired_token'));
        }

        $user = User::where('email', $tokenData->email)->first();

        if (!$user) {
            return redirect('/password/request')->withErrors(['email' => __('messages.user_not_found')]);
        }

        $user->password = Hash::make($request->new_password);

        if ($user->save()) {
            $tokenData->delete();
            return redirect()->route('admin.login')->with('flash-success', __('messages.password_reset_successfully'));
        }
        return redirect()->route('admin.forgotPassword')->with('flash-error', __('messages.something_went_wrong'));
    }
}
