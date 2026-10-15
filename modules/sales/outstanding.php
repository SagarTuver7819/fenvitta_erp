<?php
require_once __DIR__ . '/../../includes/init.php';

$pageTitle = 'Sales Outstanding List';
$active = 'sales_outstanding';
include ROOT_PATH . '/includes/header.php';

$mode = 'sales';
include ROOT_PATH . '/includes/outstanding_view.php';

include ROOT_PATH . '/includes/footer.php';
