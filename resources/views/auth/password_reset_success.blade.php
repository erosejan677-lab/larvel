<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Successful</title>
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
            text-align: center;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px 40px;
            width: 100%;
            max-width: 400px;
        }

        .container h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #28a745;
        }

        .container p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }

        .container a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .container a:hover {
            background-color: #0056b3;
        }

        .success-icon {
            font-size: 50px;
            color: #28a745;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="success-icon">&#10004;</div>
    @if(Session::has('status'))
        <div id="reset-success" class="alert alert-success">
            {{ Session::get('status') }}
        </div>
    @endif
    <h1>Password Reset Successful</h1>
    <p>Your password has been reset successfully. You can now log in with your new password.</p>
    <a href="/login">Go to Login</a>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var resetSuccess = document.getElementById('reset-success');
        setTimeout(function(){
            resetSuccess.fadeOut('slow');
        }, 3000);
    });
</script>
</body>
</html>
