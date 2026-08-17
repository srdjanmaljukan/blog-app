<?php
session_start();
session_destroy(); // briše sve podatke iz sesije
header('Location: login.php');
exit;