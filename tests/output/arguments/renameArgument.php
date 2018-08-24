<?php
declare(strict_types=1);

class RenameArgument
{
    public function one()
    {
        $a = 3;
        $b = 4; $a_mm0 = $a;  $b_mm1 = $b; 

        $a_mm0++;
        $b_mm1++;

        return $a + $b;
    }

    
}
