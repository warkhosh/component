<?php

namespace Warkhosh\Component\Url;

use Exception;

trait UrlPathMethods
{
    /**
     * Проверяет пути на корректность и записывает результат в $this->correct с результатами от проверок
     *
     * @note: пустые пути являются не корректными
     *
     * @return void
     */
    private function setPathResults(): void
    {
        foreach ($this->data as $key => $row) {
            $this->pathResults[$key] = (trim($row) != ''
                && $row === UrlHelper::getRemoveNoSemanticChar($row, '.', false));
        }
    }

    /**
     * Проверка значений путей на корректность для ЧПУ
     *
     * @note: без указания $limit, не будет строгой проверки на количество выбранных записей, а только по результатам выборки
     *
     * @param int|null $key проверка по порядковому номеру в массиве, а не ключу
     * @param int|null $limit если указано без $key, включает проверку по диапазону (отрицательное значение делает проверку с обратной стороны списка)
     * @param int $offset сдвиг
     * @return bool
     * @throws Exception
     */
    public function isCorrectPart(?int $key = null, ?int $limit = null, int $offset = 0): bool
    {
        if (empty($this->pathResults)) {
            $this->setPathResults();
        }

        $offset = getNum($offset);

        // Проверка наличие корректного пути в указанном списке значений
        if (! is_null($key) && $key > 0 && is_null($limit)) {
            $select = array_slice($this->pathResults, $offset);

            return array_key_exists((--$key), $select) ? $select[$key] : false;
        }

        // проверка с обратной стороны списка
        if (! is_null($key) && $key < 0 && is_null($limit)) {
            // отрицательное число превращаем в положительное
            $key = (getNum(trim(getMakeString($key), '-')) - 1);

            $reverse = array_reverse($this->pathResults);
            $select = array_slice($reverse, $offset);

            return array_key_exists($key, $select) ? $select[$key] : false;
        }

        $limit = (int)$limit;

        // Не указали конкретный ключ, делаем проверку указанных частей по $limit
        // Отрицательное число означает проверку с обратной стороны списка
        if (is_null($key) && ($limit > 0 || $limit < 0)) {
            if ($limit > 0) {
                $select = array_slice($this->pathResults, $offset, $limit);

                return (count($select) === $limit && ! in_array(false, $select));
            }

            if ($limit < 0) {
                // отрицательное число превращаем в положительное
                $limit = getNum(trim(getMakeString($limit), '-'));
                $reverse = array_reverse($this->pathResults);
                $select = array_slice($reverse, $offset, $limit);

                return (count($select) === $limit && ! in_array(false, $select));
            }
        }

        // если все значения default, проверить текущие пути на корректные ЧПУ
        return ! in_array(false, array_slice($this->pathResults, $offset));
    }

    /**
     * Возвращает список типов путей
     * Если передано число ищет значение по ключу в списке (отрицательное значение ищет с конца).
     * Если в $limit указано число возвращает не более этого количества записей
     *
     * @param int|null $key выборка по ключу
     * @param int|null $limit ограничение
     * @param int $offset сдвиг
     * @return array|string
     * @throws Exception
     */
    public function getTypes(?int $key = null, ?int $limit = null, int $offset = 0): array|string
    {
        if (! is_null($key) && $key > 0) {
            return array_key_exists((--$key), $this->types) ? $this->types[$key] : 'undefined';
        }

        if (! is_null($key) && $key < 0) {
            $key = (getNum(trim(getMakeString($key), '-')) - 1);
            $reverse_params = array_reverse($this->types);

            if (array_key_exists($key, $reverse_params)) {
                return $reverse_params[$key];
            }

            return 'undefined';
        }

        // Если ключ не указали, а запросили диапазон, то возвращаем массив.
        // Массив будет иметь указанное число записей или меньший диапазон значений если в списке нет нужных значений.
        if (! is_null($limit) && getNum($limit) > 0) {
            //$select = array_slice($this->types, $offset, $limit);
            //return array_replace(array_fill(0, $limit, 'undefined'), $select);
            return array_slice($this->types, $offset, $limit);
        }

        if (is_null($key)) {
            return $this->types;
        }

        if (getNum($key) === 0) {
            return 'undefined';
        }

        return $this->types;
    }

