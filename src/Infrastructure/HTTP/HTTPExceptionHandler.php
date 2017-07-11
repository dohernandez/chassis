<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\HTTP;

use Chassis\Infrastructure\Exception\ExceptionHandlerInterface;
use Chassis\Infrastructure\Exception\HttpExceptionInterface;
use Chassis\Infrastructure\Exception\NotFoundException;
use Chassis\Infrastructure\Exception\NotFoundHttpException;
use Chassis\Infrastructure\HTTP\Response\ResponseResolverInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class HTTPExceptionHandler implements ExceptionHandlerInterface
{
    const CONTEXT_ERROR_ID = 'unique_code';
    const DATA_MESSAGE = 'message';
    const DATA_ERRORS = 'errors';
    const DATA_ERROR_MESSAGE = 'error_message';
    const DATA_ERROR_TRACE = 'error_trace';

    /*
     * @var string
     */
    protected $errorId;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ResponseResolverInterface
     */
    private $responseResolver;

    /**
     * @param LoggerInterface $logger
     * @param ResponseResolverInterface $responseResolver
     */
    public function __construct(LoggerInterface $logger, ResponseResolverInterface $responseResolver)
    {
        $this->logger = $logger;
        $this->responseResolver = $responseResolver;
    }

    /**
     * @param Throwable $throwable
     *
     * @return Response
     */
    public function __invoke(Throwable $throwable): Response
    {
        if ($throwable instanceof NotFoundException) {
            $throwable = new NotFoundHttpException($throwable->getMessage(), $throwable);
        }

        $this->logger->log(
            $this->resolveLogLevel($throwable),
            $this->resolveLogMessage($throwable),
            $this->resolveContext($throwable)
        );

        return $this->respond($throwable);
    }

    /**
     * @param Throwable $throwable
     *
     * @return string
     */
    protected function resolveLogLevel(Throwable $throwable): string
    {
        return LogLevel::ERROR;
    }

    /**
     * @param Throwable $throwable
     *
     * @return string
     */
    protected function resolveLogMessage(Throwable $throwable): string
    {
        return (string) $throwable;
    }

    /**
     * @param Throwable $throwable
     *
     * @return array
     */
    protected function resolveContext(Throwable $throwable): array
    {
        return [
            self::CONTEXT_ERROR_ID => $this->getErrorId(),
        ];
    }

    /**
     * @return string
     */
    protected function getErrorId()
    {
        return $this->errorId ?: $this->errorId = $this->generateErrorId();
    }

    /**
     * @return string
     */
    protected function generateErrorId(): string
    {
        return sha1(microtime(true) . uniqid());
    }

    /**
     * @param Throwable $throwable
     *
     * @return Response
     */
    protected function respond(Throwable $throwable)
    {
        return $this->responseResolver->resolve($this->resolveResponseData($throwable), $this->resolveResponseCode($throwable));
    }

    /**
     * @param \Throwable $throwable
     *
     * @return array
     */
    protected function resolveResponseData(\Throwable $throwable): array
    {
        $data = [];

        $data[self::CONTEXT_ERROR_ID] = $this->getErrorId();
        $data[self::DATA_MESSAGE] = $throwable->getMessage();

        return $data;
    }

    /**
     * @param \Throwable $throwable
     *
     * @return int
     */
    protected function resolveResponseCode(\Throwable $throwable): int
    {
        if ($throwable instanceof HttpExceptionInterface) {
            return $throwable->getStatusCode();
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
