<?php

namespace Warkhosh\Component\Storage;

use Warkhosh\Variable\VarArray;
use Warkhosh\Variable\VarStr;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use Exception;

class AppStorage
{
    /**
     * Определить, существует ли файл или каталог
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Проверка существования файла
     * Вернёт переданный список, но со значениями которые были проверены и подтверждены как файлы.
     *
     * @note еЕсли передали аргумент как строку, то в случае наличия файла она будет возращена иначе вернется NULL
     *
     * @param array|string $files - значение(я) которые нужно проверить на существование файла
     * @return array|string|null
     */
    public function getAvailableFile(array|string $files): array|string|null
    {
        $list = is_array($files);
        $files = $list ? $files : (array)$files;

        foreach ($files as $key => $file) {
            if (! file_exists($file)) {
                unset($files[$key]);
            }
        }

        if (! $list && count($files) === 0) {
            return null;
        }

        return $list ? $files : array_shift($files);
    }

    /**
     * Проверка наличия файлов в указанной директории
     *
     * @param string $dir
     * @return bool
     * @throws Exception
     */
    public function getHasDirectoryFiles(string $dir): bool
    {
        if (! is_dir($dir)) {
            throw new Exception("The non-existent directory {$dir} is specified");
        }

        try {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_file($dir.$file)) {
                        $return = true;
                        break;
                    }
                }

