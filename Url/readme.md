# AppUrlPath

Класс для обработки текущего урла и использования этих значений в роутинге проекта
> Следует отслеживать попытки переопределить\повлиять на значения со стороны других скриптов!

##### Получить список всех путей:
```
$urlPath = new UrlPath();
$paths = $urlPath->get(null);
```

##### Получить первое ЧПУ значение или предпоследние:
```
$urlPath->get(1);
$urlPath->get(-2);
```

##### Проверка количества ЧПУ значений в запросе:
```
if ($urlPath->getAmount() > 0) { ...
if ($urlPath->amountIs(2)) { ...
```

##### Проверка наличие файла в запросе:
```
if ($urlPath->fileIs('sitemap.xml')) { ...
```

###### Проверка первого, второго, третьего, четвертого и последнего ЧПУ значения в запросе:
```
if ($urlPath->firstIs('article')) { ...
if ($urlPath->secondIs('product')) { ...
if ($urlPath->thirdIs('send')) { ...
if ($urlPath->fourthIs('random-product')) { ...
if ($urlPath->lastIs('deleted')) { ...
```

##### Последовательная проверка указанных путей на наличие:
```
if ($urlPath->checkingPaths(['post', 'deleted'], $offset = 1)) { ...
if ($urlPath->checkingPaths(['update', 'int:', 'post'])) { ...
if ($urlPath->checkingPaths(['topic', 'num:', 'deleted'])) { ...
if ($urlPath->checkingPaths(['transfer', 'float:', 'money'])) { ...
if ($urlPath->checkingPaths(['transfer', 'float:'], 0, false)) { ...
```

##### Проверка ЧПУ значений на корректность по количеству
Без указания $limit не будет строгой проверки, только по количеству выбранных записей
```
if ($urlPath->isCorrectPart(1)) { ...
if ($urlPath->isCorrectPart(null, $limit = 3, $offset = 0)) { ...
```


# AppUrl 
Класс для обработки урлов и построения типовых задач на их основе

##### Загрузка указанного урла в объект для проверок и преобразований. 
```
$url = new AppUrl($str, $strict = true); 
// Если передан второй параметр, переданные значения будут преобразованы с удалением всех недопустимых символов
```

##### Устанавливает режим строгой проверки урла и удаления из него всех лишних значений.
```
$url->strict(true);
```

##### Установка флага для допущения query значений в урле
```
$url->withQuery(true);
```

##### Установка флага для дописывания index файла если файл в урле не указан
```
$url->filePresence(true);
```

##### Устанавливает признак наличия обязательного слеша в конце пути
```
$url->slashAtEnd(true);
```

##### Устанавливает название index файла
```
$url->indexFileName('index');
```

##### Устанавливает название расширения для index файла
```
$url->indexFileExtension('php');
```

##### Устанавливает режим преобразования абсолютного пути в относительный
```
$url->inRelative(true, '/var/www/site/');
```

##### Устанавливает допустимые расширения файлов
```
$url->validExtensions(['csv', 'xls']);
// or
$url->validExtensions('doc');
```

##### Возвращает полученный урл согласно текущих настроек
```
$url = $url->getUrl();
```

##### Возвращает полученный путь в урле согласно текущих настроек
```
$path = $url->getPath();
```

##### Возвращает полученный файл из урла, согласно текущих настроек
```
$file = $url->getFile();
```

##### Возвращает массив указанных параметров
```
$file = $url->get('file');
// or
$data = $url->get(['file', 'path', 'url']);
```

##### Examples:
```
$pageUrl = (new Url("/page"))->filePresence(true)->getUrl();
$file = (new Url($file, true))->inRelative(true, FILES_PATH)->getUrl();
```
