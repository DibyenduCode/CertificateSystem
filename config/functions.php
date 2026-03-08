<?php

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/database.php";


/* ------------------------------------------------
   Generate Registration Number
------------------------------------------------ */

function generateRegistrationNumber($pdo)
{

    $year = date("y");

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM students
        WHERE YEAR(created_at)=YEAR(CURDATE())
    ");

    $stmt->execute();

    $count = $stmt->fetchColumn() + 1;

    $serial = str_pad(
        $count,
        REG_SERIAL_LENGTH,
        "0",
        STR_PAD_LEFT
    );

    return INSTITUTE_PREFIX . $year . $serial;

}


/* ------------------------------------------------
   Generate Certificate Number
------------------------------------------------ */

function generateCertificateNumber($pdo)
{

    $year = date("y");

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM students
        WHERE YEAR(created_at)=YEAR(CURDATE())
    ");

    $stmt->execute();

    $count = $stmt->fetchColumn() + 1;

    $serial = str_pad(
        $count,
        CERT_SERIAL_LENGTH,
        "0",
        STR_PAD_LEFT
    );

    return CERT_PREFIX .
           INSTITUTE_CODE .
           STATE_CODE .
           $year .
           "C" .
           $serial;

}


/* ------------------------------------------------
   Gender Title
------------------------------------------------ */

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


/* ------------------------------------------------
   CSRF Token Generator
------------------------------------------------ */

function csrf_token()
{

    if(empty($_SESSION['csrf'])){
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];

}


/* ------------------------------------------------
   CSRF Token Verification
------------------------------------------------ */

function verify_csrf($token)
{

    return isset($_SESSION['csrf']) &&
           hash_equals($_SESSION['csrf'],$token);

}


/* ------------------------------------------------
   API Key Generator
------------------------------------------------ */

function generateApiKey()
{

    return bin2hex(random_bytes(32));

}