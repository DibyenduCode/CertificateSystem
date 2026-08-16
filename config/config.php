<?php

session_start();

/* BASE URL */

define("BASE_URL","http://localhost/cert");


/* INSTITUTE INFORMATION */

define("INSTITUTE_PREFIX","BPLF");        // Registration prefix
define("CERT_PREFIX","BP");               // Certificate prefix (BP)
define("INSTITUTE_CODE","1R");            // Default fallback Institute Code (dynamic per institute)


/* NUMBER SETTINGS */

define("REG_SERIAL_LENGTH",7);            // Registration serial digits
define("CERT_SERIAL_LENGTH",7);           // Certificate serial digits


/* TIMEZONE */

date_default_timezone_set("Asia/Kolkata");