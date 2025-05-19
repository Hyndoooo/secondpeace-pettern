<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="image-section">
                <img src="/images/secondpeace.jpg" alt="Second Peace Logo"> <!-- Gunakan URL langsung -->
            </div>
            <div class="form-section">
                <h2><span class="icon">🧩</span> Second Peace</h2>
                <p>Login dulu Bang User</p>

                @if ($errors->any())
                    <div style="color: red;">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login.pelanggan.process') }}" method="POST">
                    @csrf
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" required>

                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>

                    <button type="submit" class="login-btn">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
