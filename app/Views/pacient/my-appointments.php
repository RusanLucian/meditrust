
<main class="container appointments-page">
    <div class="appointments-header">
        <div>
            <a href="<?= route('dashboard') ?>" class="back-btn">← Înapoi la Dashboard</a>
            <h1>📅 Programările Mele</h1>
        </div>
        <a href="<?= route('appointments.book') ?>" class="btn">
            ➕ Programare Nouă
        </a>
    </div>

    <!-- PROGRAMĂRI VIITOARE -->
    <div class="appointments-section">
        <h2>⏳ Programări Viitoare (<?= count($upcoming) ?>)</h2>

        <?php if (empty($upcoming)): ?>
            <div class="no-data">
                <p>Nu ai nicio consultație programată în viitor.</p>
            </div>
        <?php else: ?>
            <div class="appointments-grid">
                <?php foreach ($upcoming as $app): ?>
                    <div class="appointment-card">
                        <div class="appointment-card-header">
                            <div>
                                <h3>Dr. <?= e($app['doctor_name']) ?></h3>
                                <p>🏥 <?= e($app['specialty'] ?? 'Specialitate generală') ?></p>
                            </div>
                            <span class="status-badge">
                                Programat
                            </span>
                        </div>

                        <div class="appointment-details">
                            <p><strong>📆 Data:</strong> <?= date('d.m.Y', strtotime($app['appointment_date'])) ?></p>
                            <p><strong>⏰ Ora:</strong> <?= date('H:i', strtotime($app['appointment_date'])) ?></p>
                            <p><strong>📞 Telefon medic:</strong> <?= e($app['doctor_phone'] ?? 'N/A') ?></p>
                            <?php if (!empty($app['notes'])): ?>
                                <p class="appointment-note"><strong>📝 Notă:</strong> "<?= e($app['notes']) ?>"</p>
                            <?php endif; ?>
                        </div>

                        <form method="POST" action="<?= route('patient.appointments.cancel', ['id' => $app['id']]) ?>" onsubmit="return confirm('Sigur dorești să anulezi această programare?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="cancel-btn">
                                ❌ Anulează Programarea
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ISTORIC PROGRAMĂRI -->
    <div class="appointments-history-section">
        <h2>📜 Istoric Consultații & Anulări (<?= count($past) ?>)</h2>

        <?php if (empty($past)): ?>
            <p>Nu ai consultații în istoric.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data & Ora</th>
                            <th>Medic</th>
                            <th>Specialitate</th>
                            <th>Status</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($past as $app): ?>
                            <tr>
                                <td><?= date('d.m.Y H:i', strtotime($app['appointment_date'])) ?></td>
                                <td>Dr. <?= e($app['doctor_name']) ?></td>
                                <td><?= e($app['specialty'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if ($app['status'] === 'completed'): ?>
                                        <span class="status-completed">✓ Finalizată</span>
                                    <?php elseif ($app['status'] === 'cancelled'): ?>
                                        <span class="status-cancelled">✗ Anulată</span>
                                    <?php else: ?>
                                        <span class="status-expired">Expirată</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= route('doctors.show', ['id' => $app['doctor_id']]) ?>">
                                        Profil Medic →
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>
