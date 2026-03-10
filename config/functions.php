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



/* ------------------------------------------------
   Image Compress
------------------------------------------------ */

function compressStudentImage($source,$destination)
{

$info = getimagesize($source);

if(!$info){
return false;
}

$width = $info[0];
$height = $info[1];

$max_width = 300;

if($width > $max_width){

$ratio = $width / $max_width;

$new_width = $max_width;
$new_height = $height / $ratio;

}else{

$new_width = $width;
$new_height = $height;

}

$image_p = imagecreatetruecolor($new_width,$new_height);

switch($info['mime']){

case 'image/jpeg':
$image = imagecreatefromjpeg($source);
break;

case 'image/png':
$image = imagecreatefrompng($source);
break;

default:
return false;

}

imagecopyresampled(
$image_p,
$image,
0,
0,
0,
0,
$new_width,
$new_height,
$width,
$height
);

/* save as compressed jpg */

imagejpeg($image_p,$destination,75);

imagedestroy($image);
imagedestroy($image_p);

return true;

}