<?php
if (isset($_POST['action']) && in_array($_POST['action'], ['like', 'join'], true)) {
    $_POST['feed_action'] = $_POST['action'];
}

$_POST['action'] = 'feed_action';
require_once __DIR__ . '/ActionController.php';
