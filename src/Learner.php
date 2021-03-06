<?php

namespace Rubix\ML;

use Rubix\ML\Datasets\Dataset;

interface Learner extends Estimator
{
    /**
     * Train the learner with a dataset.
     *
     * @param  \Rubix\ML\Datasets\Dataset  $dataset
     * @return void
     */
    public function train(Dataset $dataset) : void;
}