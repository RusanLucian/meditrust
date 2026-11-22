<main class="container" style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <?php if (auth()): ?>
        <a href="<?= route('dashboard') ?>" class="back-btn">← Înapoi la Dashboard</a>
    <?php else: ?>
        <a href="<?= route('home') ?>" class="back-btn">← Înapoi la pagina principală</a>
    <?php endif; ?>

    <h1 style="margin-top: 15px;">🏥 Lista Medici</h1>

    <div class="filters">
        <h2>Filtrare</h2>

        <form method="GET" action="<?= route('doctors.index') ?>" class="filter-form" autocomplete="off">
            <div class="filter-group">
                <input
                    type="text"
                    name="search"
                    placeholder="🔍 Caută medic după nume..."
                    value="<?= e($filters['search']) ?>"
                    autocomplete="off"
                >
            </div>

            <div class="filter-group">
                <select id="specialty" name="specialty" onchange="this.form.submit()">
                    <option value="">-- Toate specialitățile --</option>
                    <?php foreach ($specialties as $spec): ?>
                        <option
                            value="<?= (int)$spec['id'] ?>"
                            <?= (string)$filters['specialty'] === (string)$spec['id'] ? 'selected' : '' ?>
                        >
                            <?= e($spec['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <select id="rating" name="rating" onchange="this.form.submit()">
                    <option value="">-- Rating minim --</option>
                    <option value="3" <?= (string)$filters['rating'] === '3' ? 'selected' : '' ?>>⭐ 3+</option>
                    <option value="4" <?= (string)$filters['rating'] === '4' ? 'selected' : '' ?>>⭐ 4+</option>
                    <option value="5" <?= (string)$filters['rating'] === '5' ? 'selected' : '' ?>>⭐ 5</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">🔍 Caută</button>

            <?php if (!empty($filters['specialty']) || !empty($filters['rating']) || !empty($filters['search'])): ?>
                <a href="<?= route('doctors.index') ?>" class="back-btn" style="display:inline-block;margin:0;">
                    Resetează filtrele
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="doctors-grid">
        <?php if (empty($doctors)): ?>
            <div class="no-data">
                <div class="no-data-icon">👨‍⚕️</div>
                <p>Nu am găsit medici pentru filtrele selectate.</p>
            </div>
        <?php else: ?>
            <?php foreach ($doctors as $doctor): ?>
                <?php
                $bioPreview = trim($doctor['bio'] ?? '');
                $bioText = $bioPreview !== '' ? mb_strimwidth($bioPreview, 0, 100, '...') : 'Fără descriere disponibilă.';
                ?>
                <div class="doctor-card">
                    <div class="doctor-header doctor-header-with-avatar">
                        <div class="doctor-card-avatar">
                            <?php if (!empty($doctor['avatar'])): ?>
                                <img
                                    class="doctor-card-avatar-img"
                                    src="<?= asset('img/' . ltrim($doctor['avatar'], '/')) ?>"
                                    alt="<?= e($doctor['name']) ?>"
                                >
                            <?php else: ?>
                                👨‍⚕️
                            <?php endif; ?>
                        </div>

                        <div class="doctor-card-title">
                            <h3><?= e($doctor['name']) ?></h3>
                            <p><?= e($doctor['specialty'] ?? 'Specialitate indisponibilă') ?></p>
                            <p>⭐ <?= number_format((float)$doctor['avg_rating'], 1) ?>/5 (<?= (int)$doctor['review_count'] ?> recenzii)</p>
                        </div>
                    </div>

                    <div class="doctor-body">
                        <div class="doctor-info">
                            <strong>📞 Telefon</strong>
                            <?= e($doctor['phone'] ?? 'N/A') ?>
                        </div>
                        <div class="doctor-info">
                            <strong>📧 Email</strong>
                            <?= e($doctor['email']) ?>
                        </div>
                        <div class="doctor-info">
                            <strong>ℹ️ Descriere</strong>
                            <?= e($bioText) ?>
                        </div>
                    </div>

                    <div class="doctor-footer">
                        <a href="<?= route('doctors.show', ['id' => $doctor['id']]) ?>" class="btn-view">
                            Vezi Detalii
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
