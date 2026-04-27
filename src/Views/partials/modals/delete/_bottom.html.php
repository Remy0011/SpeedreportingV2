                <?php
                use Src\Services\CsrfService;
                CsrfService::insertToken(); ?>
                <input type ="hidden" name="id" value="<?= $id; ?>">
                <input type ="hidden" name="command" value="delete">
                <button class='confirm-delete'>Oui</button>
            </form>
        </div>
    </div>
</div>