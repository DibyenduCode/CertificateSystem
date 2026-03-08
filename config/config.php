<?php

session_start();

/* BASE URL */

define("BASE_URL","http://localhost/certificate-system");


/* INSTITUTE INFORMATION */

define("INSTITUTE_PREFIX","BPLF");        // Registration prefix
define("CERT_PREFIX","UN");               // Certificate prefix
define("INSTITUTE_CODE","1R");            // Institute Code
define("STATE_CODE","WB");                // State Code


/* NUMBER SETTINGS */

define("REG_SERIAL_LENGTH",7);            // Registration serial digits
define("CERT_SERIAL_LENGTH",7);           // Certificate serial digits


/* TIMEZONE */

date_default_timezone_set("Asia/Kolkata");