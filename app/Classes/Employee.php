<?php

namespace App\Classes;

use App\Exception\CustomException;
use GuzzleHttp\Client;

final class Employee
{  
    public $param = null;
    public $param1 = null;
    private $db;
    
    /**
    * Employee constructor.
    */
    public function __construct()
    {
        $this->db = DataBase::getInstance();
        $this->create();
        $this->fill();
    }
    
    /**
    * @param string $property Magic method name comes here
    * @return mixed Result of called function
    * @throws \Exception
    */
    public function __get($property)
    {
        try {
            $methodName = 'get' . ucfirst($property);
            
            if (method_exists($this, $methodName)) {
                return call_user_func([$this, $methodName], $this->param, $this->param1);
            }
            throw new \Exception('Method \'' . $methodName . '\' does not exist.');    
        }  
        catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /**
    * Creates all needed tables in database
    */
    private function create()
    {
        $db = $this->db;
        
        try {            
            $query = "CREATE TABLE worker (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(30),
                tel VARCHAR(30),
                address VARCHAR(50),
                salary INT(6),
                vkId VARCHAR(100),
                photo TEXT
            )";
            $workerTable = $db->sendingQuery($query);
            
            if (!$workerTable) {
                throw new CustomException('worker');
            }
            
            $query = "CREATE TABLE cabinet (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                num INT(6),
                floor INT(6),
                capacity INT(6)
            )";
            $cabinetTable = $db->sendingQuery($query);
            
            if (!$cabinetTable) {
                throw new CustomException('cabinet');
            }
            
            $query = "CREATE TABLE worker_cabinet (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,  
                worker_id INT(6) UNSIGNED,
                cabinet_id INT(6) UNSIGNED
            )";
            $worker_cabinetTable = $db->sendingQuery($query);
            
