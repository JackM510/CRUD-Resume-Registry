<?php
// Make a connection to the mysql DB using the username 'jack' and password 'example'
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=misc2', 
   'jack', 'example');
// See the "errors" folder for details...
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
