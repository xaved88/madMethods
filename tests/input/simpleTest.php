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
        $this->two();
    }

    // neither does this one
    public function no()
    {
        echo "I'm public leave me alone";
    }

    /**
     * Dockblock - this one should be merged also
     */
    private function two()
    {
        echo "I am two!";
        $this->three();
    }

    // comment - This one should be merged
    private function three()
    {
        echo "I am three!";
    }
}

