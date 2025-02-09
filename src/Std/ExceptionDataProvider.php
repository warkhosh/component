<?php

namespace Warkhosh\Component\Std;

use Exception;
use Throwable;

/**
 * ExceptionDataProvider
 *
 * Класс для своих исключений с дополнительными полями $field, $signal, $system
 *
 * @deprecated заменить на ImprovedException из warkhosh/exception!
 */
class ExceptionDataProvider extends Exception implements Throwable
{
    // Класс оставил до лета 2025 как напоминание
}
