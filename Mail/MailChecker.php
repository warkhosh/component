<?php

namespace Warkhosh\Component\Mail;

use Exception;
use Warkhosh\Variable\VarStr;

/**
 * MailChecker
 *
 * @version v1.1
 * @package Ekv\Framework\Components\Mail
 */
class MailChecker
{
    /**
     * Disposable Temporary Email Domain list
     *
     * @var array
     */
    protected array $fakerDomainList = [];

    /**
     * Список конкурентов
     *
     * @var array
     */
    protected array $competitorList = [];

    public function __construct()
    {
    }

    /**
     * Проверка адреса на корректность с точки зрения букв
     *
     * @param mixed $email
     * @return bool
     */
    public function isCorrect(mixed $email): bool
    {
        try {
            if ($this->basicCorrect($email) !== true) {
                return false;
            }

            [$user, $domain] = explode('@', $email);

            // Имя длиннее 2 символов
            if (mb_strlen($user) < 2) {
                return false;
            }

            // Проверка символа точки в домене минимум один раз
            if (count(explode(".", $domain)) < 2) {
                return false;
            }

            // Проверка наличия букв в домене (без точек)
            if (mb_strlen(str_replace('.', '', $domain)) > 3) {
                return false;
            }

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Проверка домена у email адреса на причастность к временным
     *
     * @param mixed $email
     * @return bool
     */
    public function isFakeDomain(mixed $email): bool
    {
        try {
            if ($this->basicCorrect($email) !== true) {
                return false;
            }

            [, $domain] = explode('@', $email);

            if (in_array($domain, $this->fakerDomainList, true)) {
                return false;
            }

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Проверка домена у email адреса на причастность к конкурентам
     *
     * @param mixed $email
     * @return bool
     */
    public function isCompetitorList(mixed $email): bool
    {
        try {
            if ($this->basicCorrect($email) !== true) {
                return false;
            }

            [, $domain] = explode('@', $email);

            if (in_array($domain, $this->competitorList, true)) {
                return false;
            }

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Базовая проверка email адреса
     *
     * Проверяется что он строка, она не содержит не чего кроме символов и в ней есть разделитель собака
     *
     * @param mixed $email
     * @return bool
     */
    private function basicCorrect(mixed $email): bool
    {
        try {
            if (! is_string($email) || empty($email)) {
                return false;
            }

            // Конвертируем символы в UTF-8
            $email = VarStr::getTransformToEncoding($email, 'UTF-8');

            // Удаляем " \n\r\t\v\0"
            $email = trim($email);

            if (empty($email)) {
                return false;
            }

            // Проверка, что после удаления пробелов и кареток длинна введенного адреса осталось прежней
            if ($email !== preg_replace("/\s/", "", $email)) {
                return false;
            }

            // Проверка наличия символа собаки
            if (preg_replace('/[^@]/ium', '', $email) !== '@') {
                return false;
            }

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Переопределяет список доменов для проверки временного email
     *
     * @param array $fakerDomainList
     * @return void
     */
    public function setFakerDomainList(array $fakerDomainList): void
    {
        $this->fakerDomainList = $fakerDomainList;
    }

    /**
     * Переопределяет список доменов конкурентов для проверки email
     *
     * @param array $competitorList
     * @return void
     */
    public function setCompetitorList(array $competitorList): void
    {
        $this->competitorList = $competitorList;
    }
}
