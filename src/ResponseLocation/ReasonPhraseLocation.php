<?php
namespace Hough\Guzzle\Command\Guzzle\ResponseLocation;

use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Guzzle\Command\ResultInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Extracts the reason phrase of a response into a result field
 */
class ReasonPhraseLocation extends AbstractLocation
{

    /**
     * Set the name of the location
     *
     * @param string $locationName
     */
    public function __construct($locationName = 'reasonPhrase')
    {
        parent::__construct($locationName);
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
        $result[$param->getName()] = $param->filter(
            $response->getReasonPhrase()
        );

        return $result;
    }
}
