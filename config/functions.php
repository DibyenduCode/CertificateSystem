<?php

require_once "database.php";

function generateRegistrationNumber($pdo)
{
    $year = date("y");

    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $count = $stmt->fetchColumn() + 1;

    $serial = str_pad($count,7,"0",STR_PAD_LEFT);

    return INSTITUTE_PREFIX.$year.$serial;
}


function generateCertificateNumber($pdo)
{
    $year = date("y");

    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $count = $stmt->fetchColumn() + 1;

    $serial = str_pad($count,7,"0",STR_PAD_LEFT);

    return "UN".INSTITUTE_CODE.STATE_CODE.$year."C".$serial;
}


function genderTitle($gender)
{
    if($gender == "Male"){
        return "Mr";
    }

    if($gender == "Female"){
        return "Ms";
    }

    return "";
}


function csrf_token()
{
    if(empty($_SESSION['csrf'])){
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}


function verify_csrf($token)
{
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'],$token);
}

function generateApiKey()
{
    return bin2hex(random_bytes(32));
}