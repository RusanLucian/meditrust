<main class="container auth-shell auth-shell-register">
    <section class="auth-info">
        <img src="<?= asset('img/meditrust-logo.png') ?>" alt="MediTrust Logo" class="auth-logo">
        <h1>Alătură-te comunității MediTrust</h1>
        <p>Creează-ți un cont pentru a programa consultații medicale sau pentru a-ți oferi serviciile medicale pacienților.</p>
        <a href="<?= route('home') ?>" class="auth-back-link">← Înapoi la pagina principală</a>
    </section>

    <section class="auth-card">
        <div class="auth-card-header">
            <h2>📝 Înregistrare Cont</h2>
            <p>Completează datele de mai jos pentru a începe.</p>
        </div>

        <form method="POST" action="<?= route('register') ?>" class="auth-form" id="registerForm">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">👤 Nume complet *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="ex: Dr. Ion Popescu sau Maria Ionescu"
                    value="<?= e(old('name')) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">📧 Email *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="email@exemplu.ro"
                    value="<?= e(old('email')) ?>"
                    required
                >
            </div>

            <div class="form-row" style="display:flex;gap:15px;">
                <div class="form-group" style="flex:1;">
                    <label for="password">🔐 Parola *</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Minim 6 caractere"
                        required
                    >
                </div>

                <div class="form-group" style="flex:1;">
                    <label for="confirm_password">🔐 Confirmă Parola *</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Reintrodu parola"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="phone">📱 Telefon</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    placeholder="07xxxxxxxx"
                    value="<?= e(old('phone')) ?>"
                >
            </div>

            <div class="form-group">
                <label for="user_type">👥 Tip cont *</label>
                <select id="user_type" name="user_type" required onchange="toggleDoctorFields(this.value)">
                    <option value="patient" <?= old('user_type') === 'patient' ? 'selected' : '' ?>>🧑‍⚕️ Pacient</option>
                    <option value="doctor" <?= old('user_type') === 'doctor' ? 'selected' : '' ?>>👨‍⚕️ Medic</option>
                </select>
            </div>

            <div id="doctorFields" style="display: <?= old('user_type') === 'doctor' ? 'block' : 'none' ?>;">
                <div class="form-group">
                    <label for="specialty_id">🏥 Specialitate medicală *</label>
                    <select id="specialty_id" name="specialty_id">
                        <option value="">-- Alege specialitatea --</option>
                        <?php foreach ($specialties as $spec): ?>
                            <option value="<?= e($spec['id']) ?>" <?= (int)old('specialty_id') === (int)$spec['id'] ? 'selected' : '' ?>>
                                <?= e($spec['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="bio">📋 Despre tine / Biografie medicală</label>
                    <textarea id="bio" name="bio" rows="3" placeholder="Scurtă prezentare profesională, experiență, competențe..."><?= e(old('bio')) ?></textarea>
                </div>
            </div>

            <button type="submit" class="auth-submit">Înregistrează-te</button>
        </form>

        <div class="auth-footer">
            Ai deja cont? <a href="<?= route('login') ?>">Conectează-te aici</a>
        </div>
    </section>
</main>

<script>
function toggleDoctorFields(userType) {
    const docFields = document.getElementById('doctorFields');
    const specSelect = document.getElementById('specialty_id');
    if (userType === 'doctor') {
        docFields.style.display = 'block';
        specSelect.required = true;
    } else {
        docFields.style.display = 'none';
        specSelect.required = false;
    }
}
</script>
