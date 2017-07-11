<?php declare(strict_types = 1);

namespace Chassis\Infrastructure;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use SplDoublyLinkedList;
use SplStack;
use UnexpectedValueException;

class MiddlewareAwareTrait
{
    /**
     * Middleware call stack
     *
     * @var  SplStack
     * @link http://php.net/manual/class.splstack.php
     */
    protected $stack;

    /**
     * Middleware stack lock
     *
     * @var bool
     */
    protected $middlewareLock = false;

    protected function addMiddleware(callable $callable)
    {
        if ($this->middlewareLock) {
            throw new RuntimeException('Middleware can’t be added once the stack is dequeuing');
        }

        if (is_null($this->stack)) {
            $this->seedMiddlewareStack();
        }

        $next = $this->stack->top();
        $this->stack[] = function (
            ServerRequestInterface $request,
            ResponseInterface $response = null
        ) use (
            $callable,
            $next
        ) {
            $result = call_user_func($callable, $request, $response, $next);

            if ($result instanceof ResponseInterface === false) {
                throw new UnexpectedValueException(
                    sprintf('Middleware must return instance of %s', ResponseInterface::class)
                );
            }

            return $result;
        };

        return $this;
    }

    /**
     * @param callable|null $kernel
     *
     * @throws RuntimeException if the stack is seeded more than once
     */
    protected function seedMiddlewareStack(callable $kernel = null)
    {
        if (!is_null($this->stack)) {
            throw new RuntimeException('MiddlewareStack can only be seeded once.');
        }

        if ($kernel === null) {
            $kernel = $this;
        }

        $this->stack = new SplStack();
        $this->stack->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_KEEP);
        $this->stack[] = $kernel;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface|null $response
     *
     * @return ResponseInterface
     */
    public function callMiddlewareStack(
        ServerRequestInterface $request,
        ResponseInterface $response = null
    ): ResponseInterface {
        if (is_null($this->stack)) {
            $this->seedMiddlewareStack();
        }
        /** @var callable $start */
        $start = $this->stack->top();

        $this->middlewareLock = true;
        $response = $start($request, $response);
        $this->middlewareLock = false;

        return $response;
    }
}
