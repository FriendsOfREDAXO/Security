<?php

namespace FriendsOfRedaxo\Securit;

class Backendlogin
{
    /**
     * @throws rex_exception
     */
    public static function init(): bool
    {
        if (rex::isBackend() && rex_backend_login::hasSession()) {
            if (!isset($_SESSION[rex::getProperty('instname'). '_backend']['backend_login']['securit_STAMP'])) {
                $_SESSION[rex::getProperty('instname'). '_backend']['backend_login']['securit_STAMP'] = time();
            }

            $start_stamp = $_SESSION[rex::getProperty('instname'). '_backend']['backend_login']['securit_STAMP'];

            if ($start_stamp + self::getSessionOverallDuration() > time()) {
                // alles ist gut.
                return true;
            }

            // Zeit ist abgelaufen. be_session killen
            rex_backend_login::deleteSession();
            return false;
        }
        return true;
    }

    public static function getSessionOverallDuration(): int
    {
        $be_login_overall_duration = rex_config::get('securit', 'be_login_overall_duration', 0);
        if (!is_scalar($be_login_overall_duration) || $be_login_overall_duration < 300) {
            $be_login_overall_duration = 24 * 60 * 60; // 24h
        }
        return (int) $be_login_overall_duration;
    }

    public static function setSessionOverallDuration(int $duration): int
    {
        if ($duration < 1) {
            $duration = 24 * 60 * 60; // 24h
        }
        rex_config::set('securit', 'be_login_overall_duration', $duration);
        return $duration;
    }
}
