<?php

namespace Warkhosh\Component\Mail;

use Exception;
use Warkhosh\Variable\VarStr;

/**
 * MailChecker
 *
 * @version v1.3
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
     * @return string|true
     */
    public function isCorrect(mixed $email): string|bool
    {
        try {
            if (! empty($check = $this->basicCorrect($email))) {
                return $check;
            }

            [$user, $domain] = explode('@', $email);

            // Имя длиннее 2 символов
            if (mb_strlen($user) < 2) {
                throw new Exception("Recipient name is shorter than 2 characters");
            }

            // Проверка символа точки в домене минимум один раз
            if (count(explode(".", $domain)) < 2) {
                throw new Exception("There is no [dot] symbol in the email address domain");
            }

            // Проверка наличия букв в домене (без точек)
            if (mb_strlen(str_replace('.', '', $domain)) <= 3) {
                throw new Exception("There are no characters in the email address domain");
            }

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Проверка домена у email адреса на причастность к временным
     *
     * @param mixed $email
     * @return string|true
     */
    public function isFakeDomain(mixed $email): string|bool
    {
        try {
            if (! empty($check = $this->basicCorrect($email))) {
                return $check;
            }

            [, $domain] = explode('@', $email);

            if (in_array($domain, $this->fakerDomainList, true)) {
                throw new Exception("The domain belongs to temporary services");
            }

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Проверка домена у email адреса на причастность к конкурентам
     *
     * @param mixed $email
     * @return string|true
     */
    public function isCompetitorList(mixed $email): string|bool
    {
        try {
            if (! empty($check = $this->basicCorrect($email))) {
                return $check;
            }

            [, $domain] = explode('@', $email);

            if (in_array($domain, $this->competitorList, true)) {
                throw new Exception("The domain belongs to competitors");
            }

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Базовая проверка email адреса
     *
     * Проверяется что он строка, она не содержит не чего кроме символов и в ней есть разделитель собака
     *
     * @param mixed $email
     * @return string|true
     */
    private function basicCorrect(mixed $email): string|bool
    {
        try {
            if (! is_string($email) || empty($email)) {
                throw new Exception("The email address is not a string or is empty");
            }

            // Конвертируем символы в UTF-8
            $email = VarStr::getTransformToEncoding($email, 'UTF-8');

            // Удаляем " \n\r\t\v\0"
            $email = trim($email);

            if (empty($email)) {
                throw new Exception("The email address turned out to be empty after converting to UTF-8");
            }

            // Проверка, что после удаления пробелов и кареток длинна введенного адреса осталось прежней
            if ($email !== preg_replace("/\s/", "", $email)) {
                throw new Exception("Email address contains control characters and spaces");
            }

            // Проверка наличия символа собаки
            if (preg_replace('/[^@]/ium', '', $email) !== '@') {
                throw new Exception("One comma character was not found in the email address");
            }

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
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