            if (!$worker_cabinetTable) {
                throw new CustomException('worker_cabinet');
            }
            
        } catch (CustomException $e) {
            echo 'Таблица \'' . $e->getMessage() . '\' не создана';
        }
    }
    
    /**
    * Fills database with random data
    *
    * @throws CustomException
    */
    private function fill()
    {    
        $db = $this->db;  // Подключение к бд
        $faker = \Faker\Factory::create();  // Если нужна русская локализация, передать её параметром в метод create 'ru_RU'
        
        // Some properties
        $floors = 5;
        $minCabinetsAmount = 2;
        $maxCabinetsAmount = 5;
        $minCabinetCapacity = 4;
        $maxCabinetCapacity = 6;      
        
        // For every floor
        for ($i = 1; $i <= $floors; $i++) {
            $cabinetsAmount = rand($minCabinetsAmount, $maxCabinetsAmount);
            // For every cabinet on floor
            for ($j = 1; $j <= $cabinetsAmount; $j++) {
                
                $num = 100 * $i + $j;
                $floor = $i;
                $capacity = rand($minCabinetCapacity, $maxCabinetCapacity);
                
                // Fill in the table 'cabinets'
                $query = "INSERT INTO cabinet 
                (num, floor, capacity) 
                VALUES 
                ('$num', '$floor', '$capacity')";  
                $db->sendingQuery($query);                
            }
        }
        $query = "SELECT * FROM cabinet";
        $cabinets = $db->getData($query);
        // For each cabinet
        foreach ($cabinets as $cabinet) {
            // For every capacity num
            for ($i = 0; $i < $cabinet['capacity']; $i++) {
                $firstName = $faker->firstName;
                $tel = $faker->e164PhoneNumber;
                $address = $faker->streetAddress;
                $salary = $faker->numberBetween($min = 100, $max = 1000);
                $vkId = 'id'.$faker->numberBetween($min = 1000000, $max = 99999999);
                
                // Fill in the table 'worker'
                $query = "INSERT INTO worker 
                (name, tel, address, salary, vkId, photo) 
                VALUES 
                ('$firstName', '$tel', '$address', '$salary', '$vkId', '')";
                $db->sendingQuery($query);
                
                // Fill in the table 'worker_cabinet'
                $link = $db->getLink();
                $workerId = $link->insert_id;            
                $cabinetId = $cabinet['id'];          
                $query = "INSERT INTO worker_cabinet 
                (worker_id, cabinet_id) VALUES ($workerId, $cabinetId)";
                $db->sendingQuery($query);
                
                $pathname = 'docs/';
                // Ctreate dirs
                if (!is_dir(($pathname . $workerId))) {
                    mkdir($pathname . $workerId, 0777, true);
                }
            }
        }
    }
    
    /**
    * Get all workers from database
    *
    * @return array
    * @throws CustomException
    */
    private function getAll()
    {
        $query = $this->getByCondition();
        
        return $this->db->getData($query);
    }
    
    /**
    * Get all workers by exact floor num
    *
    * @param $floorNum
    * @return array
    * @throws CustomException
    */
    private function getWorkersByFloor($floorNum)
    {
        $condition = ['c.floor' => $floorNum];
        $query = $this->getByCondition($condition);
        
        return $this->db->getData($query);
    }
    
    /**
    * Get workers with the biggest salary in cabinet or on floor
    *
    * @param $floorOrCabinet
    * @param $num
    * @param int $limit
    * @return array
    * @throws CustomException
    */
    private function getByBiggestSalary($floorOrCabinet, $num, $limit = 1)
    {
        $condition = ['c.' . $floorOrCabinet => $num];
        $query = $this->getByCondition($condition, 'w.salary', 'DESC', $limit);
        
        return $this->db->getData($query);
    }
    
    /**
    * Get workers from cabinet min/max capacity
    *
    * @param $minOrMax
    * @return array
    * @throws CustomException
    */
    private function getWorkersFromCabinet($minOrMax)
    {
        $query = $this->getByCondition();
        $query = "$query WHERE c.capacity = (SELECT $minOrMax(capacity) FROM cabinet)";
        
        return $this->db->getData($query);
    }
    
    /**
    * Get workers data using condition
    *
    * Returns workers data using condition. Also can order and limit results
    *
    * @param null $condition
    * @param null $orderBy
    * @param string $orderDirection
    * @param null $limit
    * @return string
    */
    private function getByCondition($condition = null, $orderBy = null, $orderDirection = 'ASC', $limit = null)
    {
        $query = "SELECT w.id AS id, w.name, w.tel, w.salary, w.address,  c.num, c.floor, c.capacity
        FROM worker AS w 
        LEFT JOIN worker_cabinet AS a 
        ON a.worker_id = w.id 
        LEFT JOIN cabinet AS c
        ON c.id = a.cabinet_id";
        
        if (isset($condition)) {
            $query = $query.' WHERE '.key($condition).' = '.$condition[key($condition)];
        }
        
        if (isset($orderBy)) {
            $query = "$query ORDER BY $orderBy $orderDirection";
        }
        
        if (isset($limit)) {
            $query = "$query LIMIT $limit";
        }
        
        return $query;
    }
    
    /**
    * Get txt filenames list from worker folder
    *
    * Searches files only with digits and letters in names
    * Files must have '.txt' extension
    *
    * @param string $path
    * @return array
    * @throws \Exception
    */
    private function getFiles($path = "docs/worker.1")
    {
        if (!is_dir($path)) {
            throw new \Exception('Docs folder not found for worker id ' . $worker->id);
        }    
        $files = array_diff(scandir($path), ['.', '..']);
        $txtFiles = [];
        
        foreach ($files as $key => $fileName) {      
            // Если есть одна буква и одна цифра, то истина
            if (preg_match('#^[a-z][0-9]\.txt$#', $fileName)) {
                $txtFiles[] = $fileName;
            }
        }
        
        return $txtFiles;
    }
    
    /**
    * Get worker VK profile photo and put it into database
    *
    * @param $workerId
    * @return bool|\mysqli_result
    * @throws CustomException
    * @throws \GuzzleHttp\Exception\GuzzleException
    */
    private function getVkPhotoPathByAPI($workerId)
    {
        $db = $this->db;
        
        $query = "SELECT vkId FROM worker WHERE id='$workerId'";
        $data = $db->getData($query);
        $workerVkId = $data[0]['vkId'];
        $client = new Client();
        
        try {
            $request = $client->post('https://api.vk.com/method/users.get', [
                'form_params' => [
                    'user_ids' => $workerVkId,
                    'fields' => 'photo_max_orig',
                    'access_token' => (require __DIR__ . '/../../settings.php')['service_key'],
                    'v' => '5.124',
                ]
            ]);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
                
        $result = json_decode($request->getBody()->getContents());
        $photoUrl = $result->response[0]->photo_max_orig ?? null;
        $query = "UPDATE worker SET photo='$photoUrl' WHERE id='$workerId'";
        
        return $db->sendingQuery($query);
    }
    
    /**
    * Get worker VK profile photo and put it into database
    *
    * @param $workerId
    * @return bool|\mysqli_result
    * @throws CustomException
    */
    private function getVkFotoPath($workerId)
    {
        require_once 'phpQuery/phpQuery/phpQuery.php';
        $db = $this->db;
        
        $query = "SELECT vkId FROM worker WHERE id='$workerId'";
        $data = $db->getData($query);
        $workerVkId = "https://vk.com/{$data[0]['vkId']}";
        
        $curl = curl_init(); // Инициализируем сеанс
        curl_setopt($curl, CURLOPT_URL, $workerVkId); // Указываем адрес страницы
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0");
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // Автоматом идём по редиректам
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // Не проверять SSL сертификат
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // Не проверять Host SSL сертификата
        curl_setopt($curl, CURLOPT_URL, $workerVkId); // Куда отправляем
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // Возвращаем, но не выводим на экран результат
        $result = curl_exec($curl);
        $dataCurl = iconv('windows-1251', 'utf-8', $result);
        
        // Get worker VK profile photo with phpQuery
        $pq = \phpQuery::newDocument($dataCurl);
        $elem = $pq->find('img.page_avatar_img');
        $photoUrl = $elem->attr('src');  
        
        /**
        * Раскомментируйте код на стр. 333-335, если хотите получить массив ссылок с помощью регулярки.
        * И закоментируйте код на стр. 325-327.
        */
        // $reg = '#<img\s+class="page_avatar_img"\s+src="(.+?)"#su';
        // preg_match_all($reg, $dataCurl, $matches);
        // $photoUrl = $matches[1][0];
        
        $query = "UPDATE worker SET photo='$photoUrl' WHERE id='$workerId'";
        
        return $db->sendingQuery($query);        
    }
}