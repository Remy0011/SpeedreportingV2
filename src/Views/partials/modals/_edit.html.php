<div id='edit_<?= $id; ?>' class='modal'>
    <div class='modal-content'>
        <h2>Modifier</h2>
        <div class='modal-body'>
            <form id="edit-form-<?= $id; ?>">
                <?php foreach ($data as $input_id => $input_data): ?>
                    <div class="input-container">
                        <?php include __DIR__ . '/../../partials/inputs/_' . $input_data['input'] . '.html.php'; ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="button success">Sauvegarder</button>
            </form>
        </div>
        <div class='modal-footer'>
            <button class='close-modal'>Fermer</button>
        </div>
    </div>
</div>