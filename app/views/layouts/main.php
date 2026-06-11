<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Dashboard') ?> - <?= e($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: #f4f6f8;
        }

        .app-shell {
            min-height: 100vh;
        }

        .app-sidebar {
            width: 280px;
            background: #18212f;
        }

        .app-sidebar.offcanvas-lg {
            color: #fff;
        }

        .app-sidebar .offcanvas-body {
            min-height: 0;
        }

        .app-sidebar .nav-link {
            color: rgba(255, 255, 255, .72);
            border-radius: 6px;
            padding: .65rem .8rem;
            line-height: 1.25;
        }

        .app-sidebar .nav-link:hover,
        .app-sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, .12);
        }

        .app-sidebar .nav-link span {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .app-content {
            min-width: 0;
        }

        .app-header {
            height: 64px;
            min-height: 64px;
        }

        .app-title {
            min-width: 0;
        }

        .min-w-0 {
            min-width: 0;
        }

        .app-title .fw-semibold,
        .app-user-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 991.98px) {
            .app-sidebar {
                width: min(84vw, 320px);
            }

            .app-header {
                gap: .75rem;
            }

            .app-user-panel {
                min-width: 0;
            }

            .app-user-name {
                max-width: 128px;
            }

            main {
                overflow-x: hidden;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell d-lg-flex">
        <?php require view_path('partials/sidebar.php'); ?>

        <div class="app-content flex-grow-1 d-flex flex-column">
            <?php require view_path('partials/header.php'); ?>

            <main class="flex-grow-1 p-3 p-md-4">
                <?= $content ?>
            </main>

            <?php require view_path('partials/footer.php'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
