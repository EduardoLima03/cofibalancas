<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #155f4a 0%, #0f4a38 100%);
            padding: 1rem;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,.25);
        }
        .login-logo {
            width: 72px;
            height: 72px;
            border-radius: 1rem;
            background: #155f4a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto;
        }
        .btn-primary {
            background: #155f4a;
            border-color: #155f4a;
            min-height: 48px;
        }
        .btn-primary:hover, .btn-primary:focus {
            background: #0f4a38;
            border-color: #0f4a38;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-body p-4 p-md-5">
            <div class="login-logo mb-3">
                <i class="bi bi-basket3-fill"></i>
            </div>
            <h1 class="text-center fs-4 fw-bold mb-1">{{ config('app.name') }}</h1>
            <p class="text-center text-muted small mb-4">Sistema de conferência de peso</p>

            @if($errors->any())
                <div class="alert alert-danger small py-2">
                    @foreach($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label">Usuário</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="seu.usuario">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">Lembrar-me</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                </button>
            </form>
        </div>
    </div>
    <footer class="mt-4 text-center">
        <small class="text-light opacity-75">
            {{ config('app.name') }} v{{ config('app.version') }} &middot;
            Desenvolvido por
            <a href="https://github.com/EduardoLima03" target="_blank" rel="noopener" class="text-light">CL Dev</a>
        </small>
    </footer>
</body>
</html>