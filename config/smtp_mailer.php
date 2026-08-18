<?php

/**
 * Ensure database tables and columns for SMTP and Student Email exist
 */
function ensure_smtp_and_email_tables($pdo)
{
    static $done = false;
    if ($done) return;

    try {
        // 1. Create settings table if not exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `settings` (
                `setting_key` VARCHAR(100) NOT NULL PRIMARY KEY,
                `setting_value` TEXT DEFAULT NULL,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Set default SMTP settings if not populated
        $defaults = [
            'smtp_enabled'    => '1',
            'smtp_host'       => 'smtp.gmail.com',
            'smtp_port'       => '587',
            'smtp_auth'       => '1',
            'smtp_username'   => '',
            'smtp_password'   => '',
            'smtp_encryption' => 'tls', // 'tls', 'ssl', 'none'
            'smtp_from_email' => 'no-reply@beliefpro.org',
            'smtp_from_name'  => 'BELIEFPRO LEARNING FORUM'
        ];

        $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($defaults as $k => $v) {
            $stmt->execute([$k, $v]);
        }

        // Update smtp_enabled and smtp_from_name if set to old defaults
        $pdo->exec("UPDATE settings SET setting_value = '1' WHERE setting_key = 'smtp_enabled' AND setting_value = '0'");
        $pdo->exec("UPDATE settings SET setting_value = 'BELIEFPRO LEARNING FORUM' WHERE setting_key = 'smtp_from_name' AND setting_value LIKE 'CertiPortal%'");

        // 2. Add email column to students table if missing
        $stmt_check = $pdo->query("SHOW COLUMNS FROM students LIKE 'email'");
        if ($stmt_check->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `students` ADD COLUMN `email` VARCHAR(255) NULL AFTER `name`");
        }

        // 3. Add gov_id_doc column to students table if missing
        $stmt_check_govid = $pdo->query("SHOW COLUMNS FROM students LIKE 'gov_id_doc'");
        if ($stmt_check_govid->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `students` ADD COLUMN `gov_id_doc` VARCHAR(255) NULL AFTER `student_photo`");
        }

        // 4. Add code column to institutes table if missing
        $stmt_check_instcode = $pdo->query("SHOW COLUMNS FROM institutes LIKE 'code'");
        if ($stmt_check_instcode->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `institutes` ADD COLUMN `code` VARCHAR(50) NULL AFTER `name`");
            $pdo->exec("UPDATE `institutes` SET `code` = '1R' WHERE `code` IS NULL OR `code` = ''");
        }

        // 5. Ensure UNIQUE indexes on students registration_number and certificate_number
        try {
            $pdo->exec("ALTER TABLE `students` ADD UNIQUE INDEX `registration_number` (`registration_number`)");
        } catch (Exception $e1) {}
        try {
            $pdo->exec("ALTER TABLE `students` ADD UNIQUE INDEX `certificate_number` (`certificate_number`)");
        } catch (Exception $e2) {}

        $done = true;
    } catch (Exception $e) {
        error_log("Database initialization error in smtp_mailer.php: " . $e->getMessage());
    }
}

/**
 * Get all SMTP settings as key => value array
 */
function get_smtp_settings($pdo)
{
    ensure_smtp_and_email_tables($pdo);
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%'");
    $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $defaults = [
        'smtp_enabled'    => '1',
        'smtp_host'       => 'smtp.gmail.com',
        'smtp_port'       => '587',
        'smtp_auth'       => '1',
        'smtp_username'   => '',
        'smtp_password'   => '',
        'smtp_encryption' => 'tls',
        'smtp_from_email' => 'no-reply@beliefpro.org',
        'smtp_from_name'  => 'BELIEFPRO LEARNING FORUM'
    ];

    return array_merge($defaults, $results);
}

/**
 * Save array of SMTP settings
 */
function save_smtp_settings($pdo, array $settings)
{
    ensure_smtp_and_email_tables($pdo);
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($settings as $key => $val) {
        if (str_starts_with($key, 'smtp_')) {
            $stmt->execute([$key, trim((string)$val)]);
        }
    }
}

/**
 * Pure PHP Socket-based SMTP Mailer Client
 */
class SmtpMailer
{
    private $host;
    private $port;
    private $auth;
    private $username;
    private $password;
    private $encryption;
    private $fromEmail;
    private $fromName;
    private $timeout = 15;
    public $debugLog = [];

