<div id='read_<?= $id; ?>' class='modal'>
    <div class='modal-content'>
        <h2>Détails</h2>
        <div class='modal-body'>
            <?php foreach ($data as $key => $value): ?>
                <p><?= $key ?> : <?= $value ?></p>
            <?php endforeach; ?>
        </div>
        <div class='modal-footer'>
            <button class='close-modal'>Fermer</button>
        </div>
    </div>
</div>