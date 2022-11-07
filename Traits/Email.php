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
     * @param null|string $email
     * @return bool
     */
    static public function isCorrectEmail(?string $email = null)
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
     * @param null|string $name
     * @return bool
     */
    static public function isCorrectNickname(?string $name = null)
    {
        return (preg_replace('/[a-z0-9а-я\-\_\ ]/ium', '', (string)$name) === '');
    }
}