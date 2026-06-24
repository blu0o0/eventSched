<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Event Scheduling Reservation System - ISU Santiago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY') }}"></script>
    <style>
        body {
            background: #001829;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(45, 134, 89, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(30, 93, 63, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            padding: 0;
            overflow: hidden;
        }
        .login-header {
            padding: 30px 30px 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        .logo-circle {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(45, 134, 89, 0.3);
        }
        .logo-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }
        .logo-text {
            flex: 1;
        }
        .logo-text h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }
        .logo-text p {
            margin: 0;
            font-size: 12px;
            color: #6c757d;
        }
        .login-body {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            background-color: #e8f5e9;
            border: 2px solid #c8e6c9;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 15px;
            color: #1e5d3f;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border: 2px solid #2d8659;
            box-shadow: 0 0 0 0.2rem rgba(45, 134, 89, 0.15);
            outline: none;
        }
        .form-control::placeholder {
            color: #81c784;
        }
        .password-wrapper {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #2d8659;
            cursor: pointer;
            padding: 5px;
            font-size: 18px;
            z-index: 10;
            transition: color 0.3s ease;
        }
        .password-toggle:hover {
            color: #1e5d3f;
        }
        .btn-login {
            background: linear-gradient(135deg, #2d8659 0%, #1e5d3f 100%);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(45, 134, 89, 0.3);
            position: relative;
            overflow: hidden;
        }
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        .btn-login:hover::before {
            left: 100%;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #1e5d3f 0%, #2d8659 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(45, 134, 89, 0.4);
        }
        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(45, 134, 89, 0.3);
        }
        .login-footer {
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }
        .footer-text {
            font-size: 11px;
            color: #6c757d;
            margin: 0;
            line-height: 1.5;
        }
        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #1e5d3f 0%, #2d8659 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(45, 134, 89, 0.3);
        }
        .security-badge i {
            color: #ffc107;
        }
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 20px;
            color: #721c24;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="logo-container">
                <div class="logo-circle">
                    <img src="https://th.bing.com/th/id/OIP.YFWeW9_VhHAAEQFvvsJxhgAAAA?o=7&rm=3&rs=1&pid=ImgDetMain" alt="ISU Logo" class="logo-image">
                </div>
                <div class="logo-text">
                    <h3>Event Scheduling Reservation System - ISU Santiago</h3>
                    <p>Event Scheduling & Management</p>
                </div>
            </div>
        </div>

        <div class="login-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0" style="padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf
                <div class="form-group">
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="Email"
                        required 
                        autofocus
                    >
                </div>
                <div class="form-group">
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="Password"
                            required
                            style="padding-right: 50px;"
                        >
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="bi bi-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                <button type="submit" class="btn btn-login">
                    Login
                </button>
            </form>
        </div>

        <div class="login-footer">
            <p class="footer-text">
                Event Scheduling Reservation System - ISU Santiago. Copyright © {{ date('Y') }}. Developed by ISU Team.
            </p>
            <div class="security-badge">
                <i class="bi bi-shield-check"></i>
                <span>VERIFIED & SECURED</span>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password visibility toggle
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('passwordIcon');

        passwordToggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('bi-eye');
                passwordIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('bi-eye-slash');
                passwordIcon.classList.add('bi-eye');
            }
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ env('RECAPTCHA_SITE_KEY') }}', {action: 'login'}).then(function(token) {
                    document.getElementById('recaptcha_token').value = token;
                    document.querySelector('form').submit();
                });
            });
        });
    </script>
</body>
</html>

