<?php
// * Logout dan hapus session.

    session_start();
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit();
?>
