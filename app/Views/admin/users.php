<div class="card">
    <div class="card-header">
        <h2 class="card-title">👥 Listă Utilizatori (<?= count($users) ?>)</h2>
        <a href="<?= route('admin.users.create') ?>" class="btn btn-primary">➕ Adaugă Utilizator Nou</a>
    </div>

    <div class="card-body">
        <!-- Filtrare -->
        <form method="GET" action="<?= route('admin.users') ?>" class="admin-filters-form"
            style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <input type="text" name="search" placeholder="🔍 Caută după nume sau email..."
                value="<?= e($filterSearch) ?>"
                style="flex:1;min-width:220px;padding:8px 12px;border:1px solid #ccc;border-radius:4px;">

            <select name="type" onchange="this.form.submit()"
                style="padding:8px 12px;border:1px solid #ccc;border-radius:4px;">
                <option value="">-- Toate rolurile --</option>
                <option value="patient" <?= $filterType === 'patient' ? 'selected' : '' ?>>Pacienți</option>
                <option value="doctor" <?= $filterType === 'doctor' ? 'selected' : '' ?>>Medici</option>
                <option value="admin" <?= $filterType === 'admin' ? 'selected' : '' ?>>Administratori</option>
            </select>

            <button type="submit" class="btn btn-secondary">Filtrează</button>

            <?php if (!empty($filterSearch) || !empty($filterType)): ?>
                <a href="<?= route('admin.users') ?>" class="btn btn-outline">Resetează</a>
            <?php endif; ?>
        </form>

        <!-- Tabel utilizatori -->
        <?php if (empty($users)): ?>
            <p style="text-align:center;color:#666;padding:30px 0;">Nu au fost găsiți utilizatori conform filtrelor.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nume</th>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Rol</th>
                            <th>Specialitate</th>
                            <th style="text-align:right;">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <?php if ($u['user_type'] === 'admin')
                                continue; ?>
                            <tr>
                                <td>#<?= (int) $u['id'] ?></td>
                                <td><strong><?= e($u['name']) ?></strong></td>
                                <td><?= e($u['email']) ?></td>
                                <td><?= e($u['phone'] ?? '-') ?></td>
                                <td>
                                    <?php if ($u['user_type'] === 'admin'): ?>
                                        <span class="badge badge-purple">Admin</span>
                                    <?php elseif ($u['user_type'] === 'doctor'): ?>
                                        <span class="badge badge-teal">Medic</span>
                                    <?php else: ?>
                                        <span class="badge badge-blue">Pacient</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($u['specialty'] ?? '-') ?></td>
                                <td class="actiuni">
                                    <a href="<?= route('admin.users.edit', ['id' => $u['id']]) ?>" class="btn">
                                        ✏️ Editează
                                    </a>


                                    <form method="POST"
                                        action="<?= route('admin.users.delete', ['id' => $u['id']]) ?>" style="display:inline-b
                               lock;" onsubmit="return confir
            m                   ('Sigur dorești să ștergi acest utilizator?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger">
                                            🗑️ Șterge
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