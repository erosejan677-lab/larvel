<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px 30px;
            width: 100%;
            max-width: 400px;
        }

        .container h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.3s;
        }

        input[type="email"]:read-only {
            background-color: #f9f9f9;
            color: #888;
        }

        input[type="password"]:focus {
            border-color: #007bff;
        }

        .btn {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: #d9534f;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Set New Password</h1>
    <form action="{{ route('password.update') }}" method="POST" id="new-password-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ $email }}" readonly>
        </div>
        <div class="form-group">
            <label for="new-password">New Password</label>
            <input type="password" id="new-password" name="password" required>
            <div class="error-message" id="password-error"></div>
        </div>
        <div class="form-group">
            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" name="password_confirmation" required>
            <div class="error-message" id="confirm-password-error"></div>
        </div>
        <button type="submit" class="btn">Set Password</button>
{{--        @error('password')--}}
{{--        <p class="text-danger"> {{ $message }}</p>--}}
{{--        @enderror--}}
    </form>
</div>

<script src="{{ asset('dist/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

{{--<script>--}}
{{--    document.getElementById('new-password-form').addEventListener('submit', function(event) {--}}
{{--        event.preventDefault();--}}

{{--        const password = document.getElementById('new-password').value;--}}
{{--        const confirmPassword = document.getElementById('confirm-password').value;--}}
{{--        const passwordError = document.getElementById('password-error');--}}
{{--        const confirmPasswordError = document.getElementById('confirm-password-error');--}}

{{--        passwordError.textContent = '';--}}
{{--        confirmPasswordError.textContent = '';--}}

{{--        if (password.length < 8) {--}}
{{--            passwordError.textContent = 'Password must be at least 8 characters long.';--}}
{{--            return;--}}
{{--        }--}}

{{--        if (password !== confirmPassword) {--}}
{{--            confirmPasswordError.textContent = 'Passwords do not match.';--}}
{{--            return;--}}
{{--        }--}}

{{--        alert('Password successfully set!');--}}
{{--    });--}}
{{--</script>--}}
</body>
</html>
