<div id='delete_<?= $id; ?>' class='modal'>
    <div class='modal-content'>
        <h2>Confirmer la suppression</h2>
        <p>Êtes-vous sûr de vouloir supprimer cet élément ?</p>
        <div class='modal-footer'>
            <button class='close-modal'>Annuler</button>
            <form id="delete-form-<?= $id; ?>" method="POST">
