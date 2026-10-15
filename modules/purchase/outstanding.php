<?php
require_once __DIR__ . '/../../includes/init.php';

$pageTitle = 'Purchase Outstanding List';
$active = 'purchase_outstanding';
include ROOT_PATH . '/includes/header.php';

$mode = 'purchase';
include ROOT_PATH . '/includes/outstanding_view.php';

include ROOT_PATH . '/includes/footer.php';
