<?php

namespace FriendsOfRedaxo\Security;

use Exception;
use rex;
use rex_addon;
use rex_config;
use rex_error_handler;
use rex_file;
use rex_mailer;
use rex_markdown;
use rex_path;
use rex_request;
use rex_response;
use rex_system_report;
use Throwable;

use function array_slice;
use function count;
use function is_array;

use const PHP_EOL;
use const PHP_SAPI;

final class ErrorNotification extends rex_error_handler
{
    /** @var string */
    public const email_name = 'security Info';

    /** @var string[] */
    private const HEADERS = ['Function', 'File', 'Line'];

    public static function init(): void
    {
        if (1 != rex_config::get('security', 'error_notification_status')) {
            return;
        }

        set_exception_handler(/**
         * @throws \PHPMailer\PHPMailer\Exception
         */ static function (\PHPMailer\PHPMailer\Exception|Throwable $exception) {
            self::handleException($exception);
        });
    }

    public static function getEMail(): string
    {
        return empty(rex_config::get('security', 'error_notification_email')) ? rex::getErrorEmail() : (string) rex_config::get('security', 'error_notification_email');
    }

    public static function getName(): string
    {
        return empty(rex_config::get('security', 'error_notification_name')) ? self::email_name : (string) rex_config::get('security', 'error_notification_email');
    }