    /**
     * Возвращает конкретный путь если указали номер ключа в путях или список путей
     * Если передано число ищет значение по ключу в списке (отрицательное значение ищет с конца).
     * Если в $limit указано число возвращает не более этого количества записей
     *
     * @param int|null $key выборка по ключу
     * @param int|null $limit ограничение
     * @param int $offset сдвиг
     * @param string $default
     * @return array|string
     * @throws Exception
     */
    public function get(?int $key = null, ?int $limit = null, int $offset = 0, string $default = ''): array|string
    {
        if (! is_null($key) && $key > 0) {
            return array_key_exists((--$key), $this->data) ? $this->data[$key] : $default;
        }

        if (! is_null($key) && $key < 0) {
            $key = (getNum(trim(getMakeString($key), '-')) - 1);
            $reverse_params = array_reverse($this->data);

            if (array_key_exists($key, $reverse_params)) {
                return $reverse_params[$key];
            }

            return $default;
        }

        // Если ключ не указали, а запросили диапазон, то возвращаем массив.
        // Массив будет иметь указанное число записей или меньший диапазон значений если в списке нет нужных значений.
        if (! is_null($limit) && getNum($limit) > 0) {
            return array_slice($this->data, $offset, $limit);
        }

        return $this->data;
    }

    /**
     * Возвращает текущий путь
     *
     * @param string|null $glue символ объединения строк
     * @return array|string
     * @throws Exception
     */
    public function getPaths(?string $glue = null): array|string
    {
        $return = $this->get();

        if (is_string($glue)) {
            $return = '/'.join($glue, $return);
        }

        return $return;
    }

