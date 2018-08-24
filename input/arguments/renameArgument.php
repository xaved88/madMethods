<?php
declare(strict_types=1);

class RenameArgument
{
    public function one()
    {
        $a = 3;
        $b = 4;

        $this->two($a, $b);

        return $a + $b;
    }

    private function two(int $a, int $b)
    {
        $a++;
        $b++;
    }
}
