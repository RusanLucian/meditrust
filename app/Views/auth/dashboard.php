<main class="container" style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <div class="welcome-section">
        <h2>📋 Bun venit în MediTrust, <?= e($user['name']) ?>!</h2>
        <p>Alege o acțiune rapidă din panoul de mai jos.</p>

        <div class="user-info">
            <div class="info-item">
                <strong>📧 Email:</strong>
                <span><?= e($user['email']) ?></span>
            </div>
            <div class="info-item">
                <strong>👥 Tip cont:</strong>
                <span><?= $isDoctor ? '👨‍⚕️ Medic' : '🧑‍⚕️ Pacient' ?></span>
            </div>
            <?php if ($isDoctor && $doctorInfo): ?>
                <div class="info-item">
                    <strong>🏥 Specialitate:</strong>
                    <span><?= e($doctorInfo['specialty'] ?? 'Nespecificat') ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-grid">
        <?php if ($isPatient): ?>
            <div class="card">
                <div class="card-icon">🔍</div>
                <h3>Caută Medici</h3>
                <p>Găsește medicul potrivit pentru tine din baza noastră de date.</p>
                <a href="<?= route('doctors.index') ?>" class="card-btn">Caută Medici</a>
            </div>

            <div class="card">
                <div class="card-icon">📅</div>
                <h3>Programările Mele</h3>
                <p>Vizualizează și gestionează programările tale cu medicii.</p>
                <a href="<?= route('patient.appointments') ?>" class="card-btn">Vezi Programări</a>
            </div>

            <div class="card">
                <div class="card-icon">✍️</div>
                <h3>Lasă o Recenzie</h3>
                <p>Alege un medic și împărtășește experiența ta cu comunitatea.</p>
                <a href="<?= route('doctors.index') ?>" class="card-btn">Lasă Recenzie</a>
            </div>

            <div class="card">
                <div class="card-icon">👤</div>
                <h3>Profilul Meu</h3>
                <p>Editează datele tale de contact și profilul de pacient.</p>
                <a href="<?= route('profile') ?>" class="card-btn">Editează Profil</a>
            </div>
        <?php elseif ($isDoctor): ?>
            <div class="card">
                <div class="card-icon">👨‍⚕️</div>
                <h3>Profil Profesional</h3>
                <p>Editează biografia, specialitatea și datele profesionale.</p>
                <a href="<?= route('profile') ?>" class="card-btn">Editează Profil</a>
            </div>

            <div class="card">
                <div class="card-icon">📅</div>
                <h3>Programările Pacienților</h3>
                <p>Gestionează programările și statusul consultațiilor tale.</p>
                <a href="<?= route('doctor.appointments') ?>" class="card-btn">Vezi Programări</a>
            </div>

            <div class="card">
                <div class="card-icon">⭐</div>
                <h3>Recenziile Mele</h3>
                <p>Vezi feedback-ul și ratingurile primite de la pacienți.</p>
                <a href="<?= route('doctors.show', ['id' => $user['id']]) ?>" class="card-btn">Vezi Recenzii</a>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-icon">⚙️</div>
            <h3>Setări & Securitate</h3>
            <p>Schimbă parola și gestionează setările contului tău.</p>
            <a href="<?= route('profile') ?>" class="card-btn">Setări</a>
        </div>
    </div>
</main>
