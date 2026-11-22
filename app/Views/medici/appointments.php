
<main class="container doctor-appointments">
    <a href="<?= route('dashboard') ?>" class="back-btn">← Înapoi la Dashboard</a>
    <h1>📅 Programările Pacienților Tăi</h1>

    <!-- PROGRAMĂRI PROGRAMATE / VIITOARE -->
    <div class="appointments-section">
        <h2>⏳ Consultații Active & Viitoare (<?= count($upcoming) ?>)</h2>

        <?php if (empty($upcoming)): ?>
            <div class="no-data">
                <p>Nu ai consultații active în perioada următoare.</p>
            </div>
        <?php else: ?>
            <div class="appointments-grid">
                <?php foreach ($upcoming as $app): ?>
                    <div class="appointment-card">
                        <div class="appointment-card-header">
                            <h3>👤 <?= e($app['patient_name']) ?></h3>
                            <span class="status-badge">
                                <?= ucfirst(e($app['status'])) ?>
                            </span>
                        </div>

                        <div class="appointment-details">
                            <p><strong>📆 Data:</strong> <?= date('d.m.Y', strtotime($app['appointment_date'])) ?></p>
                            <p><strong>⏰ Ora:</strong> <?= date('H:i', strtotime($app['appointment_date'])) ?></p>
                            <p><strong>📱 Telefon:</strong> <?= e($app['patient_phone'] ?? 'Nespecificat') ?></p>
                            <p><strong>📧 Email:</strong> <?= e($app['patient_email']) ?></p>

                            <?php if (!empty($app['notes'])): ?>
                                <p class="appointment-note">
                                    <strong>📝 Notă pacient:</strong> "<?= e($app['notes']) ?>"
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Formular actualizare status -->
                        <form class="status-form" method="POST" action="<?= route('doctor.appointments.status', ['id' => $app['id']]) ?>">
                            <?= csrf_field() ?>
                            <select name="status">
                                <option value="scheduled" <?= $app['status'] === 'scheduled' ? 'selected' : '' ?>>Programat</option>
                                <option value="completed" <?= $app['status'] === 'completed' ? 'selected' : '' ?>>Finalizat</option>
                                <option value="cancelled" <?= $app['status'] === 'cancelled' ? 'selected' : '' ?>>Anulat</option>
                            </select>
                            <button type="submit" class="save-btn">
                                Salvează
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ISTORIC CONSULTAȚII -->
    <div class="appointments-history-section">
        <h2>📜 Istoric Consultații (<?= count($past) ?>)</h2>

        <?php if (empty($past)): ?>
            <p>Nu ai consultații finalizate sau anulate în istoric.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data & Ora</th>
                            <th>Pacient</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Schimbă Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($past as $app): ?>
                            <tr>
                                <td><?= date('d.m.Y H:i', strtotime($app['appointment_date'])) ?></td>
                                <td><?= e($app['patient_name']) ?></td>
                                <td><?= e($app['patient_phone'] ?? $app['patient_email']) ?></td>
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
                                    <form class="history-status-form" method="POST" action="<?= route('doctor.appointments.status', ['id' => $app['id']]) ?>">
                                        <?= csrf_field() ?>
                                        <select name="status">
                                            <option value="completed" <?= $app['status'] === 'completed' ? 'selected' : '' ?>>Finalizată</option>
                                            <option value="cancelled" <?= $app['status'] === 'cancelled' ? 'selected' : '' ?>>Anulată</option>
                                            <option value="scheduled" <?= $app['status'] === 'scheduled' ? 'selected' : '' ?>>Programată</option>
                                        </select>
                                        <button type="submit" class="history-save-btn">
                                            OK
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>
