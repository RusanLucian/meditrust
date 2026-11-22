<main class="container" style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <a href="<?= route('doctors.index') ?>" class="back-btn">← Înapoi la Lista Medicilor</a>

    <div class="doctor-profile">
        <div class="doctor-profile-header">
            <div class="doctor-profile-avatar">
                <?php if (!empty($doctor['avatar'])): ?>
                    <img src="<?= asset('img/' . ltrim($doctor['avatar'], '/')) ?>" alt="<?= e($doctor['name']) ?>" class="avatar-large">
                <?php else: ?>
                    <div class="avatar-placeholder">👨‍⚕️</div>
                <?php endif; ?>
            </div>

            <div class="doctor-profile-info">
                <h1><?= e($doctor['name']) ?></h1>
                <p class="specialty-badge">🏥 <?= e($doctor['specialty'] ?? 'Specialitate nespecificată') ?></p>
                <?php if (!empty($doctor['specialty_description'])): ?>
                    <p class="specialty-desc"><?= e($doctor['specialty_description']) ?></p>
                <?php endif; ?>

                <div class="contact-details">
                    <p><strong>📞 Telefon:</strong> <?= e($doctor['phone'] ?? 'N/A') ?></p>
                    <p><strong>📧 Email:</strong> <?= e($doctor['email']) ?></p>
                </div>
            </div>

            <div class="doctor-profile-actions">
                <?php if ($isPatient): ?>
                    <a href="<?= route('appointments.book') ?>?doctor_id=<?= (int)$doctor['id'] ?>" class="btn btn-primary" style="display:block;margin-bottom:10px;text-align:center;">
                        📅 Programează Consultație
                    </a>

                    <?php if ($canReview): ?>
                        <a href="<?= route('reviews.add', ['id' => $doctor['id']]) ?>" class="btn btn-secondary">
                            ✍️ Lasă o Recenzie
                        </a>
                    <?php elseif ($hasReviewed): ?>
                        <span class="badge-reviewed">
                            ✓ Ai trimis deja o recenzie
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($doctor['bio'])): ?>
            <div class="doctor-bio-section" style="margin-top:25px;padding:20px;background:#f9f9f9;border-radius:8px;">
                <h3>ℹ️ Despre medic</h3>
                <p><?= nl2br(e($doctor['bio'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- STATISTICI RECENZII -->
        <div class="reviews-section" style="margin-top: 35px;">
            <h2>⭐ Evaluări și Recenzii (<?= count($reviews) ?>)</h2>

            <?php if (!empty($reviews)): ?>
                <div class="ratings-overview" style="display:flex;flex-wrap:wrap;gap:20px;margin:20px 0;padding:20px;background:#fff;border:1px solid #e0e0e0;border-radius:8px;">
                    <div class="overall-rating" style="text-align:center;min-width:150px;border-right:1px solid #eee;padding-right:20px;">
                        <span style="font-size:3rem;font-weight:bold;color:#ff9800;"><?= number_format($averages['rating'], 1) ?></span>
                        <div style="font-size:1.2rem;color:#ff9800;">★★★★★</div>
                        <p style="color:#666;margin:5px 0 0 0;font-size:0.9rem;">din 5 stele</p>
                    </div>

                    <div class="breakdown-grid" style="flex:1;display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;">
                        <div><strong>🗣️ Comunicare:</strong> <?= number_format($averages['communication'], 1) ?>/5</div>
                        <div><strong>🩺 Profesionalism:</strong> <?= number_format($averages['professionalism'], 1) ?>/5</div>
                        <div><strong>⏰ Punctualitate:</strong> <?= number_format($averages['punctuality'], 1) ?>/5</div>
                        <div><strong>❤️ Empatie:</strong> <?= number_format($averages['empathy'], 1) ?>/5</div>
                        <div><strong>👍 Recomandare:</strong> <?= number_format($averages['recommendation'], 1) ?>/5</div>
                    </div>
                </div>

                <!-- LISTA RECENZII -->
                <div class="reviews-list">
                    <?php foreach ($reviews as $rev): ?>
                        <div class="review-item" style="border-bottom:1px solid #eee;padding:15px 0;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <div>
                                    <strong><?= e($rev['patient_name']) ?></strong>
                                    <span style="color:#ff9800;margin-left:10px;">
                                        <?= str_repeat('★', (int)$rev['rating']) ?><?= str_repeat('☆', 5 - (int)$rev['rating']) ?>
                                    </span>
                                </div>
                                <span style="font-size:0.85rem;color:#888;">
                                    <?= date('d.m.Y H:i', strtotime($rev['created_at'])) ?>
                                </span>
                            </div>

                            <p style="margin:10px 0;line-height:1.5;"><?= nl2br(e($rev['comment'])) ?></p>

                            <div style="font-size:0.85rem;color:#666;display:flex;gap:15px;flex-wrap:wrap;">
                                <span>Comunicare: <?= (int)$rev['communication'] ?>/5</span>
                                <span>Profesionalism: <?= (int)$rev['professionalism'] ?>/5</span>
                                <span>Punctualitate: <?= (int)$rev['punctuality'] ?>/5</span>
                                <span>Empatie: <?= (int)$rev['empathy'] ?>/5</span>
                                <span>Recomandare: <?= (int)$rev['recommendation'] ?>/5</span>
                            </div>

                            <?php if ($isOwnProfile || $isAdmin): ?>
                                <form method="POST" action="<?= route('doctors.reviews.delete', ['id' => $doctor['id']]) ?>" style="margin-top:10px;" onsubmit="return confirm('Sigur dorești să ștergi această recenzie?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="review_id" value="<?= (int)$rev['id'] ?>">
                                    <button type="submit" style="background:#ffebee;color:#c62828;border:1px solid #ffcdd2;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:0.85rem;">
                                        🗑️ Șterge recenzia
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color:#666;padding:20px;background:#fafafa;border-radius:6px;text-align:center;">
                    Acest medic nu are încă recenzii înregistrate.
                </p>
            <?php endif; ?>
        </div>
    </div>
</main>
