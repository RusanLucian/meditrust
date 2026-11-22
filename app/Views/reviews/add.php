<main class="container" style="max-width: 800px; margin: 30px auto; padding: 0 20px;">
    <a href="<?= route('doctors.show', ['id' => $doctor['id']]) ?>" class="back-btn">← Înapoi la Profil Medic</a>

    <div class="auth-card" style="margin-top: 20px; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
        <div class="auth-card-header" style="margin-bottom: 25px;">
            <h2>✍️ Adaugă Recenzie Medicală</h2>
            <p>Evaluează experiența consultației cu <strong>Dr. <?= e($doctor['name']) ?></strong> (<?= e($doctor['specialty'] ?? '') ?>)</p>
        </div>

        <form method="POST" action="<?= route('reviews.store', ['id' => $doctor['id']]) ?>" class="review-form">
            <?= csrf_field() ?>

            <div class="criteria-container" style="display:flex;flex-direction:column;gap:15px;margin-bottom:25px;">
                <?php foreach ($criteria as $key => $label): ?>
                    <div class="criteria-row" style="display:flex;justify-content:space-between;align-items:center;background:#f9f9f9;padding:12px 16px;border-radius:6px;">
                        <label for="<?= e($key) ?>" style="font-weight:600;"><?= e($label) ?></label>
                        <select name="<?= e($key) ?>" id="<?= e($key) ?>" required style="padding:6px 12px;border-radius:4px;border:1px solid #ccc;font-size:1rem;">
                            <option value="5" selected>⭐⭐⭐⭐⭐ 5 - Excelent</option>
                            <option value="4">⭐⭐⭐⭐ 4 - Foarte bun</option>
                            <option value="3">⭐⭐⭐ 3 - Satisfăcător</option>
                            <option value="2">⭐⭐ 2 - Sub așteptări</option>
                            <option value="1">⭐ 1 - Nesatisfăcător</option>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="comment" style="display:block;font-weight:600;margin-bottom:8px;">💬 Comentariul tău detaliat *</label>
                <textarea
                    id="comment"
                    name="comment"
                    rows="5"
                    placeholder="Descrie cum a decurs consultația, dacă tratamentul a fost eficient, claritatea explicațiilor oferite..."
                    maxlength="2000"
                    required
                    style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;font-family:inherit;font-size:1rem;"
                ><?= e(old('comment')) ?></textarea>
                <small style="color:#777;">Maxim 2000 de caractere.</small>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:1.1rem;cursor:pointer;">
                Trimite Recenzia
            </button>
        </form>
    </div>
</main>
