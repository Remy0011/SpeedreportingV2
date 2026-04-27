<?php

$input_id ??= 'id_' . uniqid();

$input_data ??= [];
$input_data['name'] ??= 'missing_name';
$input_data['value'] ??= '';
$input_data['label'] ??= 'missing_label';
$input_data['required'] ??= false;

?>

<label for="<?= $input_id; ?>"><?= $input_data['label'] ?? ''; ?></label>
<input type="text" id="<?= $input_id ?>" name="<?= $input_data['name'] ?>" value="<?= $input_data['value']; ?>"
    <?= isset($input_data['maxlength']) ? 'maxlength ="' . $input_data['maxlength'] . '"' : ''; ?> <?= $input_data['required'] ? 'required' : ''; ?> />