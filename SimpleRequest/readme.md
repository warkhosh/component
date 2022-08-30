# AppSimpleRequest

Класс AppSimpleRequest создан для простого взаимодействия между серверами по протоколу HTTP. 

##### GET

```php
$simpleRequest = Warkhosh\Component\SimpleRequest\AppSimpleRequest::init();

$request = $simpleRequest->get($url);
// or
$request = (new \Warkhosh\Component\SimpleRequest\AppSimpleRequest())->get($url);

// Запрос и скачивание документа из ответа
$request = $simpleRequest->headersInOutput(false)->get($url);

if ($request->getResult()) {
    copy($request->getBody()->getContents(), "/save/to/file.xml");
}
```

##### HEAD 
```php
$simpleRequest = Warkhosh\Component\SimpleRequest\AppSimpleRequest::init();

$request = $simpleRequest->head($url);
// or
$request = (new \Warkhosh\Component\SimpleRequest\AppSimpleRequest())->head($url);

// если нужно узнать только доступность ресурса, так можно сразу проверить код ответа
$code = $request->getStatusCode();
```

##### POST
```php
$simpleRequest = Warkhosh\Component\SimpleRequest\AppSimpleRequest::init();

$request = $simpleRequest->post([...], $url);

// Передать указанный массив как поток данных в формате JSON
$request = $simpleRequest->streamJson([...], $url)->request();

// Передать указанный массив как поток данных в формате XML
$request = $simpleRequest->streamXml([...], $url)->request();
```

##### PUT
```php
$request = Warkhosh\Component\SimpleRequest\AppSimpleRequest::init()->put([...], $url);
```

##### Передача файла
```php
$simpleRequest = Warkhosh\Component\SimpleRequest\AppSimpleRequest::init();
$simpleRequest->file("path/file1.jpg", "field_name");
$simpleRequest->file("path/file2.jpg", "field_some_name");
$simpleRequest->post([...], $url);
```

##### Basic Auth
```php
$simpleRequest = Warkhosh\Component\SimpleRequest\AppSimpleRequest::init();
$simpleRequest->httpAuth('user', 'password');
```

##### Custom Header
```php
$simpleRequest = Warkhosh\Component\SimpleRequest\AppSimpleRequest::init();

// указание своего дополнительного заголовка в запросе
$simpleRequest->header(['test-script: testing'])->post([...], $url);;
```

##### Обработка ответа
```php
$response = Warkhosh\Component\SimpleRequest\AppSimpleRequest::init()->get($url);

// Проверка кода ответа
if ($response->getResult(200)) {
    // code
}

// Проверка по ответу типа содержимого в ответе
if (stripos($response->getHeaderLine('Content-Type'), 'application/json') !== false) {
    // code
}

$stream = $response->getBody();

// Проверка содержимого по телу ответа
if ($stream->getContents() === '{"result":"ok"}') {
    // code
}

// Для повторного обращения к содержимому контента
$stream->rewind();

// Проверка содержимого на конкретное значение
if (stripos($response->getHeaderLine('Content-Type'), 'application/json') !== false) {
    $data = json_decode($stream->getContents(), true);
    
    if (key_exists('status', $data) && $data['status'] === 'ok') { 
        // code
    }
}
```

##### Timeout
```php
$simpleRequest = Warkhosh\Component\SimpleRequest\AppSimpleRequest::init()->timeout(2);
```