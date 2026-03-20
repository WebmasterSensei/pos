<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        :root {
            --ink: #1a1410;
            --ink-muted: #6b5f54;
            --accent: #c0522a;
            --accent-deep: #8c3a1c;
            --cream: #faf7f4;
            --border: #ddd6ce;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--cream);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-card {
            background: #fff;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            color: var(--ink);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        p.subheading {
            color: var(--ink-muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            font-size: 0.75rem;
            color: var(--ink-muted);
            margin-bottom: 0.3rem;
            text-transform: uppercase;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.9rem;
            outline: none;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: var(--accent);
        }

        .form-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .form-bottom label {
            font-size: 0.8rem;
            color: var(--ink-muted);
            text-transform: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .form-bottom input[type="checkbox"] {
            width: 14px;
            height: 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            cursor: pointer;
        }

        .forgot-link {
            font-size: 0.8rem;
            color: var(--accent);
            text-decoration: none;
        }

        .forgot-link:hover {
            color: var(--accent-deep);
        }

        button.btn {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--ink);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.85rem;
            text-transform: uppercase;
            cursor: pointer;
        }

        button.btn:hover {
            background-color: #2a201a;
        }

        .login-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.8rem;
            color: var(--ink-muted);
        }

        .login-footer a {
            color: var(--accent);
            text-decoration: none;
            margin-left: 0.3rem;
        }

        .login-footer a:hover {
            color: var(--accent-deep);
        }
    </style>
</head>

<body>

    <div class="login-card">
        <h1>Welcome Back</h1>
        <p class="subheading">Log in to your account</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus>
                @error('username')
                    <div style="color: red; font-size:12px; margin-left: 2px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
                @error('password')
                    <div style="color: red; font-size:12px; margin-left: 2px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-bottom">
                <label>
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember me
                </label>
                {{-- @if (Route::has('password.request'))
                    <a class="forgot-link" href="{{ route('password.request') }}">Forgot password?</a>
                @endif --}}
            </div>

            <button type="submit" class="btn">Log In</button>
        </form>

        {{-- <div class="login-footer">
            Don't have an account? <a href="#">Create one</a>
        </div> --}}
    </div>

</body>

</html>
