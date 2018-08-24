<?php
declare(strict_types=1);

class SimpleTest
{

    /*
     * Multiline Comment - this one doesn't get merged
     */
    public function one()
    {
        echo "I am one!";
        echo "I am two!";
        echo "I am three!";
    }

    // neither does this one
    public function no()
    {
        echo "I'm public leave me alone";
    }

    

    
}

