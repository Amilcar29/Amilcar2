<?php
$password = 'Nempresa#2q';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hash de contraseña para '$password': $hash";
?>