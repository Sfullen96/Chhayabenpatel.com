<?php
    require_once '../../core/init.php';

    $user = new User;

    if (!$user->isLoggedIn()) {
        Redirect::to('login.php');
    }

    require_once '../includes/header.php';
?>
<h1>Admin Area</h1>
<?php require_once '../includes/footer.php'; ?>