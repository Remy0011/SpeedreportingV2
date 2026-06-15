<?php
$current_week = $selected_week ?? $weeks[0];
$weeks_list = array_values($weeks);
$current_index = array_search($current_week, $weeks_list, true);
$prev_week = $current_index > 0 ? $weeks_list[$current_index - 1] : null;
$next_week = $current_index < count($weeks_list) - 1 ? $weeks_list[$current_index + 1] : null;
$current_view = htmlspecialchars($view ?? 'users', ENT_QUOTES);

$formatHours = static function (float $hours): string {
    return fmod($hours, 1.0) === 0.0 ? (string) (int) $hours : number_format($hours, 1, ',', ' ');
};
?>

<div id="user-planning">
    <div class="header">
        <h2 class="calendar-title"><?= $months['current']['name'] ?> <?= $months['current']['year'] ?></h2>
        <nav class="calendar-nav">
            <a title="Mois pr&eacute;c&eacute;dent" class="pagination-link calendar-button"
               href="?view=<?= $current_view ?>&month=<?= $months['previous']['month'] ?>&year=<?= $months['previous']['year'] ?>">
                <i class='bx bxs-chevron-left'></i>
            </a>
            <a title="Mois suivant" class="pagination-link calendar-button"
               href="?view=<?= $current_view ?>&month=<?= $months['next']['month'] ?>&year=<?= $months['next']['year'] ?>">
                <i class='bx bxs-chevron-right'></i>
            </a>

            <?php if ($prev_week): ?>
                <a title="Semaine pr&eacute;c&eacute;dente" class="pagination-link calendar-button"
                   href="?view=<?= $current_view ?>&month=<?= $months['current']['month'] ?>&year=<?= $months['current']['year'] ?>&week=<?= $prev_week['number'] ?>&week_year=<?= $prev_week['year'] ?>">
                    <i class='bx bx-chevron-left'></i>
                </a>
            <?php endif; ?>

            <div class="week-label">
                <strong>N&deg; <?= $current_week['number'] ?></strong>
                <span><?= $current_week['range'] ?></span>
            </div>

            <?php if ($next_week): ?>
                <a title="Semaine suivante" class="pagination-link calendar-button"
                   href="?view=<?= $current_view ?>&month=<?= $months['current']['month'] ?>&year=<?= $months['current']['year'] ?>&week=<?= $next_week['number'] ?>&week_year=<?= $next_week['year'] ?>">
                    <i class='bx bx-chevron-right'></i>
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <div class="planning-user-table-wrap">
        <table class="planning-user-table">
            <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Charge</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($user_rows)): ?>
                <tr>
                    <td colspan="3" class="planning-empty">Aucun utilisateur &agrave; afficher.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($user_rows as $row): ?>
                <?php
                $hours = (float) $row['hours'];
                $loadRatio = min(100, ($hours / 35) * 100);
                $loadClass = $hours >= 35 ? 'is-full' : ($hours >= 28 ? 'is-high' : 'is-normal');
                ?>
                <tr>
                    <td class="planning-user-cell">
                        <strong><?= htmlspecialchars($row['name'] ?: 'Utilisateur sans nom') ?></strong>
                        <?php if (!empty($row['role'])): ?>
                            <span><?= htmlspecialchars($row['role']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="planning-load-cell">
                        <strong><?= $formatHours($hours) ?>/35 Heures</strong>
                        <span class="planning-load-bar" aria-hidden="true">
                                <span class="<?= $loadClass ?>" style="width: <?= $loadRatio ?>%"></span>
                            </span>
                        <?php $remaining = max(0, 35 - $hours); ?>
                        <span class="planning-remaining <?= $remaining > 0 ? 'is-pending' : 'is-done' ?>">
                                <?php if ($remaining > 0): ?>
                                    <i class='bx bx-time-five'></i> Reste <?= $formatHours($remaining) ?>h
                                <?php else: ?>
                                    <i class='bx bx-check-circle'></i> Objectif atteint
                                <?php endif; ?>
                            </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>