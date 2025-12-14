<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: chat.php');
} else {
    header('Location: login.php');
}
exit;
