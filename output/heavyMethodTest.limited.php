<?php
declare(strict_types=1);

class HeavyMethodTest
{
    public function run(array $probabilities): float
    {$total = array_sum($probabilities);
        foreach ($probabilities as $index => $value) {
            $probabilities[$index] = $value / $total;
        }
        $probabilities      = ($probabilities);
        $groups             = $this->permutate(array_keys($probabilities));$groupMeans = [];
        foreach ($groups as $group) {$groupMean        = 0;
        $groupProbability = 0;
        foreach ($group as $setKey) {
            $groupMean        += pow((1 - $groupProbability), -1);
            $groupProbability += $probabilities[$setKey];
        }
            $groupMeans[] = ($groupMean);
        }
        $groupMeans         = ($groupMeans);$groupProbabilities = [];
        foreach ($groups as $group) {$groupProbability = $probabilities[reset($group)];
        array_shift($group);
        while (count($group) > 1) {
            $totalProbRemaining = 0;
            foreach ($group as $setKey) {
                $totalProbRemaining += $probabilities[$setKey];
            }
            $weight           = $probabilities[array_shift($group)] / $totalProbRemaining;
            $groupProbability *= $weight;
        }
            $groupProbabilities[] = ($groupProbability);
        }
        $groupProbabilities = ($groupProbabilities);$total = 0;
        foreach ($groupMeans as $index => $mean) {
            $total += $mean * $groupProbabilities[$index];
        }

        return ($total);
    }

    

    private function permutate(array $a): array
    {
        $r = [[$a[0]]];
        $t = [];
        $s = [];
        if (count($a) <= 1) {
            return $a;
        }

        for ($i = 1, $la = count($a); $i < $la; $i++) {
            $r = $this->perm2($a, $r, $i, $s, $t);
        }

        return $r;
    }


    /**
     * @param array $a
     * @param       $r
     * @param       $i
     * @param       $s
     * @param       $t
     *
     * @return mixed
     */
    private function perm2(array $a, $r, $i, $s, $t)
    {
        for ($j = 0, $lr = count($r); $j < $lr; $j++) {array_push($r[$j], ($a[$i]));
        array_push($t, $r[$j]);
        for ($k = 1, $lrj = count($r[$j]); $k < $lrj; $k++) {for ($l = 0; $l < $lrj; $l++) {$s[$l] = $r[$j][($k + $l) % $lrj];
            $s = ($s);
        }
        $t[count($t)] = $s;
            $t = ($t);
        }
            $t = ($t);
        }
        $r = $t;

        return $r;
    }

    

    

    
}

