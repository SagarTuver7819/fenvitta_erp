<?php
/* /production/add  and  /production/edit/{id}  -> batch form is on the production list page */
$_GET['edit'] = $_GET['id'] ?? '';
require __DIR__ . '/index.php';
