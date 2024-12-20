<?php

namespace Warkhosh\Component\Config;

use Warkhosh\Component\Storage\AppStorage;
use Warkhosh\Component\Traits\Singleton;
use Warkhosh\Variable\VarArray;
use Warkhosh\Variable\VarStr;
use Throwable;
use Exception;

/**
 * Class AppConfig
 *
 * Подгружает и хранит настройки конфигов с параметрами в виде массива для использования с разных частях кода
 *
 * @package \Warkhosh\Component\Config
 * @version 1.1
 */
class AppConfig
{
    use Singleton;

    /**
     * Список данных
     *
     * @var array $data
     */
    private array $data = [];

    /**
     * Список загруженных конфигов
     *
     * @var array $data
     */
    private array $files = [];

    /**
     * The path
     *
     * @var string
     */
    protected string $basePath;

    /**
     * Set the base path for the application.
     *
     * @param string $path
     * @return void
     */
    public function setBasePath(string $path = ''): void
    {
        $this->basePath = rtrim($path, '\/');
    }


    /**
     * Get the base path of the Laravel installation.
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }


    /**
     * Получить элемент из массива с использованием нотации "точка".
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get(?string $key = null, mixed $default = null): mixed
    {
        try {
            if (is_null($key)) { // Без ключа отдаём все
                return $this->data;
            }

            $keys = VarArray::explode('.', $key);

            if (is_null($file = VarArray::getFirst($keys))) {
                throw new Exception("Bed config file: ".VarStr::getMake($key));
            }

            if (! (isset($this->files[$file]) && array_key_exists($file, $this->files))) {
                $this->load($file);
            }

            if (count($keys) > 1) {
                $array = $this->data;

                foreach ($keys as $segment) {
                    if (! is_array($array) || ! array_key_exists($segment, $array)) {
                        return $default;
                    }

                    $array = $array[$segment];
                }

                return $array;
            }

            if (isset($this->data[$key]) && array_key_exists($key, $this->data)) {
                return $this->data[$key];
            }

            return $default;

        } catch (Throwable $e) {
            return $default;
        }
    }


    /**
     * @param string|null $file
     * @return void
     */
    protected function load(?string $file = null): void
    {
        if (! is_string($file) && ! mb_strlen($file) >= 1) {
            return;
        }

        // Проверка загружености конфига для препятствия перезаписи
        if (isset($this->files[$file]) && array_key_exists($file, $this->files)) {
            return;
        }

        if (! array_key_exists($file, $this->data)) {
            $fileName = '/'.ucfirst(ltrim(trim($file), '/')).'.php';
            $path = $this->getBasePath();

            if ((new AppStorage())->exists("{$path}{$fileName}")) {
                $data = include "{$path}{$fileName}";
                $this->data[$file] = $data;
                $this->files[$file] = true;

            } else {
                // Всегда фиксируем запрошенные данные что-бы повторно сюда не попадать
                $this->data[$file] = [];
                $this->files[$file] = false;
            }
        }
    }
}
