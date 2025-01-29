<?php

namespace Warkhosh\Component\Traits;

/**
 * Email
 *
 * @package Warkhosh\Component\Traits
 */
trait Email
{
    /**
     * Проверяет Email на корректные символы
     *
     * @param string|null $email
     * @return bool
     */
    public static function isCorrectEmail(?string $email = null): bool
    {
        $email = (string)$email;

        if (preg_replace('/[a-z0-9\.\-\_]/ium', '', $email) !== '@') {
            return false;
        }

        $part = explode("@", $email);

        if (count($part) !== 2 || (trim($part[0]) === "" || trim($part[1]) == "")) {
            return false;
        }

        return true;
    }

    /**
     * Проверяет никнейм на корректные символы
     *
     * @param string|null $name
     * @return bool
     */
    public static function isCorrectNickname(?string $name = null): bool
    {
        return (preg_replace('/[a-z0-9а-я\-\_\ ]/ium', '', (string)$name) === '');
    }
}
