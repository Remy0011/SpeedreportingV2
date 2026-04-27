<?php if ($pages['total_pages'] > 1): ?>
    <div class="pagination">
        <?php if ($pages['current_page'] > 1): ?>
            <a href="?page=<?= $pages['current_page'] - 1; ?>" class="pagination-link"
                data-page="<?= $pages['current_page'] - 1; ?>"><i class='bx bx-chevron-left'></i></a>
            <?php endif;
        // Si - de 6 pages, on affiche les numéros de page
        if ($pages['total_pages'] < 6):
            for ($i = 1; $i <= $pages['total_pages']; $i++): ?>
                <a href="?page=<?= $i; ?>" class="pagination-link <?= $i == $pages['current_page'] ? 'active' : ''; ?>"
                    data-page="<?= $i; ?>"><?= $i; ?></a>
            <?php endfor;
        else: ?>
            <!-- Première page -->
            <a href="?page=1" class="pagination-link <?= 1 == $pages['current_page'] ? 'active' : ''; ?>" data-page="1">1</a>

            <?php if ($pages['current_page'] > 4): ?>
                <span class="pagination-ellipsis">...</span>
            <?php endif;
            // Afficher les pages autour de la page actuelle
            for ($i = max(2, $pages['current_page'] - 2); $i <= min($pages['total_pages'] - 1, $pages['current_page'] + 2); $i++): ?>
                <a href="?page=<?= $i; ?>" class="pagination-link <?= $i == $pages['current_page'] ? 'active' : ''; ?>"
                    data-page="<?= $i; ?>"><?= $i; ?></a>
            <?php endfor;
            if ($pages['current_page'] < $pages['total_pages'] - 3): ?>
                <span class="pagination-ellipsis">...</span>
            <?php endif; ?>

            <!-- Dernière page -->
            <a href="?page=<?= $pages['total_pages']; ?>" class="pagination-link <?= $pages['total_pages'] == $pages['current_page'] ? 'active' : ''; ?>"
                data-page="<?= $pages['total_pages']; ?>"><?= $pages['total_pages']; ?></a>
        <?php endif;

        if ($pages['current_page'] < $pages['total_pages']): ?>
            <a href="?page=<?= $pages['current_page'] + 1; ?>" class="pagination-link"
                data-page="<?= $pages['current_page'] + 1; ?>"><i class='bx bx-chevron-right'></i></a>
        <?php endif; ?>
    </div>
<?php endif; ?>