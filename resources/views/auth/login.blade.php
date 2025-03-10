<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }
        div {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .errors p {
            color: red;
        }
    </style>
</head>
<body>
    <form method="POST" action="{{ url('login') }}">
        @csrf
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required autocomplete="username">
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <div>
            <button type="submit">Login</button>
        </div>
        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
    </form>
</body>
</html>
