<div class="header">
    <nav class="calendar-nav">
        <!-- Navigation mois uniquement -->
        <button title="Mois précédent" id="prev-month" class="pagination-link calendar-button"
            data-month="<?= $months['previous']['month']; ?>" data-year="<?= $months['previous']['year']; ?>">
            <i class='bx bxs-chevron-left'></i>
        </button>
        <button title="Mois suivant" id="next-month" class="pagination-link calendar-button"
            data-month="<?= $months['next']['month']; ?>" data-year="<?= $months['next']['year']; ?>">
            <i class='bx bxs-chevron-right'></i>
        </button>
        <?php if (
            $months['current']['year'] != $months['today']['year'] ||
            $months['current']['month'] != $months['today']['month']
        ): ?>
            <button title="Ce mois" id="today-month" class="calendar-button" data-month="<?= $months['today']['month']; ?>"
                data-year="<?= $months['today']['year']; ?>">
                Mois en cours
            </button>
        <?php endif; ?>
    </nav>
    <h2 class="calendar-title">
        <?= $months['current']['name']; ?> <?= $months['current']['year']; ?>
    </h2>
</div>