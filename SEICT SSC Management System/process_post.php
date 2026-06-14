<?php
// Forward faculty/admin composer submission into ActionController.
// Ensure required POST fields exist before the controller runs.
$_POST['action'] = 'create_post';

// This file must not rely on calling scripts to include auth helpers.
// ActionController.php already requires auth.php, but we keep this explicit
// to prevent undefined function errors if bootstrapping changes.
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ActionController.php';

