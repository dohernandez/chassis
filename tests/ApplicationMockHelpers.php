<?php declare(strict_types = 1);

namespace Tests\Chassis;

use Chassis\Infrastructure\HTTP\Controller\ControllerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ApplicationMockHelpers
{
    /**
     * @param $response
     * @param $action
     * @param $pathParams
     *
     * @return mixed
     */
    protected function mockController($response, $action, $pathParams)
    {
        return new class($response, $action, $pathParams) implements ControllerInterface {
            /**
             * @var TestCase
             */
            private $testCase;

            /*
             * @var Response
             */
            private $response;

            /**
             * @var string
             */
            private $action;

            /**
             * @var array
             */
            private $pathParams;

            public function __construct(Response $response, string $action, array $pathParams)
            {
                $this->response = $response;
                $this->action = $action;
                $this->pathParams = $pathParams;
            }

            public function __invoke(Request $request, string $action, array $pathParams): Response
            {
                $this->testCase->assertSame($this->action, $action);
                $this->testCase->assertSame($this->pathParams, $pathParams);

                return $this->response;
            }

            /**
             * @param TestCase $testCase
             */
            public function setTestCase(TestCase $testCase)
            {
                $this->testCase = $testCase;
            }
        };
    }

    /**
     * Creates a mock instance.
     *
     * @param string|null $classOrInterface
     * @param callable|null $init
     *
     * @return mixed
     */
    abstract protected function mock($classOrInterface = null, callable $init = null);
}
