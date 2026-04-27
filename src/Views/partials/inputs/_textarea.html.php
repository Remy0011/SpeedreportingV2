<?php

$input_id ??= 'id_' . uniqid();

$input_data ??= [];
$input_data['name'] ??= 'missing_name';
$input_data['value'] ??= '';
$input_data['label'] ??= 'missing_label';
$input_data['required'] ??= false;
$input_data['options'] ??= ['missing_options' => 'missing_options'];

?>

<label for="<?= $input_id; ?>"><?= $input_data['label'] ?? ''; ?></label>
<textarea name="<?= $input_data['name']; ?>" id="<?= $input_id; ?>" <?= $input_data['rows'] ? 'rows="' . $input_data['rows'] . '"' : ''; ?> <?= $input_data['cols'] ? 'cols="' . $input_data['cols'] . '"' : ''; ?>>
<?= $input_data['value'] ?? ''; ?>
</textarea>