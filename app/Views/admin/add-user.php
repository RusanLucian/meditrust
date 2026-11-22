<div class="card" style="max-width:800px;margin:0 auto;">
    <div class="card-header">
        <h2 class="card-title">➕ Adaugă Utilizator Nou</h2>
        <a href="<?= route('admin.users') ?>" class="btn btn-outline btn-sm">← Înapoi la listă</a>
    </div>

    <div class="card-body">
        <form method="POST" action="<?= route('admin.users.store') ?>">
            <?= csrf_field() ?>

            <div class="form-group" style="margin-bottom:15px;">
                <label for="name" style="display:block;font-weight:600;margin-bottom:5px;">Nume complet *</label>
                <input type="text" id="name" name="name" value="<?= e(old('name')) ?>" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label for="email" style="display:block;font-weight:600;margin-bottom:5px;">Email *</label>
                <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
            </div>

            <div style="display:flex;gap:15px;margin-bottom:15px;">
                <div style="flex:1;">
                    <label for="password" style="display:block;font-weight:600;margin-bottom:5px;">Parolă *</label>
                    <input type="password" id="password" name="password" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                </div>
                <div style="flex:1;">
                    <label for="password_confirm" style="display:block;font-weight:600;margin-bottom:5px;">Confirmă Parolă *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                </div>
            </div>

            <div style="display:flex;gap:15px;margin-bottom:15px;">
                <div style="flex:1;">
                    <label for="phone" style="display:block;font-weight:600;margin-bottom:5px;">Telefon</label>
                    <input type="text" id="phone" name="phone" value="<?= e(old('phone')) ?>" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                </div>
                <div style="flex:1;">
                    <label for="user_type" style="display:block;font-weight:600;margin-bottom:5px;">Tip Utilizator *</label>
                    <select id="user_type" name="user_type" required onchange="toggleDoctorFields(this.value)" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                        <option value="patient" <?= old('user_type') === 'patient' ? 'selected' : '' ?>>🧑‍⚕️ Pacient</option>
                        <option value="doctor" <?= old('user_type') === 'doctor' ? 'selected' : '' ?>>👨‍⚕️ Medic</option>
                        <option value="admin" <?= old('user_type') === 'admin' ? 'selected' : '' ?>>🛡️ Administrator</option>
                    </select>
                </div>
            </div>

            <!-- Câmpuri speciale pentru medic -->
            <div id="doctorFields" style="display: <?= old('user_type') === 'doctor' ? 'block' : 'none' ?>;margin-top:15px;background:#f9f9f9;padding:15px;border-radius:6px;">
                <div class="form-group" style="margin-bottom:15px;">
                    <label for="specialty_id" style="display:block;font-weight:600;margin-bottom:5px;">Specialitate medicală *</label>
                    <select id="specialty_id" name="specialty_id" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;">
                        <option value="">-- Alege specialitatea --</option>
                        <?php foreach ($specialties as $spec): ?>
                            <option value="<?= (int)$spec['id'] ?>" <?= (int)old('specialty_id') === (int)$spec['id'] ? 'selected' : '' ?>>
                                <?= e($spec['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="bio" style="display:block;font-weight:600;margin-bottom:5px;">Biografie / Despre medic</label>
                    <textarea id="bio" name="bio" rows="3" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;"><?= e(old('bio')) ?></textarea>
                </div>
            </div>

            <div style="margin-top:25px;">
                <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-size:1rem;">
                    💾 Salvează Utilizatorul
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDoctorFields(type) {
    const fields = document.getElementById('doctorFields');
    fields.style.display = (type === 'doctor') ? 'block' : 'none';
}
</script>
