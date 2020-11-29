<?php
namespace App\Classes;

use App\Exception\CustomException;

/**
 * Class DataBase set connection with data base
 * @package App\Classes
 */
class DataBase 
{ 
    private $db;
    static private $_ins = null;
    
    static public function getInstance() 
    {
        if(self::$_ins instanceof self) {
            return self::$_ins;
        }
        
        return self::$_ins = new self;
    }
    
    private function __construct() 
    {
        echo "<h4>Соединение с базой данных</h4>";
        
        $this->db = new \mysqli('localhost','root','12345','test');
        
        if($this->db->connect_error) {
            throw new DbException("Ошибка соединения : ");
        }
        $this->db->query("SET NAMES 'UTF8'");
    }
    
    // Запрещаем клонировать объект
    private function __clone() { }
    
    // Возвращаем значение свойства $db (объект mysqli) SQL-соединения
    public function getLink()
    {
        return $this->db;
    }
    
    // Отправляем запрос в БД без возврата из таблицы данных (для операций "вставка", "обновление", "удаление"..)
    public function sendingQuery($query) 
    {
        return $this->db->query($query);
    }
    
    // Отправляем запрос в БД и возвращаем полученные данные из таблицы
    public function getData($query) 
    {
        $result = $this->db->query($query);
        for ($row = []; $data = mysqli_fetch_assoc($result); $row[] = $data);
        
        if (empty($row)) {
            throw new CustomException();
        }
        
        return $row;
    }  
}
