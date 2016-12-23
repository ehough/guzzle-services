<?php
namespace Hough\Guzzle\Command\Guzzle\ResponseLocation;

use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\ResultInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AbstractLocation
 *
 * @package Hough\Guzzle\Command\Guzzle\ResponseLocation
 */
abstract class AbstractLocation implements ResponseLocationInterface
{
    /** @var string $locationName */
    protected $locationName;

    /**
     * Set the name of the location
     *
     * @param $locationName
     */
    public function __construct($locationName)
    {
        $this->locationName = $locationName;
    }

    /**
     * @param ResultInterface $result
     * @param ResponseInterface $response
     * @param Parameter $model
     * @return ResultInterface
     */
    public function before(
        ResultInterface $result,
        ResponseInterface $response,
        Parameter $model
    ) {
        return $result;
    }

    /**
     * @param ResultInterface $result
     * @param ResponseInterface $response
     * @param Parameter $model
     * @return ResultInterface
     */
    public function after(
        ResultInterface $result,
        ResponseInterface $response,
        Parameter $model
    ) {
        return $result;
    }

    /**
     * @param ResultInterface $result
     * @param ResponseInterface $response
     * @param Parameter $param
     * @return ResultInterface
     */
    public function visit(
        ResultInterface $result,
        ResponseInterface $response,
        Parameter $param
    ) {
        return $result;
    }
}
