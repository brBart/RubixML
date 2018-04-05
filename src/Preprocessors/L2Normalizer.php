<?php

namespace Rubix\Engine\Preprocessors;

class L2Normalizer implements Preprocessor
{
    /**
     * @param  array  $samples
     * @param  array|null  $outcomes
     * @return void
     */
    public function fit(array $samples, ?array $outcomes = null) : void
    {
        //
    }

    /**
     * Normalize the dataset.
     *
     * @param  array  $samples
     * @return void
     */
    public function transform(array &$samples) : void
    {
        foreach ($samples as &$sample) {
            $norm = sqrt(array_reduce($sample, function ($carry, $feature) {
                return $carry += $feature ** 2;
            }, 0)) + 1e-10;

            foreach ($sample as &$feature) {
                $feature /= $norm;
            }
        }
    }
}
