<?php
session_start(); 

// Vernietig de sessie
$_SESSION = [];
session_destroy();

// Verwijder de 'user' cookie indien aanwezig
if (isset($_COOKIE['user'])) {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    setcookie('user', '', time() - 3600, '/', '', $secure, true);
}

// Redirect naar de inlogpagina
header("Location: inloggen.php");
exit();

