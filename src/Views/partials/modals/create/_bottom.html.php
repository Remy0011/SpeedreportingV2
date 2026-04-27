            </div>
            <?php

            use Src\Services\CsrfService;

            CsrfService::insertToken();
            ?>
            <div class='modal-footer'>
                <button type='submit' class='button success confirm-create'>Créer</button>
                <button type='button' class='close-modal'>Fermer</button>
            </div>
        </div>
    </form>
</div>