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


/* ------------------------------------------------
   Flash Session Notification Messages
------------------------------------------------ */

function set_flash($type, $message)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}


/* ------------------------------------------------
   Pagination Data Helper
------------------------------------------------ */

function get_pagination_data($total_items, $limit = 10, $current_page = 1)
{
    $limit = max(1, (int)$limit);
    $total_pages = max(1, (int)ceil($total_items / $limit));
    $current_page = max(1, min($total_pages, (int)$current_page));
    $offset = ($current_page - 1) * $limit;
    $start_item = $total_items > 0 ? $offset + 1 : 0;
    $end_item = min($offset + $limit, $total_items);

    return [
        'total_items'  => $total_items,
        'total_pages'  => $total_pages,
        'current_page' => $current_page,
        'limit'        => $limit,
        'offset'       => $offset,
        'start_item'   => $start_item,
        'end_item'     => $end_item,
        'has_prev'     => $current_page > 1,
        'has_next'     => $current_page < $total_pages,
    ];
}


/* ------------------------------------------------
   Validate Uploaded Image File
------------------------------------------------ */

function validateStudentImage($file)
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return "Please select a valid image file.";
    }

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/jpg'];
    $file_info = getimagesize($file['tmp_name']);
    if (!$file_info || !in_array($file_info['mime'], $allowed_mimes)) {
        return "Invalid file type. Only JPG and PNG images are allowed.";
    }

    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        return "Image file size must not exceed 5MB.";
    }

    return true;
}


/* ------------------------------------------------
   Role & Permission Helpers
------------------------------------------------ */

function is_admin()
{
    return isset($_SESSION['admin_id']) && empty($_SESSION['staff_id']);
}

function is_staff()
{
    return isset($_SESSION['staff_id']);
}

function is_impersonating()
{
    return !empty($_SESSION['impersonated_by_admin']);
}

function get_staff_permissions()
{
    if (!isset($_SESSION['staff_permissions'])) {
        return [];
    }
    if (is_array($_SESSION['staff_permissions'])) {
        return $_SESSION['staff_permissions'];
    }
    $decoded = json_decode($_SESSION['staff_permissions'], true);
    return is_array($decoded) ? $decoded : [];
}

function has_permission($module)
{
    if (is_admin()) {
        return true;
    }
    if (is_staff()) {
        $permissions = get_staff_permissions();
        return in_array($module, $permissions);
    }
    return false;
}


/* ------------------------------------------------
   Calculate Certificate Grade (Words: EXCELLENT, VERY GOOD, etc.)
   Logic:
   - Excellent >= 80%
   - Very Good >= 70%
   - Good      >= 60%
   - Fair      >= 50%
------------------------------------------------ */

function calculateCertificateGrade($theory_marks, $practical_marks, $max_marks = 200)
{
    $total = (int)$theory_marks + (int)$practical_marks;
    if ($max_marks <= 0) {
        $max_marks = 200;
    }
    $percentage = ($total / $max_marks) * 100;

    if ($percentage >= 80) {
        return "EXCELLENT";
    } elseif ($percentage >= 70) {
        return "VERY GOOD";
    } elseif ($percentage >= 60) {
        return "GOOD";
    } elseif ($percentage >= 50) {
        return "FAIR";
    } else {
        return "FAIL";
    }
}

/* ------------------------------------------------
   Calculate Marksheet Letter Grade (A+, A, B, C, F)
   Logic:
   - A+ >= 80%
   - A  >= 70%
   - B  >= 60%
   - C  >= 50%
------------------------------------------------ */

function calculateMarksheetGrade($theory_marks, $practical_marks, $max_marks = 200)
{
    $total = (int)$theory_marks + (int)$practical_marks;
    if ($max_marks <= 0) {
        $max_marks = 200;
    }
    $percentage = ($total / $max_marks) * 100;

    if ($percentage >= 80) {
        return "A+";
    } elseif ($percentage >= 70) {
        return "A";
    } elseif ($percentage >= 60) {
        return "B";
    } elseif ($percentage >= 50) {
        return "C";
    } else {
        return "F";
    }
}

function calculateGrade($theory_marks, $practical_marks, $max_marks = 200)
{
    return calculateCertificateGrade($theory_marks, $practical_marks, $max_marks);
}