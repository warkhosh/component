# JsonEncoder

Класс для работы с данными для преобразования их в JSON

##### Examples:

```php
$json = new Warkhosh\Component\Json\JsonEncoder(["name" => "Konstantin"]);

// Работа через статически метод
$json = Warkhosh\Component\Json\JsonEncoder::init(["name" => "Konstantin"]);

// Бросить исключение с указанным текстом если при кодировании данных произошла ошибка
$json->exceptionInError("Json error");

// Получившийся JSON
echo $json->get();

// оригинальные данные
var_dump($json->getSource());
```