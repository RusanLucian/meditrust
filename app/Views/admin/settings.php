<div class="admin-grid-two">
    <!-- Profil Admin -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">👤 Informații Profil Administrator</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= route('admin.settings.update') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group" style="margin-bottom:15px;">
                    <label for="name" style="display:block;font-weight:600;margin-bottom:5px;">Nume Administrator *</label>
                    <input type="text" id="name" name="name" value="<?= e($admin['name']) ?>" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                </div>

                <div class="form-group" style="margin-bottom:15px;">
                    <label for="email" style="display:block;font-weight:600;margin-bottom:5px;">Email Administrator *</label>
                    <input type="email" id="email" name="email" value="<?= e($admin['email']) ?>" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label for="phone" style="display:block;font-weight:600;margin-bottom:5px;">Telefon</label>
                    <input type="text" id="phone" name="phone" value="<?= e($admin['phone'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                </div>

                <button type="submit" class="btn btn-primary">
                    💾 Salvează Profilul
                </button>
            </form>
        </div>
    </div>

    <!-- Schimbă Parola -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🔐 Schimbare Parolă Administrator</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= route('admin.settings.update') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="change_password">

                <div class="form-group" style="margin-bottom:15px;">
                    <label for="current_password" style="display:block;font-weight:600;margin-bottom:5px;">Parola Curentă *</label>
                    <input type="password" id="current_password" name="current_password" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                </div>

                <div class="form-group" style="margin-bottom:15px;">
                    <label for="new_password" style="display:block;font-weight:600;margin-bottom:5px;">Noua Parolă * (minim 6 caractere)</label>
                    <input type="password" id="new_password" name="new_password" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label for="confirm_password" style="display:block;font-weight:600;margin-bottom:5px;">Confirmă Noua Parolă *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                </div>

                <button type="submit" class="btn btn-secondary">
                    🔄 Schimbă Parola
                </button>
            </form>
        </div>
    </div>
</div>
