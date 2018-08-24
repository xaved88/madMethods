<?php
declare(strict_types=1);

class methodsAsArguments
{
    public function one()
    {
        return $this->three($this->two());
    }

    protected function two(): int
    {
        return 1;
    }

    private function three(int $x)
    {
        return $x;
    }
}
