<?php

namespace Warkhosh\Component\Url;

use Warkhosh\Variable\VarArray;
use Warkhosh\Variable\VarStr;

/**
 * Class AppUrl
 *
 * Класс для обработки урлов и построения типовых задач на их основе
 *
 * @package Ekv\Framework\Components\Url
 */
class AppUrl
{
    /**
     * Значение урла который указали ( не обработанный, как передали )
     *
     * @var string
     */
    protected $primaryUrl = null;

    /**
     * Значение урла который указали ( не обработанный, с приведением к типу: строка )
     *
     * @var string
     */
    protected $originalUrl = '';

    /**
     * Значение урла без названия файла
     *
     * @var string
     */
    protected $url = '';

    /**
     * Отдельно хранит только пути без фала и query парамтеров
     *
     * @var string
     */
    protected $path = '';

    /**
     * Отдельно хранит название файла если такой передали в урле
     *
     * @var string
     */
    protected $file = '';

    /**
     * Флаг для преобразования урла из абсолютного в относительный
     *
     * @var bool
     */
    protected $relative = false;

    /**
     * Флаг наличия query запросов в урле
     *
     * @var bool
     */
    protected $withQuery = false;

    /**
     * Флаг наличия слеша в конце пути (перед файлом)
     *
     * @var bool
     */
    protected $slashAtEnd = false;

    /**
     * Строка для вырезания указанного пути из начала урла, для преобразования в относительный
     *
     * @var string
     */
    protected $basePath = '';

    /**
     * @var array
     */
    protected $validExtensions = ['php', 'html', 'htm'];

    /**
     * Флаг дописывания создания index файла если файл в урле не указан
     *
     * @var bool
     */
    protected $filePresence = true;

    /**
     * Название index файла для дописывания если указан флаг $filePresence
     *
     * @var string
     */
    protected $indexFileName = 'index';

    /**
     * Расширение index файла для дописывания если указан флаг $filePresence
     *
     * @var string
     */
    protected $indexFileExtension = 'php';

    /**
     * Флаг строгой проверки преобразований урла, расширений и прочих параметров
     *
     * @note речь идет о не корректных символах с точки зрения ЧПУ
     * @var bool
     */
    protected $strict = false;

    /**
     * Системный флаг для определения что урл уже разбирался
     *
     * @var bool
     */
    private $prepareUrl = false;

    /**
     * AppUrl constructor.
     *
     * @param null  $url
     * @param false $strict
     */
    public function __construct($url = null, $strict = false)
    {
        $this->set($url, $strict);
    }

    /**
     * Установка урла как передали для последующих проверок
     *
     * @param string $url
     * @param bool   $strict - флаг вырезания лишних символов ( не корректных с точки зрения ЧПУ )
     * @return $this
     */
    public function set($url = null, $strict = false)
    {
        $this->originalUrl = VarStr::trim(VarStr::getMakeString($url));
        $this->primaryUrl = is_null($url) ? null : $this->originalUrl;

        if ($strict) {
            $this->setStrict($strict);
            $this->originalUrl = preg_replace("/[^a-zA-Z0-9\/\.\-\_\?\=]/", "", VarStr::getMakeString($this->originalUrl));

            if (($uri = ltrim($this->originalUrl, "/")) != $this->originalUrl) {
                $this->originalUrl = "/{$uri}";
            }
        }

        return $this;
    }

    /**
     * Установка флага для определения наличия query запросов в урле
     *
     * @param bool $withQuery
     * @return AppUrl
     */
    public function withQuery($withQuery)
    {
        $this->setWithQuery($withQuery);

        return $this;
    }

    /**
     * Установка флага для определения наличия query запросов в урле
     *
     * @param bool $withQuery
     */
    public function setWithQuery($withQuery)
    {
        $this->withQuery = isTrue($withQuery);
    }

    /**
     * Установка флага для дописывания создания index файла если файл в урле не указан
     *
     * @param bool $filePresence
     * @return void
     */
    public function setFilePresence($filePresence)
    {
        $this->filePresence($filePresence);
    }

    /**
     * Установка флага для дописывания создания index файла если файл в урле не указан
     *
     * @param bool $filePresence
     * @return $this
     */
    public function filePresence($filePresence)
    {
        $this->filePresence = isTrue($filePresence);

        return $this;
    }

