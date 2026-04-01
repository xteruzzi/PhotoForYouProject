<?php
session_start();
require_once 'Models/UserSession.php';
$userSession = new UserSession();
$menu_items = $userSession->getMenuItems();
$title = 'Mon Compte';
$content = '<p>Page Mon Compte : profil et réglages utilisateur.</p>';
require_once 'Views/template.php';
