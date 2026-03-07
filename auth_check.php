<?php

require_once "../config/config.php";

if(!isset($_SESSION['admin_id']))
{
    header("Location: login.php");
    exit;
}