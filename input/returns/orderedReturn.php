<?php
declare(strict_types=1);

class OrderedReturn
{
    public function one()
    {
        echo "One Before All - " . $this->two();
    }

    private function two()
    {
        return  $this->three() . ' and two was here too!';
    }

    private function three()
    {
        echo "I should happen before things!";
        return "I am three!";
    }
}
