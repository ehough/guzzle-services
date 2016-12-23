<?php namespace Hough\Guzzle\Command\Guzzle\Handler;

use Hough\Guzzle\Command\CommandInterface;
use Hough\Guzzle\Command\Exception\CommandException;
use Hough\Guzzle\Command\Guzzle\DescriptionInterface;
use Hough\Guzzle\Command\Guzzle\SchemaValidator;

/**
 * Handler used to validate command input against a service description.
 *
 * @author Stefano Kowalke <info@arroba-it.de>
 */
class ValidatedDescriptionHandler
{
    /** @var SchemaValidator $validator */
    private $validator;

    /** @var DescriptionInterface $description */
    private $description;

    /**
     * ValidatedDescriptionHandler constructor.
     *
     * @param DescriptionInterface $description
     * @param SchemaValidator|null $schemaValidator
     */
    public function __construct(DescriptionInterface $description, SchemaValidator $schemaValidator = null)
    {
        $this->description = $description;
        $this->validator = $schemaValidator ?: new SchemaValidator();
    }

    /**
     * @param callable $handler
     * @return \Closure
     */
    public function __invoke($handler)
    {
        $callback = array($this, '__callback');
        return function (CommandInterface $command) use ($handler, $callback) {
            return call_user_func($callback, $command, $handler);
        };
    }

    /**
     * @internal
     */
    public function __callback(CommandInterface $command, $handler)
    {
        $errors = array();
        $operation = $this->description->getOperation($command->getName());

        foreach ($operation->getParams() as $name => $schema) {
            $value = $command[$name];
            if (! $this->validator->validate($schema, $value)) {
                $errors = array_merge($errors, $this->validator->getErrors());
            } elseif ($value !== $command[$name]) {
                // Update the config value if it changed and no validation
                // errors were encountered
                $command[$name] = $value;
            }
        }

        if ($params = $operation->getAdditionalParameters()) {
            foreach ($command->toArray() as $name => $value) {
                // It's only additional if it isn't defined in the schema
                if (! $operation->hasParam($name)) {
                    // Always set the name so that error messages are useful
                    $params->setName($name);
                    if (! $this->validator->validate($params, $value)) {
                        $errors = array_merge($errors, $this->validator->getErrors());
                    } elseif ($value !== $command[$name]) {
                        $command[$name] = $value;
                    }
                }
            }
        }

        if ($errors) {
            throw new CommandException('Validation errors: ' . implode("\n", $errors), $command);
        }

        return call_user_func($handler, $command);
    }
}
