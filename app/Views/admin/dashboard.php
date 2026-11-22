<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon icon-blue">👥</div>
        <div class="stat-info">
            <span class="stat-number"><?= (int)$stats['totalUsers'] ?></span>
            <span class="stat-label">Total Utilizatori</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-teal">👨‍⚕️</div>
        <div class="stat-info">
            <span class="stat-number"><?= (int)$stats['totalDoctors'] ?></span>
            <span class="stat-label">Medici Activi</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-green">🧑‍⚕️</div>
        <div class="stat-info">
            <span class="stat-number"><?= (int)$stats['totalPatients'] ?></span>
            <span class="stat-label">Pacienți Înregistrați</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-purple">📅</div>
        <div class="stat-info">
            <span class="stat-number"><?= (int)$stats['totalAppointments'] ?></span>
            <span class="stat-label">Total Programări</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-yellow">⏳</div>
        <div class="stat-info">
            <span class="stat-number"><?= (int)$stats['pendingAppointments'] ?></span>
            <span class="stat-label">Programate / În așteptare</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-orange">⭐</div>
        <div class="stat-info">
            <span class="stat-number"><?= (int)$stats['totalReviews'] ?></span>
            <span class="stat-label">Recenzii Pacienți</span>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="admin-actions-bar" style="display:flex;gap:12px;margin-bottom:25px;">
    <a href="<?= route('admin.users.create') ?>" class="btn btn-primary">➕ Adaugă Utilizator Nou</a>
    <a href="<?= route('admin.doctors') ?>" class="btn btn-secondary">👨‍⚕️ Gestionează Medici</a>
    <a href="<?= route('admin.appointments') ?>" class="btn btn-secondary">📅 Toate Programările</a>
</div>

<!-- Main Dashboard Grid -->
<div class="admin-grid-two">
    <!-- Recent Appointments Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📅 Programări Recente</h2>
            <a href="<?= route('admin.appointments') ?>" class="card-link">Vezi toate →</a>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($recentApps)): ?>
                <p style="padding:20px;color:#777;margin:0;">Nu există programări recente.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Pacient</th>
                            <th>Medic</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentApps as $app): ?>
                            <tr>
                                <td><?= date('d.m.Y H:i', strtotime($app['appointment_date'])) ?></td>
                                <td><?= e($app['patient_name']) ?></td>
                                <td>Dr. <?= e($app['doctor_name']) ?></td>
                                <td>
                                    <?php if ($app['status'] === 'completed'): ?>
                                        <span class="badge badge-success">Finalizată</span>
                                    <?php elseif ($app['status'] === 'cancelled'): ?>
                                        <span class="badge badge-danger">Anulată</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Programată</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Users Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">👥 Utilizatori Înregistrați Recent</h2>
            <a href="<?= route('admin.users') ?>" class="card-link">Vezi toți →</a>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($recentUsers)): ?>
                <p style="padding:20px;color:#777;margin:0;">Nu există utilizatori.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nume</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Acțiune</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                            <tr>
                                <td><strong><?= e($u['name']) ?></strong></td>
                                <td><?= e($u['email']) ?></td>
                                <td>
                                    <?php if ($u['user_type'] === 'admin'): ?>
                                        <span class="badge badge-purple">Admin</span>
                                    <?php elseif ($u['user_type'] === 'doctor'): ?>
                                        <span class="badge badge-teal">Medic</span>
                                    <?php else: ?>
                                        <span class="badge badge-blue">Pacient</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= route('admin.users.edit', ['id' => $u['id']]) ?>" class="btn">
                                        Editează
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