    /**
     * Последовательная проверка указанных путей на наличие
     *
     * @param array $paths
     * @param int $offset сдвиг
     * @param bool $strictLimit флаг отвечающий за строгую проверку количества путей
     * @return bool
     * @throws Exception
     */
    public function checkingPaths(array $paths = [], int $offset = 0, bool $strictLimit = true): bool
    {
        $offset = max($offset, 0);

        if (($count = count($paths)) > 0) {
            // Максимально возможные пути или только количество, что указали
            $limit = $strictLimit ? 9999 : count($paths);

            // Получаем реальное количество путей
            $selectPaths = $this->get(null, $limit, $offset);

            // Сравниваем текущее количество путей с указанным,
            // так мы исключаем лишнею работу когда проверяется условие не под текущий маршрут
            if (count($selectPaths) !== count($paths)) {
                return false;
            }

            $selectTypes = $this->getTypes(null, $count, $offset);

            foreach ($paths as $key => $path) {
                switch ($path) {
                    case "int:":
                        // если указали маску по числу и оно было указано в этом пути,
                        // заменяем значение числа на код из запроса
                        if (isset($selectTypes[$key]) && in_array($selectTypes[$key], ['int', 'num'])) {
                            $selectPaths[$key] = $path;
                        }

                        break;

                    case "num:":
                        // если указали маску по целому числу и оно было указано в этом пути,
                        // заменяем значение числа на код из запроса
                        if (isset($selectTypes[$key]) && $selectTypes[$key] === 'num') {
                            $selectPaths[$key] = $path;
                        }

                        break;

                    case "float:":
                        // если указали маску по числу с плавающей точкой и оно было указано в этом пути,
                        // заменяем значение числа на код из запроса
                        if (isset($selectTypes[$key]) && $selectTypes[$key] === 'float') {
                            $selectPaths[$key] = $path;
                        }

                        break;

                    case "str:":
                        // если указали маску по строке и она была указана в этом пути,
                        // заменяем значение числа на код из запроса
                        if (isset($selectTypes[$key]) && $selectTypes[$key] === 'str') {
                            $selectPaths[$key] = $path;
                        }

                        break;

                    case "mixed:":
                        // если указали маску по любому типу,
                        // заменяем значение числа на код из запроса
                        if (isset($selectTypes[$key]) && in_array($selectTypes[$key], ['str', 'int', 'num', 'float'])) {
                            $selectPaths[$key] = $path;
                        }

                        break;
                }
            }

            if (join('[§]', $selectPaths) === join('[§]', $paths)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Возвращает первый путь
     *
     * @param string|null $type признак типа возвращаемого значения
     * @return string
     * @throws Exception
     */
    public function first(?string $type = null): string
    {
        $url = $this->get(1);
        $url = ! is_array($url) ? $url : '';

        return match ($type) {
            "num" => getMakePositiveInteger($url),
            "int", "integer" => getMakeInteger($url),
            "float" => getMakeFloat($url),
            "string" => getMakeString($url),
            default => $url,
        };

    }

    /**
     * Проверяет первый путь на равенство с указанным значением
     *
     * @param string|null $name
     * @return bool
     */
    public function firstIs(?string $name = null): bool
    {
        if (gettype($name) === 'string' && isset($this->data[0]) && $this->data[0] === $name) {
            return true;
        }

        return false;
    }

    /**
     * Возвращает второй путь
     *
     * @param string|null $type признак типа возвращаемого значения
     * @return string
     * @throws Exception
     */
    public function second(?string $type = null): string
    {
        $url = $this->get(2);
        $url = ! is_array($url) ? $url : '';

        return match ($type) {
            "num" => getMakePositiveInteger($url),
            "int", "integer" => getMakeInteger($url),
            "float" => getMakeFloat($url),
            "string" => getMakeString($url),
            default => $url,
        };

    }

    /**
     * Проверяет второй путь на равенство с указанным значением
     *
     * @param string|null $name
     * @return bool
     */
    public function secondIs(?string $name = null): bool
    {
        if (gettype($name) === 'string' && isset($this->data[1]) && $this->data[1] === $name) {
            return true;
        }

        return false;
    }

    /**
     * Возвращает третий путь
     *
     * @param string|null $type признак типа возвращаемого значения
     * @return string
     * @throws Exception
     */
    public function third(?string $type = null): string
    {
        $url = $this->get(3);
        $url = ! is_array($url) ? $url : '';

        return match ($type) {
            "num" => getMakePositiveInteger($url),
            "int", "integer" => getMakeInteger($url),
            "float" => getMakeFloat($url),
            "string" => getMakeString($url),
            default => $url,
        };

    }

    /**
     * Проверяет третий путь на равенство с указанным значением
     *
     * @param string|null $name
     * @return bool
     */
    public function thirdIs(?string $name = null): bool
    {
        if (gettype($name) === 'string' && isset($this->data[2]) && $this->data[2] === $name) {
            return true;
        }

        return false;
    }

    /**
     * Возвращает четвертый путь
     *
     * @param string|null $type признак типа возвращаемого значения
     * @return string
     * @throws Exception
     */
    public function fourth(?string $type = null): string
    {
        $url = $this->get(4);
        $url = ! is_array($url) ? $url : '';

        return match ($type) {
            "num" => getMakePositiveInteger($url),
            "int", "integer" => getMakeInteger($url),
            "float" => getMakeFloat($url),
            "string" => getMakeString($url),
            default => $url,
        };

    }

    /**
     * Проверяет четвертый путь на равенство с указанным значением
     *
     * @param string|null $name
     * @return bool
     */
    public function fourthIs(?string $name = null): bool
    {
        if (gettype($name) === 'string' && isset($this->data[3]) && $this->data[3] === $name) {
            return true;
        }

        return false;
    }

    /**
     * Возвращает пятый путь
     *
     * @param string|null $type признак типа возвращаемого значения
     * @return string
     * @throws Exception
     */
    public function fifth(?string $type = null): string
    {
        $url = $this->get(5);
        $url = ! is_array($url) ? $url : '';

        return match ($type) {
            "num" => getMakePositiveInteger($url),
            "int", "integer" => getMakeInteger($url),
            "float" => getMakeFloat($url),
            "string" => getMakeString($url),
            default => $url,
        };

    }

    /**
     * Проверяет пятый путь на равенство с указанным значением
     *
     * @param bool|null $name
     * @return bool
     */
    public function fifthIs(?bool $name = null): bool
    {
        if (gettype($name) === 'string' && isset($this->data[4]) && $this->data[4] === $name) {
            return true;
        }

        return false;
    }

    /**
     * Возвращает шестой путь
     *
     * @param string|null $type признак типа возвращаемого значения
     * @return string
     * @throws Exception
     */
    public function sixth(?string $type = null): string
    {
        $url = $this->get(6);
        $url = ! is_array($url) ? $url : '';

        return match ($type) {
            "num" => getMakePositiveInteger($url),
            "int", "integer" => getMakeInteger($url),
            "float" => getMakeFloat($url),
            "string" => getMakeString($url),
            default => $url,
        };

    }

    /**
     * Проверяет шестой путь на равенство с указанным значением
     *
     * @param string|null $name
     * @return bool
     */
    public function sixthIs(?string $name = null): bool
    {
        if (gettype($name) === 'string' && isset($this->data[5]) && $this->data[5] === $name) {
            return true;
        }

        return false;
    }

    /**
     * Возвращает седьмой путь
     *
     * @param string|null $type признак типа возвращаемого значения
     * @return string
     * @throws Exception
     */
    public function seventh(?string $type = null): string
    {
        $url = $this->get(7);
        $url = ! is_array($url) ? $url : '';

        return match ($type) {
            "num" => getMakePositiveInteger($url),
            "int", "integer" => getMakeInteger($url),
            "float" => getMakeFloat($url),
            "string" => getMakeString($url),
            default => $url,
        };

    }

    /**
     * Проверяет седьмой путь на равенство с указанным значением
     *
     * @param string|null $name
     * @return bool
     */
    public function seventhIs(?string $name = null): bool
    {
        if (gettype($name) === 'string' && isset($this->data[6]) && $this->data[6] === $name) {
            return true;
        }

        return false;
    }

    /**
     * Возвращает последний путь
     *
     * @param string|null $type признак типа возвращаемого значения
     * @return string
     * @throws Exception
     */
    public function last(?string $type = null): string
    {
        $url = $this->get(-1);
        $url = ! is_array($url) ? $url : '';

        return match ($type) {
            "num" => getMakePositiveInteger($url),
            "int", "integer" => getMakeInteger($url),
            "float" => getMakeFloat($url),
            "string" => getMakeString($url),
            default => $url,
        };

    }

    /**
     * Проверяет последний путь на равенство с указанным значением
     *
     * @param string|null $name
     * @return bool
     */
    public function lastIs(?string $name = null): bool
    {
        if (gettype($name) === 'string' && isset($this->data[(count($this->data) - 1)])) {
            return ($this->data[(count($this->data) - 1)] === $name);
        }

        return false;
    }

    /**
     * Проверка длинны
     *
     * @param int|null $equal
     * @return int
     */
    public function amountIs(?int $equal = null): int
    {
        if ($equal === count($this->data)) {
            return true;
        }

        return false;
    }

    /**
     * Возвращает количество путей
     *
     * @return int
     */
    public function getAmount(): int
    {
        return count($this->data);
    }

    /**
     * Возвращает название файла
     *
     * @return string|null
     */
    public function getFileName(): string|null
    {
        return empty($this->file) ? null : $this->file;
    }

    /**
     * @param string|null $name
     * @return bool
     */
    public function fileIs(?string $name = null): bool
    {
        if ($this->file === $name) {
            return true;
        }

        return false;
    }

    /**
     * @return $this
     */
    private function reset(): static
    {
        $this->data = [];

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
