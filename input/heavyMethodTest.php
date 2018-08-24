<?php
declare(strict_types=1);

// CURRENTLY BREAKING ON STEP 4 -> 5, due to the early returns found in permutate()
class HeavyMethodTest
{
    public function run(array $probabilities): float
    {
        $probabilities      = $this->normalizeProbabilities($probabilities);
        $groups             = $this->permutate(array_keys($probabilities));
        $groupMeans         = $this->calculateGroupMeans($probabilities, $groups);
        $groupProbabilities = $this->calculateGroupProbabilities($probabilities, $groups);

        return $this->determineTheAverageMean($groupMeans, $groupProbabilities);
    }

    private function normalizeProbabilities(array $probabilities): array
    {
        $total = array_sum($probabilities);
        foreach ($probabilities as $index => $value) {
            $probabilities[$index] = $value / $total;
        }

        return $probabilities;
    }

    /*
     * I really want to make this method work, but it has multiple return values... so til I handle that, it's as good as we get
     */
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

    private function getMeanOfGroup(array $group, array $probabilities): float
    {
        $groupMean        = 0;
        $groupProbability = 0;
        foreach ($group as $setKey) {
            $groupMean        += pow((1 - $groupProbability), -1);
            $groupProbability += $probabilities[$setKey];
        }

        return $groupMean;
    }

    private function getProbabilityOfGroup(array $group, array $probabilities): float
    {
        $groupProbability = $probabilities[reset($group)];
        array_shift($group);
        while (count($group) > 1) {
            $totalProbRemaining = 0;
            foreach ($group as $setKey) {
                $totalProbRemaining += $probabilities[$setKey];
            }
            $weight           = $probabilities[array_shift($group)] / $totalProbRemaining;
            $groupProbability *= $weight;
        }

        return $groupProbability;
    }

    /**
     * @param $groupMeans
     * @param $groupProbabilities
     *
     * @return float
     */
    private function determineTheAverageMean($groupMeans, $groupProbabilities): float
    {
        $total = 0;
        foreach ($groupMeans as $index => $mean) {
            $total += $mean * $groupProbabilities[$index];
        }
        return $total;
    }

    /**
     * @param array $probabilities
     * @param       $groups
     *
     * @return array
     */
    private function calculateGroupProbabilities(array $probabilities, $groups): array
    {
        $groupProbabilities = [];
        foreach ($groups as $group) {
            $groupProbabilities[] = $this->getProbabilityOfGroup($group, $probabilities);
        }
        return $groupProbabilities;
    }

    /**
     * @param array $probabilities
     * @param       $groups
     *
     * @return array
     */
    private function calculateGroupMeans(array $probabilities, $groups): array
    {
        $groupMeans = [];
        foreach ($groups as $group) {
            $groupMeans[] = $this->getMeanOfGroup($group, $probabilities);
        }
        return $groupMeans;
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
        for ($j = 0, $lr = count($r); $j < $lr; $j++) {
            $t = $this->perm3($a, $r, $i, $s, $t, $j);
        }
        $r = $t;

        return $r;
    }

    /**
     * @param array $a
     * @param       $r
     * @param       $i
     * @param       $s
     * @param       $t
     * @param       $j
     *
     * @return mixed
     */
    private function perm3(array $a, $r, $i, $s, $t, $j)
    {
        array_push($r[$j], ($a[$i]));
        array_push($t, $r[$j]);
        for ($k = 1, $lrj = count($r[$j]); $k < $lrj; $k++) {
            $t = $this->perm4($r, $s, $t, $j, $lrj, $k);
        }
        return $t;
    }

    /**
     * @param $r
     * @param $s
     * @param $t
     * @param $j
     * @param $lrj
     * @param $k
     *
     * @return mixed
     */
    private function perm4($r, $s, $t, $j, $lrj, $k)
    {
        for ($l = 0; $l < $lrj; $l++) {
            $s = $this->perm5($r, $s, $j, $lrj, $k, $l);
        }
        $t[count($t)] = $s;
        return $t;
    }

    /**
     * @param $r
     * @param $s
     * @param $j
     * @param $lrj
     * @param $k
     * @param $l
     *
     * @return mixed
     */
    private function perm5($r, $s, $j, $lrj, $k, $l)
    {
        $s[$l] = $r[$j][($k + $l) % $lrj];
        return $s;
    }
}

