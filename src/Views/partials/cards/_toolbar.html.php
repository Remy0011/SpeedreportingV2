<?php $current_view = $view ?? 'project'; ?>

<div class="toolbar">
    <button class="btn btn-primary" id="create-button">
        <i class='bx bx-plus-circle'></i>
        <span>Créer</span>
    </button>

    <div class="search-bar">
        <i class='bx bx-left-arrow-alt'></i>
        <input type="text" id="search-input" placeholder="projets">
        <i class='bx bx-x' id="search-clear"></i>
    </div>

    <div class="filter-bar">
        <i class='bx bx-filter-alt'></i>
        <span>Afficher :</span>
    </div>

    <div class="filter-buttons">
        <a href="?view=projects" class="btn btn-filter <?= $current_view === 'projects' ? 'active' : '' ?>">
            <i class='bx bx-file'></i>
            <span>Projets</span>
        </a>

        <a href="?view=users" class="btn btn-filter <?= $current_view === 'users' ? 'active' : '' ?>">
            <i class='bx bx-group'></i>
            <span>Utilisateurs</span>
        </a>

        <a href="?view=calendar" class="btn btn-filter <?= $current_view === 'calendar' ? 'active' : '' ?>">
            <i class='bx bx-calendar'></i>
            <span>Calendrier</span>
        </a>
    </div>
</div>