<main class="container" style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <a href="<?= route('dashboard') ?>" class="back-btn">← Înapoi la Dashboard</a>

    <div class="profile-container" style="margin-top: 20px;">
        <div class="profile-card">
            <h2>👤 Editare Profil</h2>
            <form method="POST" action="<?= route('profile') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group">
                    <label for="name">📝 Nume Complet:</label>
                    <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">📧 Email:</label>
                    <input type="email" id="email" name="email" value="<?= e($user['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">📱 Telefon:</label>
                    <input type="tel" id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>">
                </div>

                <?php if ($isDoctor): ?>
                    <div class="form-group">
                        <label for="bio">ℹ️ Despre mine / Biografie medicală:</label>
                        <textarea id="bio" name="bio" rows="4"><?= e($doctorInfo['bio'] ?? '') ?></textarea>
                    </div>
                <?php endif; ?>

                <button type="submit" class="submit-btn">💾 Salvează Modificări</button>
            </form>
        </div>

        <div class="password-card">
            <h2>🔐 Schimbă Parola</h2>
            <form method="POST" action="<?= route('profile') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label for="old_password">Parola Veche:</label>
                    <input type="password" id="old_password" name="old_password" placeholder="Parola curentă" required>
                </div>

                <div class="form-group">
                    <label for="new_password">Parola Nouă:</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Minim 6 caractere" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmă Parola Nouă:</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Reintrodu noua parolă" required>
                </div>

                <button type="submit" class="submit-btn">🔄 Schimbă Parola</button>
            </form>
        </div>
    </div>
</main>
