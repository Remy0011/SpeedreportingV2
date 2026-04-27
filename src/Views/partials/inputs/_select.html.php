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
<select name="<?= $input_data['name']; ?>" id="<?= $input_id; ?>" <?= $input_data['required'] ? 'required' : ''; ?>>
    <?php foreach ($input_data['options'] as $value => $option): ?>
        <option value="<?= $value; ?>" <?= $value == $input_data['value'] ? 'selected' : ''; ?>>
            <?= $option; ?>
        </option>
    <?php endforeach; ?>
</select>