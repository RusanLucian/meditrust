<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'MediTrust - Evaluare Medici & Programări') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="<?= e($bodyClass ?? '') ?>">
    <header>
        <div class="header-logo">
            <a href="<?= route('home') ?>" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;">
                <img src="<?= asset('img/meditrust-logo.png') ?>" alt="MediTrust" class="logo">
                <h1 style="margin:0;font-size:1.5rem;">MediTrust</h1>
            </a>
        </div>

        <nav class="header-links">

            <?php if ($user = auth()): ?>
                <span>👋 Salut, <?= e($user['name']) ?></span>
                
                <?php if ($user['user_type'] === 'admin'): ?>
                    <a href="<?= route('admin.dashboard') ?>">🛡️ Panou Admin</a>
                <?php elseif ($user['user_type'] === 'doctor'): ?>
                   
                <?php else: ?>
                    
                <?php endif; ?>

                
                <a href="<?= route('logout') ?>">🚪 Deconectare</a>
            <?php else: ?>
                <a href="<?= route('login') ?>">🔓 Conectare</a>
                <a href="<?= route('register') ?>">📝 Înregistrare</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container flash-container" style="max-width: 1200px; margin: 15px auto 0 auto; padding: 0 20px;">
        <?php if ($success = flash('success')): ?>
            <div class="alert alert-success" style="background:#e8f5e9;color:#2e7d32;padding:12px 16px;border-radius:6px;margin-bottom:15px;border:1px solid #c8e6c9;">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error = flash('error')): ?>
            <div class="alert alert-danger" style="background:#ffebee;color:#c62828;padding:12px 16px;border-radius:6px;margin-bottom:15px;border:1px solid #ffcdd2;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>
    </div>

    <?= $content ?>

</body>
</html>
