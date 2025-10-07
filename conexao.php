<?php
ini_set('display_errors', 1);

$servername = '179.188.16.33';
$username = 'entiresys';
$password = 'Etsys#2014';
$dbname = 'entiresys';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>