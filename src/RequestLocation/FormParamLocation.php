<?php
namespace Hough\Guzzle\Command\Guzzle\RequestLocation;

use Hough\Guzzle\Command\CommandInterface;
use Hough\Guzzle\Command\Guzzle\Operation;
use Hough\Guzzle\Command\Guzzle\Parameter;
use Hough\Psr7;
use Psr\Http\Message\RequestInterface;

/**
 * Add form_params to a request
 */
class FormParamLocation extends AbstractLocation
{
    /** @var string $contentType */
    protected $contentType = 'application/x-www-form-urlencoded; charset=utf-8';

    /** @var array $formParamsData */
    protected $formParamsData = array();

    /**
     * Set the name of the location
     *
     * @param string $locationName
     */
    public function __construct($locationName = 'formParam')
    {
        parent::__construct($locationName);
    }

    /**
     * @param CommandInterface $command
     * @param RequestInterface $request
     * @param Parameter        $param
     *
     * @return RequestInterface
     */
    public function visit(
        CommandInterface $command,
        RequestInterface $request,
        Parameter $param
    ) {
        $this->formParamsData['form_params'][$param->getWireName()] = $this->prepareValue(
            $command[$param->getName()],
            $param
        );

        return $request;
    }

    /**
     * @param CommandInterface $command
     * @param RequestInterface $request
     * @param Operation        $operation
     *
     * @return RequestInterface
     */
    public function after(
        CommandInterface $command,
        RequestInterface $request,
        Operation $operation
    ) {
        $data = $this->formParamsData;
        $this->formParamsData = array();
        $modify = array();

        // Add additional parameters to the form_params array
        $additional = $operation->getAdditionalParameters();
        if ($additional && $additional->getLocation() == $this->locationName) {
            foreach ($command->toArray() as $key => $value) {
                if (!$operation->hasParam($key)) {
                    $data['form_params'][$key] = $this->prepareValue($value, $additional);
                }
            }
        }

        $body = http_build_query($data['form_params'], '', '&');
        $modify['body'] = Psr7\stream_for($body);
        $modify['set_headers']['Content-Type'] = $this->contentType;
        $request = Psr7\modify_request($request, $modify);

        return $request;
    }
}