    /**
     * Handles the given Exception.
     *
     * @param Throwable|Exception $exception The Exception to handle
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function handleException($exception): void
    {
        // in case exceptions happen early - before symfony-console doRun()
        if ('cli' === PHP_SAPI) {
            /** @psalm-taint-escape html */ // actually it is not escaped, it is not necessary in cli output
            $exceptionString = $exception->__toString();
            echo $exceptionString;
            exit(1);
        }

        $bugBody = self::getMarkdownReport($exception);

        // replace multiple spaces with one
        $bugBodyCompressed = (string) preg_replace('/ +/', ' ', $bugBody);
        $markdown_whoops = $bugBodyCompressed;

        if ('0' == rex_config::get('security', 'error_notification_package')) {
            // direct email
            $mail = new rex_mailer();
            $mail->AddAddress(self::getEMail(), self::getName());
            $mail->Subject = 'security - Error: Reporting ' . $exception->getMessage();
            $mail->MsgHTML(rex_markdown::factory()->parse($markdown_whoops, true));
            $mail->AltBody = $markdown_whoops;
            if (!$mail->Send()) {
                // Mail failed - log Exception
                rex_file::put(rex_addon::get('security')->getDataPath('error_notification/' . time() . '.log.md'), $markdown_whoops);
            }
        } elseif ('1' == rex_config::get('security', 'error_notification_package')) {
            // log - bundle for action
            rex_file::put(rex_addon::get('security')->getDataPath('error_notification/' . time() . '.log.md'), $markdown_whoops);
        }

        parent::handleException($exception);
    }

    /**
     * @return array<int, string>
     */
    public static function getLogFiles(): array
    {
        $log_files = scandir(rex_addon::get('security')->getDataPath('error_notification'));
        if (!is_array($log_files)) {
            $log_files = [];
        }

        return array_diff($log_files, ['.', '..']);
    }

    public static function deleteLogFiles(): void
    {
        foreach (self::getLogFiles() as $file) {
            rex_file::delete(rex_addon::get('security')->getDataPath('error_notification/' . $file));
        }
    }

    public static function downloadLogFiles(): void
    {
        $content = [];
        foreach (self::getLogFiles() as $file) {
            $content[] = rex_file::get(rex_addon::get('security')->getDataPath('error_notification/' . $file));
        }

        $fileName = 'security_logs_' . date('YmdHis') . '.log';
        header('Content-Disposition: attachment; filename="' . $fileName . '"; charset=utf-8');
        rex_response::sendContent(implode("\n\n\n\n\n\n-----\n\n\n\n\n\n", $content), 'application/octetstream');
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function sendBundle(int $interval = 3600): bool // 60*60 = 3600 (1 hour)
    {
        $files = self::getLogFiles();
        $fromtime = time() - $interval;
        $sendfiles = [];

        if ([] !== $files) {
            foreach (array_slice($files, 0, 20) as $file) {
                $parts = preg_split('#[.]#', $file);
                if ($parts[0] > $fromtime) {
                    $sendfiles[] = $file;
                }
            }

            if (0 < count($sendfiles)) {
                $mail = new rex_mailer();
                $mail->AddAddress(self::getEMail(), self::getName());
                $mail->Subject = 'security - Error: Bundle-Reporting';
                $mail->MsgHTML('security - Error: Bundle-Reporting');
                $mail->AltBody = 'security - Error: Bundle-Reporting';
                foreach ($sendfiles as $sendfile) {
                    $mail->addAttachment(rex_addon::get('security')->getDataPath('error_notification/' . $sendfile));
                }

                try {
                    if (!$mail->Send()) {
                        // Mail failed - log Exception
                        return false;
                    }
                } catch (\PHPMailer\PHPMailer\Exception) {
                    return false;
                }
            }
        }

        return true;
    }

    private static function getMarkdownReport(Throwable|Exception $exception): string
    {
        $file = rex_path::relative($exception->getFile());

        $markdown = "\n##Error Report:\n\n";
        $markdown .= '| Key | Value |' . PHP_EOL;
        $markdown .= '| --- | --- |' . PHP_EOL;
        $markdown .= '| **key:** | ' . rex_config::get('security', 'error_notification_key') . ' |' . PHP_EOL;
        $markdown .= '| **unixtime with microtime:** | ' . microtime(true) . " |\n";
        $markdown .= '| **' . $exception::class . ":** | {$exception->getMessage()} |\n";
        $markdown .= "| **File:** | $file |\n";
        $markdown .= "| **Line:** | {$exception->getLine()} |\n";
        if (rex_server('REQUEST_URI')) {
            $markdown .=
                '| **Request-Uri:** | ' . rex_server('REQUEST_URI') . " |\n" .
                '| **Request-Method:** | ' . strtoupper(rex_request::requestMethod()) . " |\n";
        }

        $trace = [];
        $widths = [mb_strlen(self::HEADERS[0]), mb_strlen(self::HEADERS[0]), mb_strlen(self::HEADERS[0])];

        foreach ($exception->getTrace() as $frame) {
            $function = $frame['function'];
            if (isset($frame['class'])) {
                $function = $frame['class'] . $frame['type'] . $function;
            }

            $file = isset($frame['file']) ? rex_path::relative($frame['file']) : '';
            $line = $frame['line'] ?? '';

            $trace[] = [$function, $file, $line];

            $widths[0] = max($widths[0], mb_strlen($function));
            $widths[1] = max($widths[1], mb_strlen($file));
            $widths[2] = max($widths[2], mb_strlen($line));
        }

        $table = '| ' . str_pad(self::HEADERS[0], $widths[0]) . ' | ' . str_pad(self::HEADERS[1], $widths[1]) . ' | ' . str_pad(self::HEADERS[2], $widths[2]) . " |\n";
        $table .= '| ' . str_repeat('-', $widths[0]) . ' | ' . str_repeat('-', $widths[1]) . ' | ' . str_repeat('-', $widths[2]) . " |\n";

        foreach ($trace as $row) {
            $table .= '| ' . str_pad($row[0], $widths[0]) . ' | ' . str_pad($row[1], $widths[1]) . ' | ' . str_pad($row[2], $widths[2]) . " |\n";
        }

        $markdown .= "\n" . <<<OUTPUT
            $table
            OUTPUT;

        $markdown .= strip_tags(rex_system_report::factory()->asMarkdown());

        foreach (['$_SERVER' => $_SERVER, '$_REQUEST' => $_REQUEST, '$_POST' => $_POST, '$_GET' => $_GET, '$_COOKIE' => $_COOKIE, '$_FILES' => $_FILES, '$_SESSION' => $_SESSION] as $type => $vars) {
            $markdown .= "\n\n------------------------------------------------\n\n" . $type . ": \n";
            $markdown .= print_r($vars, true);
        }

        return $markdown;
    }
}
