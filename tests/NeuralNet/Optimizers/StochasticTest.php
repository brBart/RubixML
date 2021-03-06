<?php

namespace Rubix\ML\Tests\NeuralNet\Optimizers;

use Rubix\Tensor\Matrix;
use Rubix\ML\NeuralNet\Parameter;
use Rubix\ML\NeuralNet\Optimizers\Optimizer;
use Rubix\ML\NeuralNet\Optimizers\Stochastic;
use PHPUnit\Framework\TestCase;

class StochasticTest extends TestCase
{
    protected $optimizer;

    protected $gradients;

    protected $parameter;

    public function setUp()
    {
        $this->parameter = new Parameter(Matrix::quick([
            [0.1, 0.6, -0.4],
            [0.5, 0.6, -0.4],
            [0.1, 0.1, -0.7],
        ]));

        $this->gradients = Matrix::quick([
            [0.01, 0.05, -0.02],
            [-0.01, 0.02, 0.03],
            [0.04, -0.01, -0.5],
        ]);

        $this->optimizer = new Stochastic(0.001);
    }

    public function test_build_optimizer()
    {
        $this->assertInstanceOf(Stochastic::class, $this->optimizer);
        $this->assertInstanceOf(Optimizer::class, $this->optimizer);
    }

    public function test_step()
    {
        $step = $this->optimizer->step($this->parameter, $this->gradients);

        $this->assertInstanceOf(Matrix::class, $step);
        $this->assertEquals([3, 3], $step->shape());
    }
}
