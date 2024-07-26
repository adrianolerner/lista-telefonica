<?php
session_start();
if ((!$_SESSION['usuario']) && (empty($adminarray['admin']))) {
	header('Location: ../acesso.php');
	exit();
}
?>