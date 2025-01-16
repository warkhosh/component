<?php

namespace Warkhosh\Component\Url;

/**
 * Class AppUrlPath
 *
 * Класс для обработки текущего урла и использования для роутинга и других проверках
 *
 * @note следует внимательно следить что-бы по коду значения не переопределялись!
 *
 * @package Ekv\Framework\Components\Page
 */
class AppUrlPath
{
    use UrlPathMethods;

    /**
     * Список путей полученные из урла для будущих параметров
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Список типов переменных от $this->data для сложных проверок
     *
     * @var array
     */
    protected array $types = [];

    /**
     * Список путей из перебора $this->data, но значения каждого пути подверглось проверки на реальность и корректность
     *
     * @var array
     */
    protected array $pathResults = [];

    /**
     * Название файла если оно присутствует в пути урла
     *
     * @var string|null
     */
    protected ?string $file = null;

    /**
     * При разборе важно не удалять не допустимые символы или пустоты между слешами как: //,
     * иначе потом в проверках не поймем что урл не допустимый.
     *
     * @param string|null $url
     */
    public function __construct(?string $url = null)
    {
        $url = empty($url) ? server()->request_uri : $url;
        $this->configuring($url);
    }

    /**
     * Устанавливает значений переменных объекта по указанному урлу
     *
     * @note этот метод кеширует в рамках php процесса значения и не приводит к повторному срабатыванию
     *
     * @param string|null $url
     * @return void
     */
    private function configuring(?string $url = null): void
    {
        static $data = [];
        $slug = md5($url);

        if (key_exists($slug, $data)) {
            $this->data = $data[$slug]['data'];
            $this->types = $data[$slug]['types'];
            $this->pathResults = $data[$slug]['pathResults'];
            $this->file = $data[$slug]['file'];

            return;
        }

        $url = parse_url(rawurldecode($url));

        if (isset($url['path']) && array_key_exists('path', $url)) {
            $paths = getExplodeString('/', $url['path'], ['', ' ']);
            $types = [];

            if (count($paths) > 0) {
                foreach ($paths as $var) {
                    $types[] = is_numeric($var) ? (is_float($var) ? 'float' : ($var >= 0 ? 'num' : 'int')) : 'str';
                }

                $item = array_pop($paths); // Извлекает последний элемент массива
                $tmp = pathinfo($item);

                if (isset($tmp['filename']) && isset($tmp['extension']) && mb_strlen($tmp['extension']) > 2) {
                    $this->file = $item;
                    array_pop($types); // извлекает последний элемент типа

                } else {
                    $paths[] = $item; // возвращаем на место
                }
            }

            $this->data = array_values($paths);
            $this->types = array_values($types);

            reset($this->data);

            // Если значения ещё не были установлены
            //if (is_null($this->file)) {
            //    $appConfig = \Warkhosh\Component\Config\AppConfig::getInstance();
            //    $this->file = (string)$appConfig->get('server.index.file');
            //}
        }

        $data[$slug] = [
            'data' => $this->data,
            'types' => $this->types,
            'pathResults' => $this->pathResults,
            'file' => $this->file,
        ];
    }
}
