<?php

$input_id ??= 'id_' . uniqid();

$input_data ??= [];
$input_data['name'] ??= 'missing_name';
$input_data['value'] ??= '';
$input_data['label'] ??= 'missing_label';
$input_data['required'] ??= false;

?>

<label for="<?= $input_id; ?>"><?= $input_data['label'] ?? ''; ?></label>
<input type="datetime-local" id="<?= $input_id ?>" name="<?= $input_data['name'] ?>" value="<?= $input_data['value']; ?>"
    <?= isset($input_data['min']) ? 'min ="' . $input_data['min'] . '"' : ''; ?> <?= isset($input_data['max']) ? 'max ="' . $input_data['max'] . '"' : ''; ?> <?= $input_data['required'] ? 'required' : ''; ?> />