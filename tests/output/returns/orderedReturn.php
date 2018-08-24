<?php
declare(strict_types=1);

class OrderedReturn
{
    public function one()
    {echo "I should happen before things!";
        echo "One Before All - " . (("I am three!") . ' and two was here too!');
    }

    

    
}
