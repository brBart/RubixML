<?php

namespace Rubix\ML\AnomalyDetectors;

use Rubix\ML\Learner;
use Rubix\ML\Persistable;
use Rubix\ML\Kernels\SVM\RBF;
use Rubix\ML\Datasets\Dataset;
use Rubix\ML\Datasets\DataFrame;
use Rubix\ML\Kernels\SVM\Kernel;
use InvalidArgumentException;
use RuntimeException;
use svmmodel;
use svm;

/**
 * One Class SVM
 * 
 * An unsupervised Support Vector Machine used for anomaly detection. The One Class
 * SVM aims to find a maximum margin between a set of data points and the origin,
 * rather than between classes like the multiclass SVM.
 * 
 * > **Note**: This estimator requires the SVM PHP extension which uses the LIBSVM
 * engine written in C++ under the hood.
 * 
 * References:
 * [1] C. Chang et al. (2011). LIBSVM: A library for support vector machines.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class OneClassSVM implements Learner, Persistable
{
    /**
     * The support vector machine instance.
     * 
     * @var \svm
     */
    protected $svm;

    /**
     * The trained model instance.
     * 
     * @var \svmmodel|null
     */
    protected $model;

    /**
     * @param  float  $nu
     * @param  \Rubix\ML\Kernels\SVM\Kernel|null  $kernel
     * @param  bool  $shrinking
     * @param  float  $tolerance
     * @param  float  $cacheSize
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __construct(float $nu = 0.5, ?Kernel $kernel = null, bool $shrinking = true,
                                float $tolerance = 1e-3, float $cacheSize = 100.)
    {
        if (!extension_loaded('svm')) {
            throw new RuntimeException('SVM extension is not loaded, check'
                . ' PHP configuration.');
        }

        if ($nu < 0. or $nu > 1.) {
            throw new InvalidArgumentException('Nu must be between 0 and 1'
                . ", $nu given.");
        }

        if (is_null($kernel)) {
            $kernel = new RBF();
        }

        if ($tolerance < 0.) {
            throw new InvalidArgumentException('Tolerance cannot be less than 0,'
                . " $tolerance given.");
        }

        if ($cacheSize <= 0.) {
            throw new InvalidArgumentException('Cache size must be greater than'
                . " 0M, {$cacheSize}M given.");
        }

        $options = [
            svm::OPT_TYPE => svm::ONE_CLASS,
            svm::OPT_NU => $nu,
            svm::OPT_SHRINKING => $shrinking,
            svm::OPT_EPS => $tolerance,
            svm::OPT_CACHE_SIZE => $cacheSize,
        ];

        $options = array_replace($options, $kernel->options());

        $this->svm = new svm();
        $this->svm->setOptions($options);
    }

    /**
     * Return the integer encoded type of estimator this is.
     *
     * @return int
     */
    public function type() : int
    {
        return self::DETECTOR;
    }

    /**
     * Train the learner with a dataset.
     *
     * @param  \Rubix\ML\Datasets\Dataset  $dataset
     * @throws \InvalidArgumentException
     * @return void
     */
    public function train(Dataset $dataset) : void
    {
        if ($dataset->typeCount(DataFrame::CONTINUOUS) !== $dataset->numColumns()) {
            throw new InvalidArgumentException('This estimator only works'
                . ' with continuous features.');
        }

        $this->model = $this->svm->train($dataset->samples());
    }

    /**
     * Make predictions from a dataset.
     *
     * @param  \Rubix\ML\Datasets\Dataset  $dataset
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return array
     */
    public function predict(Dataset $dataset) : array
    {
        if (in_array(DataFrame::CATEGORICAL, $dataset->types())) {
            throw new InvalidArgumentException('This estimator only works with'
            . ' continuous features.');
        }

        if (is_null($this->model)) {
            throw new RuntimeException('Estimator has not been trained.');
        }

        $predictions = [];

        foreach ($dataset as $sample) {
            $predictions[] = $this->model->predict($sample) !== 1. ? 0 : 1;
        }

        return $predictions;
    }
}