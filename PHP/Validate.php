<?php

/**
 * Class Validate
 * @author xun
 */

class Validate
{
    private $arguments;
    private $fieldsSet;

    private function __construct($arguments, $fieldsSet = [])
    {
        $this->arguments = $arguments;
        $this->fieldsSet = $fieldsSet;
    }

    /**
     * init
     * @param $arguments
     * @param array $fieldsSet
     * @return self
     */
    public static function init($arguments, $fieldsSet = [])
    {
        $className = __CLASS__;
        return new $className($arguments, $fieldsSet);
    }

    /**
     * intAble
     * @param $name
     * @param bool $isThrow
     * @param string $errorMsg
     * @return $this
     * @throws Exception
     */
    public function intAble($name, $isThrow = false, $errorMsg = '')
    {
        $this->arguments[$name] = isset($this->arguments[$name]) ? intval($this->arguments[$name]) : 0;
//        if ($name == 'uid') {
//            var_dump($this->arguments, $this->arguments[$name]);
//            exit;
//        }
        $this->throwException($name, $isThrow, $errorMsg);
        return $this;
    }

    /**
     * floatAble
     * @param $name
     * @param bool $isThrow
     * @param string $errorMsg
     * @return $this
     * @throws Exception
     */
    public function floatAble($name, $isThrow = false, $errorMsg = '')
    {
        $this->arguments[$name] = isset($this->arguments[$name]) ? floatval($this->arguments[$name]) : 0.00;
        $this->throwException($name, $isThrow, $errorMsg);
        return $this;
    }

    /**
     * strAble
     * @param $name
     * @param bool $isThrow
     * @param string $errorMsg
     * @return $this
     * @throws Exception
     */
    public function strAble($name, $isThrow = false, $errorMsg = '')
    {
        $this->arguments[$name] = isset($this->arguments[$name]) ? strval($this->arguments[$name]) : '';
        $this->throwException($name, $isThrow, $errorMsg);
        return $this;
    }

    /**
     * arrAble
     * @param $name
     * @param bool $isThrow
     * @param string $errorMsg
     * @return $this
     * @throws Exception
     */
    public function arrAble($name, $isThrow = false, $errorMsg = '')
    {
        $this->arguments[$name] = isset($this->arguments[$name]) ? (array)$this->arguments[$name] : [];
        $this->throwException($name, $isThrow, $errorMsg);
        return $this;
    }


    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * throwException
     * @param string $name
     * @param bool $isThrow
     * @param string $errorMsg
     * @throws Exception
     */
    private function throwException($name, $isThrow, $errorMsg = '')
    {
        if (empty($this->arguments[$name]) && $isThrow == true) {
            if (empty($errorMsg)) {
                $name     = isset($this->fieldsSet[$name]) ? $this->fieldsSet[$name] : $name;
                $errorMsg = "{$name}不得为空";
            }
            throw new Exception($errorMsg);
        }
    }
}

$val       = file_get_contents('php://input');
$params    = json_decode($val, true);
$fieldsSet = ['uid' => '用户', 'author' => '车辆拥有者'];

try {
    $params = Validate::init($params, $fieldsSet)
        ->intAble('uid', true)
        ->strAble('author', true)
        ->getArguments();
} catch (Throwable $e) {
    var_dump($e->getMessage());
    exit;
}

/**
 * @var $uid
 * @var $author
 */
extract($params);
var_dump($uid, $author);

