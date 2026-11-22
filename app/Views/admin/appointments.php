<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <h2 class="card-title">📅 Toate Programările (<?= count($appointments) ?>)</h2>
    </div>

    <div class="card-body">
        <!-- Filtrare -->
        <form method="GET" action="<?= route('admin.appointments') ?>" class="admin-filters-form" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <input
                type="text"
                name="search"
                placeholder="🔍 Caută după pacient sau medic..."
                value="<?= e($filterSearch) ?>"
                style="flex:1;min-width:220px;padding:8px 12px;border:1px solid #ccc;border-radius:4px;"
            >

            <select name="status" onchange="this.form.submit()" style="padding:8px 12px;border:1px solid #ccc;border-radius:4px;">
                <option value="">-- Toate statusurile --</option>
                <option value="scheduled" <?= $filterStatus === 'scheduled' ? 'selected' : '' ?>>Programate</option>
                <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Finalizate</option>
                <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Anulate</option>
            </select>

            <button type="submit" class="btn btn-secondary">Filtrează</button>

            <?php if (!empty($filterSearch) || !empty($filterStatus)): ?>
                <a href="<?= route('admin.appointments') ?>" class="btn btn-outline">Resetează</a>
            <?php endif; ?>
        </form>

        <?php if (empty($appointments)): ?>
            <p style="text-align:center;color:#666;padding:30px 0;">Nu au fost găsite programări conform filtrelor.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data & Ora</th>
                            <th>Pacient</th>
                            <th>Medic</th>
                            <th>Specialitate</th>
                            <th>Status Curent</th>
                            <th style="text-align:right;">Actualizează Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $app): ?>
                            <tr>
                                <td><strong><?= date('d.m.Y H:i', strtotime($app['appointment_date'])) ?></strong></td>
                                <td>
                                    <div><strong><?= e($app['patient_name']) ?></strong></div>
                                    <div style="font-size:0.85rem;color:#777;"><?= e($app['patient_email']) ?></div>
                                </td>
                                <td>Dr. <?= e($app['doctor_name']) ?></td>
                                <td><span class="badge badge-teal"><?= e($app['specialty'] ?? 'N/A') ?></span></td>
                                <td>
                                    <?php if ($app['status'] === 'completed'): ?>
                                        <span class="badge badge-success">Finalizată</span>
                                    <?php elseif ($app['status'] === 'cancelled'): ?>
                                        <span class="badge badge-danger">Anulată</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Programată</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <form method="POST" action="<?= route('admin.appointments.status', ['id' => $app['id']]) ?>" style="display:inline-flex;gap:6px;align-items:center;">
                                        <?= csrf_field() ?>
                                        <select name="status" style="padding:4px 8px;border:1px solid #ccc;border-radius:4px;font-size:0.85rem;">
                                            <option value="scheduled" <?= $app['status'] === 'scheduled' ? 'selected' : '' ?>>Programată</option>
                                            <option value="completed" <?= $app['status'] === 'completed' ? 'selected' : '' ?>>Finalizată</option>
                                            <option value="cancelled" <?= $app['status'] === 'cancelled' ? 'selected' : '' ?>>Anulată</option>
                                        </select>
                                        <button type="submit" class="btn btn-xs btn-primary">
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
</div>
