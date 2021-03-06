<?php

namespace App\Controllers;

use ExperimentEndpointAuthorizable;
use App\Entity\{
    ExperimentValues,
    ExperimentVariable,
    Experiment,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ExperimentRepository,
    Repositories\ExperimentVariableRepository,
    Repositories\ExperimentValueRepository
};

use IGroupRoleAuthWritableController;
use App\Exceptions\
{
	MissingRequiredKeyException,
	DependentResourcesBoundException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ExperimentValueRepository $repository
 * @method ExperimentValues getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ExperimentValueController extends ParentedRepositoryController implements IGroupRoleAuthWritableController
{

    use ExperimentEndpointAuthorizable;

	/** @var ExperimentValueRepository */
	private $valueRepository;
	private $variableRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->variableRepository = $c->get(ExperimentVariableRepository::class);
		$this->valueRepository = $c->get(ExperimentValueRepository::class);
	}

	protected static function getAllowedSort(): array
	{
		return ['id', 'time', 'value'];
	}


	protected function getData(IdentifiedObject $value): array
	{
		/** @var ExperimentValues $value */
		return [
			'time' => $value->getTime(),
			'value' => $value->getValue(),
		];
	}

	protected function setData(IdentifiedObject $value, ArgumentParser $data): void
	{
		/** @var ExperimentValues $value */
		$value->getVariableId() ?: $value->setVariableId($this->repository->getParent());
		!$data->hasKey('time') ?: $value->setTime($data->getFloat('time'));
		!$data->hasKey('value') ?: $value->setValue($data->getFloat('value'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
        if (!$body->hasKey('time'))
			throw new MissingRequiredKeyException('time');
		if (!$body->hasKey('value'))
			throw new MissingRequiredKeyException('value');
		return new ExperimentValues;
	}

	protected function checkInsertObject(IdentifiedObject $value): void
	{
		/** @var ExperimentValues $value */
		if ($value->getVariableId() === null)
			throw new MissingRequiredKeyException('variableId');
		if ($value->getTime() === null)
			throw new MissingRequiredKeyException('time');
		if ($value->getValue() === null)
			throw new MissingRequiredKeyException('value');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		$value = $this->getObject($args->getInt('id'));
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
            //'value' => new Assert\Type(['type' => 'float']),
			//'time' => new Assert\Type(['type' => 'double']),
		]);
	}

	public function createObjects(Request $request, Response $response)
    {
        foreach ($request->getParsedBody()['values'] as $val) {
            $newValue = new ExperimentValues();
            $newValue->setValue($val["value"]);
            $newValue->setTime($val["time"]);
            $variable = $this->variableRepository->get($val["variableId"]);
            $newValue->setVariableId($variable);
            $variable->addValue($newValue);
            $this->orm->persist($newValue);
            $this->orm->flush();
            dump($newValue->getValue());
        }
        //exit;
        return self::formatOk($response, []);
    }

	protected static function getObjectName(): string
	{
		return 'value';
	}

	protected static function getRepositoryClassName(): string
	{
		return ExperimentValueRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ExperimentVariableRepository::class;
	}

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('variable-id', ExperimentVariable::class);
	}


    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        // TODO: Implement checkParentValidity() method.
    }
}
