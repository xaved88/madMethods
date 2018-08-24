<?php
declare(strict_types=1);

class SimpleArgument
{
    public function one()
    {
        return $this->two(1 + 2, 3, 4);
    }

    private function two(int $x, int $y, $z)
    {
        return $x + $y * $z;
    }
}
