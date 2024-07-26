<?php
//Verificação de IP
        $ip = $_SERVER['HTTP_X_REAL_IP'];
        $ipaddress = strstr($ip, ',', true);
	
if(!fnmatch("172.16.0.*", $ipaddress)){
	header('Location: index.php');
	exit();
	}
	
session_start();
	if(!$_SESSION['usuario']) {
	header('Location: acesso.php');
	exit();
	}
?>