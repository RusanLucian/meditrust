<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <h2 class="card-title">👨‍⚕️ Listă Medici (<?= count($doctors) ?>)</h2>
        <a href="<?= route('admin.users.create') ?>" class="btn btn-primary">➕ Adaugă Medic Nou</a>
    </div>

    <div class="card-body">
        <!-- Filtrare -->
        <form method="GET" action="<?= route('admin.doctors') ?>" class="admin-filters-form" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <input
                type="text"
                name="search"
                placeholder="🔍 Caută medic după nume..."
                value="<?= e($filterSearch) ?>"
                style="flex:1;min-width:220px;padding:8px 12px;border:1px solid #ccc;border-radius:4px;"
            >

            <select name="specialty" onchange="this.form.submit()" style="padding:8px 12px;border:1px solid #ccc;border-radius:4px;">
                <option value="0">-- Toate specialitățile --</option>
                <?php foreach ($specialties as $spec): ?>
                    <option value="<?= (int)$spec['id'] ?>" <?= $filterSpecialty === (int)$spec['id'] ? 'selected' : '' ?>>
                        <?= e($spec['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-secondary">Filtrează</button>

            <?php if (!empty($filterSearch) || $filterSpecialty > 0): ?>
                <a href="<?= route('admin.doctors') ?>" class="btn btn-outline">Resetează</a>
            <?php endif; ?>
        </form>

        <?php if (empty($doctors)): ?>
            <p style="text-align:center;color:#666;padding:30px 0;">Nu au fost găsiți medici conform filtrelor.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Medic</th>
                            <th>Specialitate</th>
                            <th>Contact</th>
                            <th>Programări</th>
                            <th>Rating & Recenzii</th>
                            <th style="text-align:right;">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctors as $doc): ?>
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <?php if (!empty($doc['avatar'])): ?>
                                            <img src="<?= asset('img/' . ltrim($doc['avatar'], '/')) ?>" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                                        <?php else: ?>
                                            <span style="font-size:1.5rem;">👨‍⚕️</span>
                                        <?php endif; ?>
                                        <div>
                                            <strong>Dr. <?= e($doc['name']) ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-teal"><?= e($doc['specialty'] ?? 'N/A') ?></span></td>
                                <td>
                                    <div>📧 <?= e($doc['email']) ?></div>
                                    <div>📞 <?= e($doc['phone'] ?? '-') ?></div>
                                </td>
                                <td><strong><?= (int)$doc['total_appointments'] ?></strong> consultații</td>
                                <td>
                                    <strong style="color:#ff9800;">⭐ <?= number_format((float)$doc['avg_rating'], 1) ?>/5</strong>
                                    <span style="color:#777;font-size:0.85rem;">(<?= (int)$doc['total_reviews'] ?>)</span>
                                </td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a href="<?= route('doctors.show', ['id' => $doc['id']]) ?>" target="_blank" class="btn">
                                        👁️ Profil
                                    </a>
                                    <a href="<?= route('admin.users.edit', ['id' => $doc['id']]) ?>" class="btn">
                                        ✏️ Editează
                                    </a>
                                    <form method="POST" action="<?= route('admin.doctors.delete', ['id' => $doc['id']]) ?>" style="display:inline-block;" onsubmit="return confirm('Sigur dorești să ștergi acest medic?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-xs btn-danger">
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
