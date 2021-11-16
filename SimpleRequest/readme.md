# AppSimpleRequest

Класс AppSimpleRequest создан для простого взаимодействования между серверами по протоколу HTTP. 

> Для простоты использования он поставляется с фасадом SimpleRequest но вы всегда можете расширить его в своем проекте.

##### GET 
```php
$request = SimpleRequest::get($url);

// or
$request = (new \Warkhosh\Component\SimpleRequest\AppSimpleRequest())->get($url);

// Запрос и скачивание документа из ответа
$request = (new \Warkhosh\Component\SimpleRequest\AppSimpleRequest())->headersInOutput(false)->get($url);

if ($request->getResult()) {
    copy($request->getDocument(), "/save/to/file.xml");
}
```

##### HEAD 
```php
$request = SimpleRequest::head($url);
// or
$request = (new AppSimpleRequest())->head($url);

// если нужно узнать только доступность ресурса, так можно сразу проверить код ответа
$code = $request->getStatusCode();
```

##### POST
```php
$request = SimpleRequest::post([...], $url);

// Передать указанный массив как поток данных в формате JSON
$request = SimpleRequest::streamJson([...], $url)->request();

// Передать указанный массив как поток данных в формате XML
$request = SimpleRequest::streamXml([...], $url)->request();
```

##### PUT
```php
$request = SimpleRequest::put([...], $url);
```

##### Передача файла
```php
$request = new AppSimpleRequest();
$request->file("path/file1.jpg", "field_name");
$request->file("path/file2.jpg", "field_some_name");
$request->post([...], $url);
```

##### Basic Auth
```php
$request = SimpleRequest::httpAuth('user', 'password');
```

##### Custom Header
```php
$request = new AppSimpleRequest();
...
// указание своего дополнительного заголовка в запросе
$request->header(['test-script: testing']);
```

##### Обработка ответа
```php
$request = SimpleRequest::get($url);

// Проверка кода ответа
if ($request->getResult(200)) ...

// Проверка содержимого в ответе
if ($request->getResult(200) && $request->getDocument() === 'ok') ...

// Проверка по ответу типа содержимого в ответе
if ($request->getResult(200) && $request->getHeader('content-type') === 'json') ...

// Проверка в ответе типа JSON значения status
if ($request->getHeader('content-type') === 'json' && $request->getDocumentValue('status') === 'ok') ...
```

##### Обработка тела ответа
```php
// Получить тело ответа
$response = $request->getDocument();

// Преобразовать и вернуть результат JSON ответ в массив
$response = $request->getDocument('toArray');

// Преобразовать и вернуть JSON ответ в объект stdClass
$response = $request->getDocument('toObject');

// Вернуть значеение в JSON ответе
$result = $request->getDocumentValue('import.result')
```

##### Timeout
```php
$request = SimpleRequest::timeout(2);
```