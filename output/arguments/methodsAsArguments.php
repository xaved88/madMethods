<?php
declare(strict_types=1);

class methodsAsArguments
{
    public function one()
    { $x = $this->two(); 
        return ($x);
    }

    protected function two(): int
    {
        return 1;
    }

    
}
