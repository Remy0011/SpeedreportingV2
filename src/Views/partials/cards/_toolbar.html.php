<?php 
$current_view = $view ?? 'projects';
$month_param = isset($months) ? '&month=' . $months['current']['month'] . '&year=' . $months['current']['year'] : '';
?>

<div class="toolbar">
    <!-- ... boutons inchangés ... -->
    <div class="filter-buttons">
        <a href="?view=projects<?= $month_param ?>" class="btn btn-filter <?= $current_view === 'projects' ? 'active' : '' ?>">
            <i class='bx bx-file'></i>
            <span>Projets</span>
        </a>
        <a href="?view=users<?= $month_param ?>" class="btn btn-filter <?= $current_view === 'users' ? 'active' : '' ?>">
            <i class='bx bx-group'></i>
            <span>Utilisateurs</span>
        </a>
        <a href="?view=calendar<?= $month_param ?>" class="btn btn-filter <?= $current_view === 'calendar' ? 'active' : '' ?>">
            <i class='bx bx-calendar'></i>
            <span>Calendrier</span>
        </a>
    </div>
</div>