    /**
     * Допустимые расширения файлов
     *
     * @return array
     */
    public function getValidExtensions()
    {
        return $this->validExtensions;
    }

    /**
     * Установить допустимые расширения файлов
     *
     * @param array $extensions
     * @return void
     */
    public function setValidExtensions($extensions = [])
    {
        if (is_array($extensions)) {
            $this->validExtensions = $extensions;

        } elseif (is_string($extensions)) {
            $this->validExtensions = [$extensions];
        }
    }

    /**
     * Установить допустимые расширения файлов
     *
     * @param array $extensions
     * @return $this
     */
    public function validExtensions($extensions = [])
    {
        $this->setValidExtensions($extensions);

        return $this;
    }

    /**
     * Возвращает название установленного index файла
     *
     * @return string
     */
    public function getIndexFileName()
    {
        return $this->indexFileName;
    }

    /**
     * Устанавливает название index файла
     *
     * @param string $fileName
     * @return string
     */
    public function indexFileName(string $fileName)
    {
        $this->setIndexFileName($fileName);

        return $this;
    }

    /**
     * Устанавливает название index файла
     *
     * @param string $fileName
     * @return void
     */
    public function setIndexFileName(string $fileName)
    {
        if (isEmpty($fileName)) {
            trigger_error("Invalid file name");
        }

        $this->indexFileName = $fileName;
    }

    /**
     * Возвращает название расширения файла
     *
     * @return string
     */
    public function getIndexFileExtension()
    {
        return $this->indexFileExtension;
    }

    /**
     * Устанавливает название для расширения файла
     *
     * @param string $fileExtension
     * @return $this
     */
    public function indexFileExtension(string $fileExtension)
    {
        $this->setIndexFileExtension($fileExtension);

        return $this;
    }

    /**
     * Устанавливает название для расширения файла
     *
     * @param string $fileExtension
     * @return void
     */
    public function setIndexFileExtension(string $fileExtension)
    {
        if (isEmpty($fileExtension)) {
            trigger_error("Invalid file extension name");
        }

        $this->indexFileExtension = $fileExtension;
    }

    /**
     * Возвращает признак наличия обязательного слеша в конце пути
     *
     * @return bool
     */
    public function isSlashAtEnd()
    {
        return $this->slashAtEnd;
    }

    /**
     * Устанавливает признак наличия обязательного слеша в конце пути
     *
     * @param boolean | integer $slashAtEnd
     * @return $this
     */
    public function slashAtEnd($slashAtEnd)
    {
        $this->setSlashAtEnd($slashAtEnd);

        return $this;
    }

    /**
     * Устанавливает признак наличия обязательного слеша в конце пути
     *
     * @param boolean | integer $slashAtEnd
     * @return void
     */
    public function setSlashAtEnd($slashAtEnd)
    {
        $this->slashAtEnd = isTrue($slashAtEnd);
    }

    /**
     * Возвращает значение флага строгой проверки урла
     *
     * @return bool
     */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * Устанавливает значение флага строгой проверки урла
     *
     * @param boolean|integer $strict
     * @return void
     */
    public function setStrict($strict)
    {
        $this->strict = isTrue($strict);
    }

    /**
     * Устанавливает значение флага строгой проверки урла
     *
     * @param boolean|integer $strict
     * @return $this
     */
    public function strict($strict)
    {
        $this->setStrict($strict);

        return $this;
    }

    /**
     * Установка флага преобразования абсолютного пути в относительный
     *
     * @param boolean $relative - флаг преобразования
     * @param string  $basePath - базовый путь для прописывания его в начало урла
     * @return $this
     */
    public function inRelative(bool $relative, string $basePath)
    {
        $this->relative = isTrue($relative);
        $this->basePath = (string)$basePath;

        return $this;
    }

    /**
     * Возвращает полученый урл, согласно текущих настроек
     *
     * @return string
     */
    public function getUrl()
    {
        if (! $this->prepareUrl) {
            $this->prepareUrl($this->originalUrl);
        }

        return $this->url;
    }

    /**
     * Возвращает полученный путь из урла, согласно текущих настроек
     *
     * @return string
     */
    public function getPath()
    {
        if (! $this->prepareUrl) {
            $this->prepareUrl($this->originalUrl);
        }

        return $this->path;
    }

