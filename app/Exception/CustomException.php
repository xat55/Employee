<?php
namespace App\Exception;

/**
 * Class CustomException
 * @package App\Exception
 */
class CustomException extends \Exception 
{
  const MESSAGE = 'База данных вернула пустой ответ!!!';
  const CODE = 500;
  
  public function __construct($message = '', $code = 0, \Exception $previous = null)
  {
    if (!empty($message)) {
      parent::__construct($message, $code, $previous);
    } else {
      parent::__construct(self::MESSAGE, self::CODE, null);
    }
  }
  
  public function errorMessage()
  {
    //сообщение об ошибке
    $errorMsg = 'Ошибка на линии '.$this->getLine().' в '.$this->getFile()
    .': <b>'.$this->getMessage().'</b> не является допустимым адресом электронной почты';
    return $errorMsg;
  }
  
  // Переопределим строковое представление объекта.
  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }
}
