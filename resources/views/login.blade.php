<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            margin-bottom: 1rem;
            font-size: 1.5rem;
            color: #333;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background-color: #007bff;
            border: none;
            color: #fff;
            padding: 0.75rem;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .alert ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .alert li {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>

        @if ($errors->any())
            <div class="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>
</html>