    /**
     * Возвращает полученный файл из урла, согласно текущих настроек
     *
     * @return string
     */
    public function getFile()
    {
        if (! $this->prepareUrl) {
            $this->prepareUrl($this->originalUrl);
        }

        return $this->file;
    }

    /**
     * Возвращает массив всех полученных параметров или переданные значения
     *
     * @return array|string
     */
    public function get()
    {
        if (! $this->prepareUrl) {
            $this->prepareUrl($this->originalUrl);
        }

        $arg = func_get_args();
        $data = ['path' => $this->path, 'file' => $this->file, 'url' => $this->url];

        // фикс если передали сюда пустой массив в массиве
        if (is_array($arg) && count($arg) === 1 && is_array($arg[0]) && count($arg[0]) === 0) {
            $arg = [];
        }

        if (is_array($arg) && ($count = count($arg)) > 0) {

            if ($count > 1) {
                return VarArray::getOnly($arg, $data); // список полей

            } else {
                if (is_array($arg[0])) {
                    return VarArray::getOnly($arg[0], $data);

                } else if (is_string($arg[0])) { // если передали один строчный параметр
                    return VarArray::getFirst(VarArray::getOnly([$arg[0]], $data)); // вернуть одно значение
                }
            }
        }

        return $data;
    }

    /**
     * Удаление из начала строки абсолютной части пути что-бы сделать из него относительный
     *
     * @param string $url
     * @return string
     */
    protected function getRelative($url = '')
    {
        $relativeUrl = VarStr::getRemoveStart($this->basePath, $url);

        // если удаление прошло удачно то отслеживаем наличие слеша в начале урла
        if ($relativeUrl !== $url) {
            $relativeUrl = "/" . ltrim($relativeUrl, "/");
        }

        return $relativeUrl;
    }

    /**
     * Проверка и подготовка урл строки для работы с классом
     *
     * @param null $url
     * @return string
     */
    protected function prepareUrl($url = null)
    {
        if ($this->isStrict()) {
            $url = preg_replace("/[^a-zA-Z0-9\/\.\-\_\?\=]/", "", $url);
        }

        $param = parse_url($url);
        $param['path'] = key_exists('path', $param) ? $param['path'] : '';

        if ($this->withQuery === false && isset($param['query'])) {
            unset($param['query']);
        }

        $info = pathinfo($param['path']);
        $this->file = '';

        // Важно что-бы при всех варантах (включая кривые) всегда был dirname!
        if (isset($info['dirname'])) {
            $info['dirname'] = $info['dirname'] === '.' ? DIRECTORY_SEPARATOR : $info['dirname'];
        } else {
            $info['dirname'] = DIRECTORY_SEPARATOR;
        }

        // если есть расширение файла то пытаемся отдельно установить параметры файла
        if (isset($info['extension']) && isEmpty($info['extension']) === false) {

            // при включенной строгой проверке убираем недопустимые расширения и файл
            if ($this->isStrict() && ! in_array($info['extension'], $this->validExtensions)) {
                unset($info['extension']);
                unset($info['filename']);

            } else {
                $this->file = "{$info['filename']}.{$info['extension']}";
            }

            unset($info['basename']); // удаляем сгруппированные значения о файле с разрешением

        } else {
            $info['dirname'] = $info['dirname'] === '/' ? '' : "/" . ltrim($info['dirname'], '/');
            $info['basename'] = $info['basename'] === '/' ? '' : VarStr::start('/', $info['basename']);
            $info['dirname'] = $info['dirname'] . $info['basename']; // объединяем пути
            unset($info['basename']);
        }

        // если стоит флаг обязательного наличия файла а его не удалось установить
        if (! array_key_exists('extension', $info) && $this->filePresence) {
            $this->file = $this->getIndexFileName() . ".{$this->indexFileExtension}";
        }

        $path = isEmpty($info['dirname']) ? "/" : $info['dirname'];

        if ($this->isSlashAtEnd()) {
            $path = VarStr::ending("/", $path);
        }

        // указан флаг преобразования абсолютного значения на относительное по отношения к страницам сайта;
        if ($this->relative === true) {
            $path = $this->getRelative($path);
        }

        $this->path = $path; // записываем отдельно путь без файла и прочих параметров

        $param['path'] = $path;
        $param['path'] .= (! isEmpty($this->file) ? "/{$this->file}" : "");

        $this->url = UrlHelper::getGenerated($param);

        $this->prepareUrl = true;
    }
}