                closedir($dh);
            }

        } catch (Throwable $e) {
            if (isset($dh)) {
                closedir($dh);
            }
        }

        return isset($return) && $return;
    }

    /**
     * Чтение файла
     *
     * @param string $path
     * @param bool $silent - признак тихого ответа без использования trigger_error()
     * @return string
     */
    public function get(string $path, bool $silent = true): string
    {
        if ($this->isFile($path)) {
            return file_get_contents($path);

        } elseif (! $silent) {
            if (empty($path)) {
                trigger_error("Empty path");

            } else {
                trigger_error("`{$path}` not found");
            }
        }

        return "";
    }

    /**
     * Требовать данный файл один раз
     *
     * @param string $file
     * @return void
     */
    public function requireOnce(string $file): void
    {
        require_once $file;
    }

    /**
     * Запись в файл
     *
     * @param string $path
     * @param string $contents
     * @param int $lock
     * @return false|int
     */
    public function put(string $path, string $contents = '', int $lock = LOCK_EX): false|int
    {
        return file_put_contents($path, $contents, $lock);
    }

    /**
     * Создаёт файл
     *
     * @param string $path
     * @param string $contents
     * @param int $lock
     * @return bool
     */
    public function create(string $path, string $contents = '', int $lock = LOCK_EX): bool
    {
        return ! ($this->put($path, $contents, $lock) === false);
    }

    /**
     * Добавление контента в начало файла
     *
     * @param string $path
     * @param string $contents
     * @return int
     */
    public function prepend(string $path, string $contents): int
    {
        if ($this->exists($path)) {
            return $this->put($path, $contents.$this->get($path));
        }

        return $this->put($path, $contents);
    }

    /**
     * Дописывать в файл
     *
     * @param string $path
     * @param string $contents
     * @return int
     */
    public function append(string $path, string $contents): int
    {
        return file_put_contents($path, $contents, FILE_APPEND | LOCK_EX);
    }

    /**
     * Удалить файл по заданному пути
     *
     * @note если передали 1 параметр, но массивом в результате будет такой-же массив, где значения будут ключами, а значения будут содержать результат операции
     *
     * @param array|string $paths путь к файлу
     * @param bool $silent признак тихого удаления без использования подавления ошибки
     * @return array|bool
     */
    public function delete(array|string $paths, bool $silent = true): array|bool
    {
        $isList = is_array($paths);
        $paths = $isList ? $paths : (array)$paths;
        $success = [];

        if (count($paths)) {
            $paths = array_flip(VarArray::getRemove($paths, [false, true, null]));

            foreach ($paths as $path => $row) {
                try {
                    if (is_file($path)) {
                        if ($silent && @unlink($path)) {
                            $success[$path] = true;

                        } elseif (unlink($path)) {
                            $success[$path] = true;

                        } else {
                            $success[$path] = false;
                        }

                    } elseif (is_dir($path)) {
                        if ($silent && @rmdir($path)) {
                            $success[$path] = true;

                        } elseif (rmdir($path)) {
                            $success[$path] = true;

                        } else {
                            $success[$path] = false;
                        }
                    } else {
                        $success[$path] = false;
                    }

                } catch (Throwable $e) { // php 7.1
                    $success[$path] = false;

                    if (! $silent) {
                        trigger_error($e->getMessage(), E_USER_ERROR);
                    }
                }
            }
        }

        return $isList ? $success : VarArray::getFirst($success);
    }

    /**
     * Перемещение файла в новое место
     *
     * @param string $path
     * @param string $target
     * @return bool
     */
    public function move(string $path, string $target): bool
    {
        return rename($path, $target);
    }

    /**
     * Скопировать файл в новое место
     *
     * @param string $path
     * @param string $target
     * @return bool
     */
    public function copy(string $path, string $target): bool
    {
        return copy($path, $target);
    }

    /**
     * Извлечь <Имя> файла из пути к файлу
     *
     * @param string $path
     * @return string
     */
    public function name(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Извлечь <расширение> файла из пути к файлу
     *
     * @param string $path
     * @return string
     */
    public function extension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Получить <Тип> файла указанного файла
     *
     * @param string $path
     * @return string
     */
    public function type(string $path): string
    {
        return filetype($path);
    }

    /**
     * Получить MIME-Тип файла
     *
     * @param string $path
     * @return false|string
     */
    public function mimeType(string $path): false|string
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * Получить Размер заданного файла
     *
     * @param string $path
     * @return int
     */
    public function size(string $path): int
    {
        return filesize($path);
    }

    /**
     * Получить время последнего изменения файла
     *
     * @param string $path
     * @return int
     */
    public function lastModified(string $path): int
    {
        return filemtime($path);
    }

    /**
     * Определить, если данный путь является каталогом
     *
     * @param string $directory
     * @return bool
     */
    public function isDirectory(string $directory): bool
    {
        return is_dir($directory);
    }

    /**
     * Определить, если данный путь доступен для записи
     *
     * @param string $path
     * @return bool
     */
    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * Определить, если данный путь файл
     *
     * @param string $file
     * @return bool
     */
    public function isFile(string $file): bool
    {
        return is_file($file);
    }

    /**
     * Найти имена путей, соответствующие заданному шаблону
     *
     * @param string $pattern
     * @param int $flags
     * @return array
     */
    public function glob(string $pattern, int $flags = 0): array
    {
        return glob($pattern, $flags);
    }

    /**
     * Получить массив всех файлов в каталоге
     *
     * @param string $directory
     * @return array
     */
    public function files(string $directory): array
    {
        $glob = glob($directory.'/*');

        if ($glob === false) {
            return [];
        }

        // To get the appropriate files, we'll simply glob the directory and filter
        // out any "files" that are not truly files so we do not end up with any
        // directories in our list, but only true files within the directory.
        return array_filter($glob, function ($file) {
            return filetype($file) == 'file';
        });
    }

    /**
     * Создание каталога
     *
     * @param string $path
     * @param int $mode права для создаваемой директории
     * @param bool $recursive Разрешает создание вложенных директорий, указанных в pathname
     * @param bool $force подавляет ошибку\исключение при неудаче создания директории
     * @return bool
     */
    public function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        if ($force) {
            try {
                return @mkdir($path, $mode, $recursive);

            } catch (Throwable $e) {
                return false;
            }
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * Очистить указанный каталог от всех файлов и папок и подпапок
     *
     * @param string $directory папка
     * @param int $depth количество вложенных итераций для удаления данных в дочерних папках (0 = no limit)
     * @param bool $silent признак тихого удаления без использования подавления ошибки
     * @return bool
     * @throws Throwable
     */
    public function cleanDirectory(string $directory, int $depth = 12, bool $silent = true): bool
    {
        try {
            $iterator = new RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS);
            $recursiveIterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($recursiveIterator as $path) {
                try {
                    if ($depth > 0 && $recursiveIterator->getDepth() > $depth) {
                        continue;
                    }

                    if ($path->isDir() && ! $path->isLink()) {
                        rmdir($path->getPathname());
                    } else {
                        unlink($path->getPathname());
                    }
                } catch (Throwable $e) {
                    if ($silent !== true) {
                        throw $e;
                    }
                }
            }

            return true;

        } catch (Throwable $e) {
            if ($silent !== true) {
                throw $e;
            }
        }

        return false;
    }

    /**
     * Возвращает список сущностей в указанной директории
     *
     * @param string $dir
     * @param string|null $basePath
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function getDirEssences(string $dir, ?string $basePath = null, array $options = []): array
    {
        if (is_string($basePath)) {
            $dir = VarStr::getRemoveStart($basePath, $dir);
        }

        $relative = rtrim($dir, '/');
        $dir = rtrim((string)$basePath.$relative, '/');
        $limit = key_exists('limit', $options) ? $options['limit'] : 10000;
        $ignore = key_exists('ignore', $options) ? (array)$options['ignore'] : ['.gitignore'];
        $pictures = ['png', 'gif', 'jpg', 'jpeg', 'bmp', 'tiff'];
        $pictures = key_exists('pictures', $options) ? (array)$options['pictures'] : $pictures;
        $returnTypes = key_exists('return_types', $options) ? (array)$options['return_types'] : ["dir", "file"];
        $sort = key_exists('sort', $options) && in_array($options['sort'], [0, 1, 2]) ? (int)$options['sort'] : 0;
        $return = [];

        if (isset($options['cache_clear']) && isTrue($options['cache_clear'])) {
            clearstatcache();
        }

        if (is_dir($dir)) {
            $dirs = $files = [];
            $list = scandir($dir, $sort);

            if (! is_array($list)) {
                throw new Exception("An error occurred while reading the directory");
            }

            foreach ($list as $str) {
                if (empty($str2 = trim($str, './'))) {
                    continue;
                }

                if (in_array($str, $ignore)) {
                    continue;
                }

                if ($limit === 0) {
                    break;
                }

                $essence = "{$dir}/{$str}";

                // время изменения файла
                $dateTime = filemtime($essence);
                $owner = posix_getpwuid(fileowner($essence));
                $permission = substr(sprintf('%o', fileperms($essence)), -4);
                $info = pathinfo($essence);
                $limit--;

                if (is_dir($essence)) {
                    $dirs[] = [
                        "name" => $str,
                        "path" => $dir,
                        "dir" => $essence,
                        "relative" => $relative,
                        "type" => 'dir',
                        "owner" => $owner['name'] ?? '',
                        "perm" => $permission,
                        "date_time" => $dateTime,
                        "size" => 0,
                        "info" => $info,
                    ];

                } elseif (is_file($essence)) {
                    $picture = isset($info["extension"]) && in_array($info["extension"], $pictures);

                    $files[] = [
                        "name" => $str,
                        "path" => $dir,
                        "file" => $essence,
                        "relative" => $relative,
                        "type" => 'file',
                        "picture" => $picture,
                        "owner" => $owner['name'] ?? '',
                        "perm" => $permission,
                        "date_time" => $dateTime,
                        "size" => getAmountMemory(filesize($essence), 'mb', true),
                        "info" => $info,
                    ];
                }
            }

            if (in_array('dir', $returnTypes)) {
                foreach ($dirs as $row) {
                    $return[] = $row;
                }
            }

            if (in_array('file', $returnTypes)) {
                foreach ($files as $row) {
                    $return[] = $row;
                }
            }
        }

        return $return;
    }

    /**
     * @param array $param
     * @return void
     */
    public function removeOldFiles(
        array $param = ['dir' => null, 'expire' => 86400, 'ignore' => ['.gitignore'], 'show' => false]
    ): void {
        $dir = $param['dir'] ?? null;
        $expire = isset($param['expire']) ? getNum($param['expire']) : ((60 * 60) * 24);
        $show = (isset($param['show']) && isTrue($param['show']));
        $ignore = isset($param['ignore']) && is_array($param['ignore']) && count($param['ignore']) > 0
            ? $param['ignore'] : ['.gitignore'];

        if (! is_null($dir)) {
            $dir = trim(VarStr::getMake($dir));
            $dir = mb_substr($dir, -1) === '/' ? $dir : $dir.'/';

            if (is_dir($dir)) {
                if ($dh = opendir($dir)) {
                    if ($show) {
                        echo "Read dir: ", $dir;
                        echo defined('TERMINAL_MODE') && TERMINAL_MODE === true ? "\n" : "<br />";
                    }

                    // читаем и выводим все элементы
                    // от первого до последнего
                    while (($file = readdir($dh)) !== false) {
                        // текущее время
                        $time_sec = time();

                        // время изменения файла
                        $time_file = filemtime($dir.$file);

                        // теперь узнаем сколько прошло времени (в секундах)
                        $time = $time_sec - $time_file;

                        $unlink = $dir.$file;

                        if (is_file($unlink) && ! in_array($file, $ignore)) {
                            if ($time > $expire) {
                                $result = unlink($unlink);

                                if ($show) {
                                    if ($result !== true) {
                                        echo "Error, file not remove:", $unlink;
                                        echo defined('TERMINAL_MODE') && TERMINAL_MODE === true ? "\n" : "<br />";

                                    } else {
                                        echo "Remove file: ", $unlink;
                                        echo defined('TERMINAL_MODE') && TERMINAL_MODE === true ? "\n" : "<br />";
                                    }
                                }
                            }
                        }
                    }

                    closedir($dh);
                }
            }
        }
    }
}
