<?php
declare(strict_types=1);

class OrderOfOperationsReturn
{
    public function one()
    {
        echo 5 * $this->two();
    }

    private function two()
    {
        return 3 + 1;
    }
}