    public function __construct(array $config)
    {
        $this->host       = $config['smtp_host'] ?? 'smtp.gmail.com';
        $this->port       = (int)($config['smtp_port'] ?? 587);
        $this->auth       = !empty($config['smtp_auth']) && $config['smtp_auth'] !== '0';
        $this->username   = $config['smtp_username'] ?? '';
        $this->password   = $config['smtp_password'] ?? '';
        $this->encryption = strtolower($config['smtp_encryption'] ?? 'tls');
        $this->fromEmail  = $config['smtp_from_email'] ?? 'no-reply@certiportal.com';
        $this->fromName   = $config['smtp_from_name'] ?? 'CertiPortal Certificate System';
    }

    private function log($msg)
    {
        $this->debugLog[] = date('Y-m-d H:i:s') . ' - ' . $msg;
    }

    public function send($toEmail, $subject, $htmlBody)
    {
        $this->debugLog = [];
        $this->log("Attempting to send email to <{$toEmail}> via SMTP Host: {$this->host}:{$this->port} (Encryption: {$this->encryption})");

        $socketHost = $this->host;
        if ($this->encryption === 'ssl') {
            $socketHost = 'ssl://' . $this->host;
        }

        $socket = @fsockopen($socketHost, $this->port, $errno, $errstr, $this->timeout);
        if (!$socket) {
            $this->log("Connection failed to {$socketHost}:{$this->port} - Error #{$errno}: {$errstr}");
            // Fallback to PHP native mail() if socket fails
            return $this->sendNativeMail($toEmail, $subject, $htmlBody);
        }

        $response = $this->readResponse($socket);
        $this->log("SERVER: " . trim($response));
        if (substr($response, 0, 3) !== '220') {
            fclose($socket);
            return $this->sendNativeMail($toEmail, $subject, $htmlBody);
        }

        // Send EHLO
        $this->sendCommand($socket, "EHLO " . gethostname());
        $response = $this->readResponse($socket);
        $this->log("EHLO Response: " . trim(str_replace("\r\n", " | ", $response)));

        // STARTTLS if requested
        if ($this->encryption === 'tls') {
            $this->sendCommand($socket, "STARTTLS");
            $response = $this->readResponse($socket);
            $this->log("STARTTLS Response: " . trim($response));

            if (substr($response, 0, 3) === '220') {
                $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                    $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                }
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                    $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
                }

                if (@stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
                    $this->log("TLS Encryption established successfully.");
                    // Re-send EHLO after TLS negotiation
                    $this->sendCommand($socket, "EHLO " . gethostname());
                    $this->readResponse($socket);
                } else {
                    $this->log("Failed to enable TLS encryption on socket.");
                    fclose($socket);
                    return $this->sendNativeMail($toEmail, $subject, $htmlBody);
                }
            }
        }

        // SMTP Authentication
        if ($this->auth) {
            $this->sendCommand($socket, "AUTH LOGIN");
            $response = $this->readResponse($socket);
            $this->log("AUTH LOGIN Response: " . trim($response));

            if (substr($response, 0, 3) === '334') {
                $this->sendCommand($socket, base64_encode($this->username));
                $response = $this->readResponse($socket);

                if (substr($response, 0, 3) === '334') {
                    $this->sendCommand($socket, base64_encode($this->password));
                    $response = $this->readResponse($socket);
                    $this->log("Password Response: " . trim($response));

                    if (substr($response, 0, 3) !== '235') {
                        $this->log("SMTP Authentication failed for user {$this->username}");
                        fclose($socket);
                        return false;
                    }
                } else {
                    $this->log("Username rejected by SMTP server");
                    fclose($socket);
                    return false;
                }
            }
        }

        // MAIL FROM
        $this->sendCommand($socket, "MAIL FROM: <{$this->fromEmail}>");
        $response = $this->readResponse($socket);
        $this->log("MAIL FROM Response: " . trim($response));
        if (substr($response, 0, 3) !== '250') {
            fclose($socket);
            return false;
        }

        // RCPT TO
        $this->sendCommand($socket, "RCPT TO: <{$toEmail}>");
        $response = $this->readResponse($socket);
        $this->log("RCPT TO Response: " . trim($response));
        if (substr($response, 0, 3) !== '250' && substr($response, 0, 3) !== '251') {
            fclose($socket);
            return false;
        }

        // DATA
        $this->sendCommand($socket, "DATA");
        $response = $this->readResponse($socket);
        $this->log("DATA Response: " . trim($response));
        if (substr($response, 0, 3) !== '354') {
            fclose($socket);
            return false;
        }

        // Headers and Body construction
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: quoted-printable";
        $headers[] = "From: =?UTF-8?B?" . base64_encode($this->fromName) . "?= <{$this->fromEmail}>";
        $headers[] = "To: <{$toEmail}>";
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
        $headers[] = "Date: " . date('r');
        $headers[] = "X-Mailer: CertiPortal SMTP Mailer v1.0";

        // Convert body to quoted-printable to guarantee lines <= 76 characters (RFC 2045 / RFC 5322 compliance)
        $encodedBody = quoted_printable_encode($htmlBody);
        $encodedBody = str_replace(["\r\n", "\r"], "\n", $encodedBody);
        $encodedBody = str_replace("\n", "\r\n", $encodedBody);

        $messageContent = implode("\r\n", $headers) . "\r\n\r\n" . $encodedBody . "\r\n.";
        $this->sendCommand($socket, $messageContent);
        $response = $this->readResponse($socket);
        $this->log("Send Content Response: " . trim($response));

        $success = (substr($response, 0, 3) === '250');

        $this->sendCommand($socket, "QUIT");
        fclose($socket);

        return $success;
    }

    private function sendCommand($socket, $cmd)
    {
        fputs($socket, $cmd . "\r\n");
    }

    private function readResponse($socket)
    {
        $response = "";
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] == ' ') {
                break;
            }
        }
        return $response;
    }

    private function sendNativeMail($toEmail, $subject, $htmlBody)
    {
        $this->log("Falling back to native PHP mail() function...");
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: quoted-printable";
        $headers[] = "From: " . sprintf("=?UTF-8?B?%s?= <%s>", base64_encode($this->fromName), $this->fromEmail);

        $encodedBody = quoted_printable_encode($htmlBody);
        $encodedBody = str_replace(["\r\n", "\r"], "\n", $encodedBody);
        $encodedBody = str_replace("\n", "\r\n", $encodedBody);

        $result = @mail($toEmail, $subject, $encodedBody, implode("\r\n", $headers));
        $this->log("Native mail() result: " . ($result ? "SUCCESS" : "FAILED"));
        return $result;
    }
}

