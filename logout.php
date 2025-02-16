<?php 
    // Remove name and user_id from $_SESSION and redirect back to index.php
    session_start();
    unset($_SESSION['name']);
    unset($_SESSION['user_id']);
    header('Location: index.php');
?>