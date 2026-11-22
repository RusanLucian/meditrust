<main class="container" style="max-width: 800px; margin: 30px auto; padding: 0 20px;">
    <a href="<?= route('dashboard') ?>" class="back-btn">← Înapoi la Dashboard</a>

    <div class="auth-card" style="margin-top: 20px; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
        <div class="auth-card-header" style="margin-bottom: 25px;">
            <h2>📅 Programează o Consultație</h2>
            <p>Alege medicul, data și intervalul orar dorit pentru consultația ta.</p>
        </div>

        <form method="POST" action="<?= route('appointments.store') ?>" class="appointment-form">
            <?= csrf_field() ?>

            <div class="form-group" style="margin-bottom: 18px;">
                <label for="doctor_id" style="display:block;font-weight:600;margin-bottom:6px;">👨‍⚕️ Medic *</label>
                <select name="doctor_id" id="doctor_id" required style="width:100%;padding:10px;border-radius:6px;border:1px solid #ccc;font-size:1rem;">
                    <option value="">-- Alege medicul --</option>
                    <?php foreach ($doctors as $doc): ?>
                        <option
                            value="<?= (int)$doc['id'] ?>"
                            <?= (int)$selectedDoctorId === (int)$doc['id'] ? 'selected' : '' ?>
                        >
                            Dr. <?= e($doc['name']) ?> (<?= e($doc['specialty'] ?? 'General') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row" style="display:flex;gap:15px;margin-bottom:18px;">
                <div class="form-group" style="flex:1;">
                    <label for="appointment_date" style="display:block;font-weight:600;margin-bottom:6px;">📆 Data consultației *</label>
                    <input
                        type="date"
                        id="appointment_date"
                        name="appointment_date"
                        min="<?= e($minDate) ?>"
                        value="<?= e(old('appointment_date')) ?>"
                        required
                        style="width:100%;padding:10px;border-radius:6px;border:1px solid #ccc;font-size:1rem;"
                    >
                </div>

                <div class="form-group" style="flex:1;">
                    <label for="appointment_time" style="display:block;font-weight:600;margin-bottom:6px;">⏰ Ora consultației *</label>
                    <select name="appointment_time" id="appointment_time" required style="width:100%;padding:10px;border-radius:6px;border:1px solid #ccc;font-size:1rem;">
                        <option value="">-- Alege ora --</option>
                        <?php foreach ($timeSlots as $slot): ?>
                            <option value="<?= e($slot) ?>" <?= old('appointment_time') === $slot ? 'selected' : '' ?>>
                                <?= e($slot) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 22px;">
                <label for="notes" style="display:block;font-weight:600;margin-bottom:6px;">📝 Motiv consultație / Simptome (opțional)</label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    placeholder="Descrie pe scurt motivul programării sau simptomele resimțite..."
                    style="width:100%;padding:10px;border-radius:6px;border:1px solid #ccc;font-size:1rem;font-family:inherit;"
                ><?= e(old('notes')) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:1.1rem;cursor:pointer;">
                Rezervă Programarea
            </button>
        </form>
    </div>
</main>
