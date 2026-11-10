<?php $errors = session('errors'); ?>
<?php if (session('success')): ?>
    <div class="flash flash-success" role="status"><?= esc(session('success')) ?></div>
<?php endif; ?>
<?php if (session('error')): ?>
    <div class="flash flash-error" role="alert"><?= esc(session('error')) ?></div>
<?php endif; ?>
<?php if (is_array($errors) && $errors): ?>
    <div class="flash flash-error" role="alert">
        Ada isian yang perlu diperbaiki:
        <ul><?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
