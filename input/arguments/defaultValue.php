<?php
declare(strict_types=1);

class DefaultValue
{
    public function one()
    {
        return $this->two(1, 2);
    }

    private function two(int $x, int $y, $z = 3)
    {
        return $x + $y * $z;
    }
}
