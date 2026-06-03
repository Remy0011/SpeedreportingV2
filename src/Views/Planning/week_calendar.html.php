<div id="planning-calendar">
    <div id="calendar-container">
        <div id="calendar">
            <div class="header">
                <nav class="calendar-nav">
                    <a title="Mois précédent" class="pagination-link calendar-button"
                       href="?month=<?= $months['previous']['month']; ?>&year=<?= $months['previous']['year']; ?>">
                        <i class='bx bxs-chevron-left'></i>
                    </a>
                    <a title="Mois suivant" class="pagination-link calendar-button"
                       href="?month=<?= $months['next']['month']; ?>&year=<?= $months['next']['year']; ?>">
                        <i class='bx bxs-chevron-right'></i>
                    </a>
                    <?php if (
                        $months['current']['year'] !== $months['today']['year'] ||
                        $months['current']['month'] !== $months['today']['month']
                    ): ?>
                        <a title="Mois en cours" class="calendar-button"
                           href="?month=<?= $months['today']['month']; ?>&year=<?= $months['today']['year']; ?>">
                            Mois en cours
                        </a>
                    <?php endif; ?>
                </nav>
                <h2 id="calendar-title" class="calendar-title">
                    <?= $months['current']['name']; ?> <?= $months['current']['year']; ?>
                </h2>
            </div>

            <div class="planning-week-list">
                <?php foreach ($weeks as $week): ?>
                    <section class="planning-week">
                        <header class="planning-week-header">
                            <div>
                                <span class="planning-week-kicker">Semaine</span>
                                <h3>N° <?= $week['number']; ?></h3>
                            </div>
                            <span class="planning-week-range"><?= $week['range']; ?></span>
                        </header>

                        <div class="planning-days-grid">
                            <?php foreach ($week['days'] as $day): ?>
                                <article class="planning-day <?= !$day['is_current_month'] ? 'is-outside-month' : ''; ?> <?= $day['is_today'] ? 'is-today' : ''; ?>">
                                    <div class="planning-day-header">
                                        <span class="planning-day-name"><?= $day['name']; ?></span>
                                        <span class="planning-day-number"><?= $day['number']; ?></span>
                                    </div>
                                    <time datetime="<?= $day['date']; ?>"><?= $day['display']; ?></time>
                                    <div class="planning-day-body">
                                        <span>Aucune prévision</span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
