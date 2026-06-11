<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> - <?= e(config('app.name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light min-vh-100 d-flex align-items-center">
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-1"><?= e(config('app.name')) ?></h1>
                        <p class="text-secondary mb-4">Login admin</p>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= e($error) ?></div>
                        <?php endif; ?>

                        <form method="post" action="<?= e(url('/login')) ?>" class="d-flex flex-column gap-3">
                            <div>
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                            <div>
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
