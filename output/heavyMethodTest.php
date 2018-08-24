<?php
declare(strict_types=1);

// CURRENTLY BREAKING ON STEP 4 -> 5, due to the early returns found in permutate()
class HeavyMethodTest
{
    public function run(array $probabilities): float
    { $probabilities_mm0 = $probabilities; $total = array_sum($probabilities_mm0);
        foreach ($probabilities_mm0 as $index => $value) {
            $probabilities_mm0[$index] = $value / $total;
        }
        $probabilities      = ($probabilities_mm0);
        $groups             = $this->permutate(array_keys($probabilities)); $probabilities_mm9 = $probabilities;  $groups_mm10 = $groups; $groupMeans = [];
        foreach ($groups_mm10 as $group) {$groupMean        = 0; $group_mm1 = $group;  $probabilities_mm2 = $probabilities_mm9; 
        $groupProbability = 0;
        foreach ($group_mm1 as $setKey) {
            $groupMean        += pow((1 - $groupProbability), -1);
            $groupProbability += $probabilities_mm2[$setKey];
        }
            $groupMeans[] = ($groupMean);
        }
        $groupMeans         = ($groupMeans); $probabilities_mm7 = $probabilities;  $groups_mm8 = $groups; $groupProbabilities = [];
        foreach ($groups_mm8 as $group) { $group_mm3 = $group;  $probabilities_mm4 = $probabilities_mm7; $groupProbability = $probabilities_mm4[reset($group_mm3)];
        array_shift($group_mm3);
        while (count($group_mm3) > 1) {
            $totalProbRemaining = 0;
            foreach ($group_mm3 as $setKey) {
                $totalProbRemaining += $probabilities_mm4[$setKey];
            }
            $weight           = $probabilities_mm4[array_shift($group_mm3)] / $totalProbRemaining;
            $groupProbability *= $weight;
        }
            $groupProbabilities[] = ($groupProbability);
        }
        $groupProbabilities = ($groupProbabilities); $groupMeans_mm5 = $groupMeans;  $groupProbabilities_mm6 = $groupProbabilities; $total = 0;
        foreach ($groupMeans_mm5 as $index => $mean) {
            $total += $mean * $groupProbabilities_mm6[$index];
        }

        return ($total);
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

        for ($i = 1, $la = count($a); $i < $la; $i++) { $a_mm29 = $a;  $r_mm30 = $r;  $i_mm31 = $i;  $s_mm32 = $s;  $t_mm33 = $t; for ($j = 0, $lr = count($r_mm30); $j < $lr; $j++) { $a_mm23 = $a_mm29;  $r_mm24 = $r_mm30;  $i_mm25 = $i_mm31;  $s_mm26 = $s_mm32;  $t_mm27 = $t_mm33;  $j_mm28 = $j; array_push($r_mm24[$j_mm28], ($a_mm23[$i_mm25]));
        array_push($t_mm27, $r_mm24[$j_mm28]);
        for ($k = 1, $lrj = count($r_mm24[$j_mm28]); $k < $lrj; $k++) { $r_mm17 = $r_mm24;  $s_mm18 = $s_mm26;  $t_mm19 = $t_mm27;  $j_mm20 = $j_mm28;  $lrj_mm21 = $lrj;  $k_mm22 = $k; for ($l = 0; $l < $lrj_mm21; $l++) { $r_mm11 = $r_mm17;  $s_mm12 = $s_mm18;  $j_mm13 = $j_mm20;  $lrj_mm14 = $lrj_mm21;  $k_mm15 = $k_mm22;  $l_mm16 = $l; $s_mm12[$l_mm16] = $r_mm11[$j_mm13][($k_mm15 + $l_mm16) % $lrj_mm14];
            $s_mm18 = ($s_mm12);
        }
        $t_mm19[count($t_mm19)] = $s_mm18;
            $t_mm27 = ($t_mm19);
        }
            $t_mm33 = ($t_mm27);
        }
        $r_mm30 = $t_mm33;
            $r = ($r_mm30);
        }

        return $r;
    }

    

    

    

    

    

    

    

    

    
}

