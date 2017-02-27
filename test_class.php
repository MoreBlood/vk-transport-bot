<?php

/**
 * Created by PhpStorm.
 * User: moreblood
 * Date: 20.02.17
 * Time: 10:28
 */
class test_class{

    private $test1;
    public $test2;
    protected $test3;

    function __construct ($value){
        $this->test1 = $value;
    }

    function __destruct()
    {
        $this->test1;
    }

    public function SETtest1 ($value){
        $this->test1 = $value;
    }

    public function GETtest1 (){
        return $this->test1;
    }
}


class test_class2 extends test_class{

}

$classTest = new test_class(14);

$classTest2 = new test_class2(15);

echo $classTest->GETtest1();

echo $classTest2->GETtest1();

