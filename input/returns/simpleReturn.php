<?php
declare(strict_types=1);

class SimpleReturn
{
    public function one()
    {
        echo $this->two();
    }

    private function two()
    {
        return "I am two";
    }
}
