<?php
namespace Hough\Guzzle\Command\Guzzle\RequestLocation;

use Hough\Guzzle\Command\CommandInterface;
use Hough\Guzzle\Command\Guzzle\Operation;
use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Psr7;
use Psr\Http\Message\RequestInterface;

/**
 * Adds POST files to a request
 */
class MultiPartLocation extends AbstractLocation
{
    /** @var string $contentType */
    protected $contentType = 'multipart/form-data; boundary=';

    /** @var array $formParamsData */
    protected $multipartData = array();

    /**
     * Set the name of the location
     *
     * @param string $locationName
     */
    public function __construct($locationName = 'multipart')
    {
        parent::__construct($locationName);
    }

    /**
     * @param CommandInterface $command
     * @param RequestInterface $request
     * @param Parameter $param
     * @return RequestInterface
     */
    public function visit(
        CommandInterface $command,
        RequestInterface $request,
        Parameter $param
    ) {
        $this->multipartData[] = array(
            'name' => $param->getWireName(),
            'contents' => $this->prepareValue($command[$param->getName()], $param)
        );

        return $request;
    }


    /**
     * @param CommandInterface $command
     * @param RequestInterface $request
     * @param Operation $operation
     * @return RequestInterface
     */
    public function after(
        CommandInterface $command,
        RequestInterface $request,
        Operation $operation
    ) {
        $data = $this->multipartData;
        $this->multipartData = array();
        $modify = array();

        $body = new Psr7\MultipartStream($data);
        $modify['body'] = Psr7\stream_for($body);
        $request = Psr7\modify_request($request, $modify);
        if ($request->getBody() instanceof Psr7\MultipartStream) {
            // Use a multipart/form-data POST if a Content-Type is not set.
            $request->withHeader('Content-Type', $this->contentType . $request->getBody()->getBoundary());
        }

        return $request;
    }
}
