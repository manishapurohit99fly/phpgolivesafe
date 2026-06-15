<?php

declare(strict_types=1);

namespace App\Http\Controllers\api\v1;

use App\Http\Resources\UserResource;
use OpenApi\Attributes as OA;
use App\Mail\OtpMail;
use App\Models\Roles;
use App\Models\User;
use App\Models\Verification;
use App\Traits\Common_trait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;

class ApiAuthController extends BaseApiController
{
    use Common_trait;

    #[OA\Post(
        path: '/api/v1/signup',
        operationId: 'signup',
        summary: 'Register a new user',
        description: 'Sends OTP to email for verification if not yet verified, then creates account on second call (after OTP verified).',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'password_confirmation', 'device_type', 'device_id'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 6, maxLength: 15, example: 'Secret@123', description: 'Min 6 chars, mixed case, number & symbol required'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'Secret@123'),
                    new OA\Property(property: 'device_type', type: 'string', enum: ['android', 'ios'], example: 'android'),
                    new OA\Property(property: 'device_id', type: 'string', example: 'fcm_token_here'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OTP sent or user registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User registered successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/AuthData'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function signup(Request $req)
    {

        $v = Validator::make($req->all(), [
            'first_name' => 'required|string|max:100|min:2',
            'last_name' => 'string',
            'email' => [
                'required',
                'email',
                Rule::unique(User::class, 'email')->whereNull('deleted_at'),
            ],
            // 'language' => 'required|in:1,2',
            'password' => [
                'required',
                'string',
                'min:6',
                'max:15',
                'confirmed',
                RulesPassword::min(6)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'device_id' => 'required|string',
            'device_type' => 'required|string',
        ], [
            'password.required' => 'A password is required.',
            'password.min' => 'The password must be at least 6 characters long.',
            'password.max' => 'The password may not be greater than 15 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.mixedCase' => 'The password must contain both uppercase and lowercase letters.',
            'password.numbers' => 'The password must contain at least one number.',
            'password.symbols' => 'The password must contain at least one special character.',
        ]);
        if ($v->fails()) {
            $firstError = collect($v->errors()->all())->first();

            return $this->sendError($firstError, [], 422);
        }
        try {
            
            $validated = $v->validated();
            
            if (! isset($validated['email'])) {
                return $this->sendError('Email is required', [], 422);
            }
            $email = strtolower($validated['email']);
            $deviceId = $validated['device_id'];
            $existingUser = User::where('email', $email)->whereNull('deleted_at')->first();
            if ($existingUser) {
                return $this->sendError('This email is already registered.', [], 422);
            }
            $isVerified = Verification::where('value', $email)
                ->where('device_id', $deviceId)
                ->where('type', 1)
                ->where('status', 1)
                ->exists();
            if (! $isVerified) {
                $otp = rand(1000, 9999);
                $expiryTime = Carbon::now()->addMinutes(10);
                $verification = Verification::updateOrCreate(
                    [
                        'value' => $email,
                        'device_id' => $deviceId,
                        'type' => 1,
                    ],
                    [
                        'device_type' => $validated['device_type'],
                        'otp' => $otp,
                        'expires_at' => $expiryTime,
                        'otp_type' => 1,
                        'status' => 0,
                    ]
                );
                if ($verification) {
                    $this->sendEmail($email, new OtpMail($otp));

                    return $this->sendResponse(null, 'An OTP has been sent to your email address. Please verify it to complete the signup process.');
                }
            }
            
            $user = User::create([
                'first_name' => $validated['first_name'] ?? "",
                'last_name'=> $validated['last_name'] ?? "",
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'role' => 2,                
                'status' => 1,
                'device_id' => $deviceId,
            ]);

            $token = $user->createToken(config('app.secret.key'))->plainTextToken;
            // $lastToken = $user->tokens()->latest()->first();
            // if ($lastToken) {
            //     $user->last_token_id = $lastToken->id;
            //     $user->save();
            // }
            // Mail::to($user->email)->send(new SuccessfulSignupMail($user));
            Verification::where('value', $email)
                ->where('device_id', $deviceId)
                ->where('type', 1)
                ->delete();

            return $this->sendResponse([
                'user' => new UserResource($user),
                'token' => $token,
            ], 'User registered successfully');
        } catch (\Exception $e) {
            return $this->sendError('Some error occurred', [$e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/send-otp',
        operationId: 'sendOtpVerification',
        summary: 'Send OTP to email for verification',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['value', 'type', 'device_type', 'device_id'],
                properties: [
                    new OA\Property(property: 'value', type: 'string', format: 'email', example: 'john@example.com', description: 'Email address'),
                    new OA\Property(property: 'type', type: 'string', enum: ['email'], example: 'email'),
                    new OA\Property(property: 'device_type', type: 'string', enum: ['android', 'ios'], example: 'android'),
                    new OA\Property(property: 'device_id', type: 'string', example: 'fcm_token_here'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OTP sent successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'OTP sent successfully via email'),
                        new OA\Property(property: 'data', type: 'string', example: 'john@example.com'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Already verified or invalid', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function sendOtpVerification(Request $req)
    {
        $v = Validator::make($req->all(), [
            'value' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return;
                    }
                    if (! ctype_digit($value)) {
                        $fail('The value must be a valid email .');
                    }
                },
            ],
            'type' => 'required|in:email',
            'device_type' => 'required|in:android,ios',
            'device_id' => 'required',
        ]);
        if ($v->fails()) {
            $firstError = collect($v->errors()->all())->first();

            return $this->sendError($firstError, [], 422);
        }
        if (! filter_var($req->value, FILTER_VALIDATE_EMAIL)) {
            return $this->sendError('Invalid email address', [], 400);
        }
        $otp = rand(1000, 9999);
        $expiryTime = Carbon::now()->addMinutes(10);
        $verification = Verification::where('value', strtolower($req->value))
            ->where('type', 1)
            ->where('device_id', $req->device_id)
            ->first();
        if ($verification && $verification->status == 1) {
            return $this->sendError('This Email is already verified', [], 400);
        }
        if ($verification) {
            if ($verification->device_id == $req->device_id) {
                $verification->otp = $otp;
                $verification->expires_at = $expiryTime;
                $verification->otp_type = 1;
                $verification->save();
            } else {
                $verification = new Verification;
                $verification->value = strtolower($req->value);
                $verification->type = 1; // 1 = email verify
                $verification->device_type = $req->device_type;
                $verification->device_id = $req->device_id;
                $verification->otp = $otp;
                $verification->expires_at = $expiryTime;
                $verification->otp_type = 1;
                $verification->save();
            }
        } else {
            $verification = new Verification;
            $verification->value = strtolower($req->value);
            $verification->type = 1;
            $verification->device_type = $req->device_type;
            $verification->device_id = $req->device_id;
            $verification->otp = $otp;
            $verification->expires_at = $expiryTime;
            $verification->otp_type = 1;
            $verification->save();
        }
        if ($req->type == 'email') {            
            $emailSent = $this->sendDynamicEmail(
                                $request->email,
                                'user_2fa_code',
                                [                                    
                                    'otp'    => $otp,                                    
                                ]
                            );


            return $this->sendResponse($verification->value, 'OTP sent successfully via email');
        }
    }

    #[OA\Post(
        path: '/api/v1/verify-otp',
        operationId: 'verifyOtp',
        summary: 'Verify the OTP sent to email',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['value', 'type', 'otp', 'device_type', 'device_id'],
                properties: [
                    new OA\Property(property: 'value', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'type', type: 'string', enum: ['email'], example: 'email'),
                    new OA\Property(property: 'otp', type: 'string', example: '1234'),
                    new OA\Property(property: 'device_type', type: 'string', enum: ['android', 'ios'], example: 'android'),
                    new OA\Property(property: 'device_id', type: 'string', example: 'fcm_token_here'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OTP verified successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'OTP verified successfully'),
                        new OA\Property(property: 'data', type: 'string', example: 'john@example.com'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Invalid or expired OTP', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function verifyOtp(Request $req)
    {
        $v = Validator::make($req->all(), [
            'value' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return;
                    }
                    if (! ctype_digit($value)) {
                        $fail('The value must be a valid email .');
                    }
                },
            ],
            'type' => 'required|in:email',
            'otp' => 'required',
            'device_type' => 'required|in:android,ios',
            'device_id' => 'required',
        ]);
        if ($v->fails()) {
            $firstError = collect($v->errors()->all())->first();

            return $this->sendError($firstError, [], 422);
        }
        try {
            if (! filter_var($req->value, FILTER_VALIDATE_EMAIL)) {
                return $this->sendError('Invalid email address', [], 400);
            }
            $otpRecord = Verification::where('value', strtolower($req->value))
                ->where('otp_type', 1)
                ->where('device_id', $req->device_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (! $otpRecord) {
                return $this->sendError('Account not found', [], 400);
            }
            if ($otpRecord->status == 1) {
                return $this->sendError('This account is already verified', [], 400);
            }

            if ($otpRecord->otp !== $req->otp) {
                return $this->sendError('Invalid OTP', [], 400);
            }
            if ($otpRecord->expires_at && Carbon::now()->gt($otpRecord->expires_at)) {
                return $this->sendError('OTP has expired', [], 400);
            }
            $otpRecord->status = 1;
            $otpRecord->save();
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', [$e->getMessage()], 500);
        }

        return $this->sendResponse($otpRecord->value, message: 'OTP verified successfully');
    }

    #[OA\Post(
        path: '/api/v1/login',
        operationId: 'login',
        summary: 'Login a user',
        description: 'Authenticates a user and returns a Sanctum bearer token.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'device_type', 'device_id'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Secret@123'),
                    new OA\Property(property: 'device_type', type: 'string', enum: ['android', 'ios'], example: 'android'),
                    new OA\Property(property: 'device_id', type: 'string', example: 'fcm_token_here'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User login successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/AuthData'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Invalid credentials', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_type' => 'required|in:android,ios',
            'device_id' => 'required|string',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $throttleKey =  Str::lower($request->input('email')) . '|' . $request->ip();
        $maxAttempts = 3;
        $decaySeconds = 180; // 3 minutes
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return $this->sendError(
                "Too many attempts detected. Please try again later.",
                ['retry_after' => $seconds],
                429
            );
        }

        $user = User::where('email', $request->email)->first();        
        if (! $user) {
            RateLimiter::hit($throttleKey, $decaySeconds);
            return $this->sendError('Invalid credentials', [], 401);
        }

        // Check password
        if (! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, $decaySeconds);
            $remaining = max(0, $maxAttempts - RateLimiter::attempts($throttleKey));
            return $this->sendError($remaining > 0
            ? "Too many attempts detected. Please try again later."
            : 'Too many attempts detected. Please try again later.',
            ['attempts_left' => $remaining],
            429);
        }

        $user->device_type = $request->device_type;
        $user->device_id = $request->device_id;

        if ($user->save()) {
            $token = $user->createToken(config('app.secret.key'))->plainTextToken;
            $result = array_merge((new UserResource($user))->toArray(request()), ['token' => $token]);

            return $this->sendResponse($result, 'User login successfully');
        } else {
            return $this->sendError('Some error occured', [], 404);
        }
    }

    #[OA\Post(
        path: '/api/v1/auth/logout',
        operationId: 'logout',
        summary: 'Logout the authenticated user',
        description: 'Revokes the current Sanctum token and clears device info.',
        security: [['sanctum' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Logout successful'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function logout(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return $this->sendError('User unauthenticated', [], 401);
        }

        $token = $user->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        $user->device_id = null;
        $user->device_type = null;
        $user->save();

        return $this->sendResponse([], 'Logout successful');
    }

    #[OA\Post(
        path: '/api/v1/auth/change-password',
        operationId: 'changePassword',
        summary: "Change the authenticated user's password",
        description: 'Verifies current password and sets a new one.',
        security: [['sanctum' => []]],
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'new_password', 'new_password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password', type: 'string', format: 'password', example: 'Secret@123'),
                    new OA\Property(property: 'new_password', type: 'string', format: 'password', minLength: 6, maxLength: 15, example: 'NewSecret@456'),
                    new OA\Property(property: 'new_password_confirmation', type: 'string', format: 'password', example: 'NewSecret@456'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Password updated successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 422, description: 'Wrong current password or validation error', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|max:15|confirmed',
        ], [
            'new_password.confirmed' => 'New password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), [], 422);
        }

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Current password is incorrect', ['current_password' => ['Current password is incorrect']], 422);
        }

        // Prevent same new password
        if (Hash::check($request->new_password, $user->password)) {
            return $this->sendError('New password cannot be the same as the current password', [
                'new_password' => ['New password cannot be the same as the current password'],
            ], 422);
        }

        $user->password = Hash::make($request->new_password);

        if ($user->save()) {
            return $this->sendResponse([], 'Password updated successfully');
        } else {
            return $this->sendError('Oops! Something went wrong!', [], 500);
        }
    }
   

    #[OA\Post(
        path: '/api/v1/forgot-password',
        operationId: 'forgotPassword',
        summary: 'Send a password reset link to email',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reset link sent',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Reset password link sent successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 422, description: 'Email not found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $table = config('tables.users');
            $validator = Validator::make($request->all(), [
                'email' => "required|email|exists:$table,email",
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), null, 422);
            }

            $user = User::where('email', $request->email)->first();

            // Generate token
            $token = Str::random(64);

            // Store token in password_resets table
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => Carbon::now(),
                ]
            );

            // Send custom reset mail
            $resetLink = url('/api/v1/reset-password?token=' . $token . '&email=' . urlencode($request->email));
            
            $fullname = trim($user->first_name . ' ' . ($user->last_name ?? ''));
            
            $emailSent = $this->sendDynamicEmail(
                                $request->email,
                                'user_reset_password',
                                [
                                    'reset_link'     => $resetLink,
                                    'name'    => $fullname,                                    
                                ]
                            );

            return $this->sendResponse([], 'Reset password link sent successfully');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', $e->getMessage(), 500);
        }
    }

    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');
        
        $tokenExists = DB::table('password_reset_tokens')->where('email', $email)->first();
        if (! $tokenExists) {
            return view('reset-password', ['isExpired' => true, 'email' => $email]);
        }

        return view('reset-password', ['token' => $token, 'email' => $email, 'isExpired' => false]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:6',
                'max:15',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            ],
        ], [
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.max' => 'The password must not exceed 15 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
        ]);
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            if (Hash::check($password, $user->password)) {
                throw ValidationException::withMessages(['password' => ['You cannot reuse your old password. Please choose a different one.']]);
            }
            $user->forceFill(['password' => Hash::make($password)])->save();
        });
        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('thankyou')->with('message', 'Your password has been reset successfully. You can now log in to the app with your new password.');
        }

        return back()->withErrors(['email' => [__($status)]]);
    }

    public function resetPassword(Request $req)
    {
        $v = Validator::make($req->all(), [
            'email' => 'required|email',
            'password' => [
                'required',
                'min:8',
                'max:15',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            ],
            'device_type' => 'required|in:1,2',
            'device_id' => 'required',
        ], [
            'password.required' => 'The password field is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password must not exceed 15 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
        ]);
        if ($v->fails()) {
            $firstError = collect($v->errors()->all())->first();

            return $this->sendError($firstError, [], 422);
        }
        $user = User::where('email', strtolower($req->email))->first();
        if (! $user) {
            return $this->sendError(__('messages.not_found', ['item' => 'User']), [], 404);
        }
        if ($user->status == 0) {
            return $this->sendError(__('messages.account_is_deactivate'), null, 403);
        }
        if (Hash::check($req->password, $user->password)) {
            return $this->sendError(__('messages.you_cannot_reuse_your_old_password'), [], 422);
        }
        $user->password = Hash::make($req->password);
        $user->save();

        return $this->sendResponse([], __('messages.password_reset_successful'));
    }

    #[OA\Get(
        path: '/api/v1/auth/profile',
        operationId: 'getProfile',
        summary: 'Get authenticated user profile',
        security: [['sanctum' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User profile fetched successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/UserObject'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function profile(Request $request)
    {
        $user = $request->user();

        return $this->sendResponse($user, 'User profile fetched successfully');
    }

    
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'fullname' => 'sometimes|string|max:50',
            'billing_email' => 'sometimes|string|max:50',
            'role_id' => 'required|integer|exists:'.config('tables.roles').',id',
            'profile_photo' => 'sometimes|file|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Update basic fields
        $user->first_name = $request->fullname ?? $user->first_name;
        $user->billing_email = $request->billing_email ?? $user->billing_email;
        $user->role = $request->role_id ?? $user->role;

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {

            // Delete old image if exists
            if (! empty($user->profile_photo)) {
                $oldPath = public_path($user->profile_photo);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Save new file
            $file = $request->file('profile_photo');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/profile_photo'), $filename);

            $user->profile_photo = 'uploads/profile_photo/'.$filename;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user,
        ]);
    }

    public function setupProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'fullname' => 'required|sometimes|string|max:50',
            'billing_email' => 'required|sometimes|string|max:50',
            'profile_photo' => 'required|sometimes|file|image|mimes:jpg,jpeg,png|max:2048',
            'role_id' => 'required|integer|not_in:1|exists:'.config('tables.roles').',id',
        ]);

        // Update basic fields
        $user->first_name = $request->fullname;
        $user->billing_email = $request->billing_email;
        $user->profile_photo = $request->profile_photo;
        $user->role = $request->role_id;

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/profile_photo'), $filename);

            // Save path in database
            $user->profile_photo = 'uploads/profile_photo/'.$filename;
        }

        $user->save();

        // return response()->json([
        //     'success' => true,
        //     'message' => "Profile created successfully",
        //     'data' => $user
        // ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile created successfully',
            'data' => [
                'id' => $user->id,
                'fullname' => $user->first_name,
                'billing_email' => $user->billing_email,
                'role' => $user->role,
                'profile_photo' => $user->profile_photo ? url($user->profile_photo) : null,
            ],
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/auth/delete-user',
        operationId: 'deleteUser',
        summary: 'Delete authenticated user account',
        security: [['sanctum' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User deleted successfully.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function deleteUser(Request $request)
    {
        $auth = Auth::user();
        if (! $auth) {
            return $this->sendError('Unauthorized', [], 401);
        }
        try {
            $user = User::find($auth->id);
            DB::beginTransaction();
            $user?->currentAccessToken()?->delete();
            $user?->tokens()?->delete();
            $user->delete();
            DB::commit();

            return $this->sendResponse([], 'User deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Failed to delete user', $e->getMessage(), 500);
        }
    }

    public function getRole()
    {
        $roles = Roles::where('id', '!=', 1)->get();
        $this->sendResponse([$roles], 'Roles fetched successfully');
    }
}
