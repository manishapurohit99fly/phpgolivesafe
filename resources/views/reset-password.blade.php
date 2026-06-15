<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Reset Password</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" href="{{ asset('assets/img/favicon.ico') }}" type="image/png" />
        <style>
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                font-family:
                    'Inter',
                    -apple-system,
                    BlinkMacSystemFont,
                    'Segoe UI',
                    Roboto,
                    sans-serif;
                background: #f8f9fa;
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }

            .container {
                background: #ffffff;
                border: 1px solid #e9ecef;
                padding: 40px 35px;
                border-radius: 24px;
                box-shadow:
                    0 20px 40px rgba(0, 0, 0, 0.08),
                    0 1px 3px rgba(0, 0, 0, 0.03);
                max-width: 420px;
                width: 100%;
                /* animation: slideUp 0.8s ease-out; */
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .header {
                text-align: center;
                margin-bottom: 32px;
            }

            .logo {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
                display: block;
                object-fit: contain;
            }

            @keyframes pulse {
                0%,
                100% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.05);
                }
            }

            h2 {
                color: #2d3748;
                margin-bottom: 8px;
                font-size: 28px;
                font-weight: 700;
                letter-spacing: -0.025em;
            }

            .subtitle {
                color: #718096;
                font-size: 15px;
                font-weight: 500;
            }

            .alert {
                background: linear-gradient(135deg, #fed7d7, #feb2b2);
                border: 1px solid #fc8181;
                padding: 16px 18px;
                margin-bottom: 24px;
                color: #c53030;
                font-size: 14px;
                border-radius: 12px;
                position: relative;
                animation: shake 0.5s ease-in-out;
            }

            @keyframes shake {
                0%,
                100% {
                    transform: translateX(0);
                }
                25% {
                    transform: translateX(-5px);
                }
                75% {
                    transform: translateX(5px);
                }
            }

            .alert::before {
                content: '⚠️';
                margin-right: 8px;
            }

            form {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            .input-group {
                position: relative;
            }

            input[type='password'] {
                width: 100%;
                padding: 16px 20px;
                border: 2px solid #e2e8f0;
                border-radius: 12px;
                font-size: 16px;
                background: #ffffff;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                font-weight: 500;
            }

            input[type='password']:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                transform: translateY(-2px);
            }

            input[type='password']:hover {
                border-color: #cbd5e0;
            }

            .input-group::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 50%;
                width: 0;
                height: 2px;
                background: linear-gradient(90deg, #667eea, #764ba2);
                transition: all 0.3s ease;
                transform: translateX(-50%);
            }

            .input-group:focus-within::after {
                width: 100%;
            }

            .invalid-feedback {
                color: #e53e3e;
                font-size: 13px;
                margin-top: 6px;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .invalid-feedback::before {
                content: '❌';
                font-size: 12px;
            }

            button {
                padding: 16px 24px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 12px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                overflow: hidden;
                margin-top: 8px;
            }

            button::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(
                    90deg,
                    transparent,
                    rgba(255, 255, 255, 0.2),
                    transparent
                );
                transition: left 0.5s;
            }

            button:hover::before {
                left: 100%;
            }

            button:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 24px rgba(102, 126, 234, 0.4);
            }

            button:active {
                transform: translateY(0);
            }

            .security-note {
                margin-top: 24px;
                padding: 16px;
                background: rgba(102, 126, 234, 0.05);
                border-radius: 12px;
                border-left: 4px solid #667eea;
                font-size: 14px;
                color: #4a5568;
            }

            .security-note::before {
                content: '🛡️';
                margin-right: 8px;
            }

            @media (max-width: 480px) {
                .container {
                    padding: 30px 25px;
                    margin: 10px;
                }

                h2 {
                    font-size: 24px;
                }

                .logo {
                    width: 60px;
                    height: 60px;
                }
            }
            .toggle-password {
                position: absolute;
                top: 50%;
                right: 18px;
                transform: translateY(-50%);
                cursor: pointer;
                font-size: 18px;
                color: #718096;
                user-select: none;
            }

            .toggle-password:hover {
                color: #4a5568;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <center>
                    <img
                        src="{{ asset('assets/images/sidebarlogo.svg') }}"
                        alt="logo"
                        style="width: 100px"
                        width="70px"
                    />
                </center>
                <h2>Reset Password</h2>
                <p class="subtitle">Create a new secure password for your account</p>
            </div>

            @if ($isExpired)
                <!-- Show expired message if token is expired -->
                <div class="alert">This password reset link has expired.</div>
            @else
                <!-- Show form if token is valid -->
                {{-- @if ($errors->any())
                    <div class="alert">
                        @foreach ($errors->all() as $error)
                            )
                            {{ $error }}
                            <br />
                        @endforeach
                    </div>
                @endif --}}

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}" />
                    <input type="hidden" name="email" value="{{ $email }}" />

                    <div class="input-group">
                        <input type="password" name="password" placeholder="New Password" />
                    </div>

                    <div class="input-group">
                        <input
                            type="password"
                            name="password_confirmation"
                            placeholder="Confirm Password"
                        />
                    </div>

                    <button type="submit">Reset Password</button>
                </form>

                <div class="security-note">
                    Your password must be at least 8 characters long and include a mix of letters,
                    numbers, and symbols.
                </div>
            @endif
        </div>
    </body>
</html>