/**
 * Dispatch Student Congratulation Email
 */
function sendStudentCongratulationEmail($studentId, $pdo, $forceSend = false)
{
    ensure_smtp_and_email_tables($pdo);
    $smtp_settings = get_smtp_settings($pdo);

    if (!$forceSend && empty($smtp_settings['smtp_enabled'])) {
        return [
            'success' => false,
            'message' => 'SMTP notifications are currently disabled in Admin settings.'
        ];
    }

    // Fetch complete student information
    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            c.name AS course_name,
            i.name AS institute_name,
            m.name AS mentor_name
        FROM students s
        LEFT JOIN courses c ON s.course_id = c.id
        LEFT JOIN institutes i ON s.institute_id = i.id
        LEFT JOIN mentors m ON s.mentor_id = m.id
        WHERE s.id = ?
    ");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        return [
            'success' => false,
            'message' => 'Student record not found.'
        ];
    }

    if (empty($student['email'])) {
        return [
            'success' => false,
            'message' => 'No student email address specified.'
        ];
    }

    // Render HTML template
    $templateFile = __DIR__ . '/../templates/email/congratulation.php';
    if (!file_exists($templateFile)) {
        return [
            'success' => false,
            'message' => 'Email template file not found.'
        ];
    }

    ob_start();
    $data = $student;
    include $templateFile;
    $htmlBody = ob_get_clean();

    $subject = "Congratulations " . strtoupper($student['name']) . "! Registration & Certificate Confirmation";

    $mailer = new SmtpMailer($smtp_settings);
    $success = $mailer->send($student['email'], $subject, $htmlBody);

    $errorMsg = "Failed to send email. Check SMTP credentials.";
    if (!$success && !empty($mailer->debugLog)) {
        $lastLog = end($mailer->debugLog);
        $errorMsg .= " (" . $lastLog . ")";
    }

    return [
        'success'  => $success,
        'message'  => $success ? "Congratulation email successfully sent to {$student['email']}" : $errorMsg,
        'debugLog' => $mailer->debugLog
    ];
}
