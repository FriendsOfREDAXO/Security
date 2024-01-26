<?php

namespace FriendsOfRedaxo\Securit;

final class Health
{
    /**
     * @var string
     */
    private const REQUEST_PARAMS = 'securit_health';

    public static function checkRequest(): void
    {
        if ('' != \rex_request::get(self::REQUEST_PARAMS)) {
            self::getStatusPage();
        }
    }

    public static function getStatusPage(): void
    {
        \rex_response::sendContent(\rex_response::HTTP_OK, \rex_response::HTTP_OK);
    }

    public static function getLink(): string
    {
        if (\rex_addon::get('yrewrite')->isAvailable()) {
            return \rex_yrewrite::getFullPath().'?'.self::REQUEST_PARAMS.'=1';
        }

        return 'http'.('https' == rex_server('REQUEST_SCHEME') ? 's' : '').'://'. rex_server('HTTP_HOST').'?'.self::REQUEST_PARAMS.'=1';
    }
}
