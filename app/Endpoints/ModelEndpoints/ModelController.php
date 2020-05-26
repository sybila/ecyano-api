<?php

namespace App\Controllers;

use App\Entity\{
	Model,
	IdentifiedObject,
	ModelCompartment,
	ModelConstraint,
	ModelEvent,
	ModelFunctionDefinition,
	ModelInitialAssignment,
	ModelParameter,
	ModelReaction,
	ModelRule,
	ModelUnitDefinition,
	Repositories\IEndpointRepository,
	Repositories\ModelRepository
};
use App\Exceptions\{
	DependentResourcesBoundException,
	MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelRepository $repository
 * @method Model getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelController extends SBaseController
{
	/** @var ModelRepository */
	private $modelRepository;

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$this->modelRepository = $c->get(ModelRepository::class);
	}

    protected static function getAlias(): string
    {
        return 'm';
    }

	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'userId', 'approvedId', 'status'];
	}

	protected function getData(IdentifiedObject $model): array
	{
		/** @var Model $model */
		$sBaseData = parent::getData($model);
		return array_merge($sBaseData, [
			'userId' => $model->getUserId(),
			'approvedId' => $model->getApprovedId(),
			'description' => $model->getDescription(),
			'status' => (string)$model->getStatus(),
			//'origin' => $model->getOrigin(),
			'compartments' => $model->getCompartments()->map(function (ModelCompartment $compartment) {
				return ['id' => $compartment->getId(), 'name' => $compartment->getName()];
			})->toArray(),
			'constraints' => $model->getConstraints()->map(function (ModelConstraint $constraint) {
				return ['id' => $constraint->getId(), 'formula' => $constraint->getFormula()];
			})->toArray(),
			'events' => $model->getEvents()->map(function (ModelEvent $event) {
				return ['id' => $event->getId(), 'name' => $event->getName()];
			})->toArray(),
			'functionDefinitions' => $model->getFunctionDefinitions()->map(function (ModelFunctionDefinition $functionDefinition) {
				return ['id' => $functionDefinition->getId(), 'name' => $functionDefinition->getName()];
			})->toArray(),
			'initialAssignments' => $model->getInitialAssignments()->map(function (ModelInitialAssignment $initialAssignment) {
				return ['id' => $initialAssignment->getId(), 'formula' => $initialAssignment->getFormula()];
			})->toArray(),
			'parameters' => $model->getParameters()->map(function (ModelParameter $parameter) {
				return ['id' => $parameter->getId(), 'name' => $parameter->getName()];
			})->toArray(),
			'reactions' => $model->getReactions()->map(function (ModelReaction $reaction) {
				return ['id' => $reaction->getId(), 'name' => $reaction->getName()];
			})->toArray(),
			'rules' => $model->getRules()->map(function (ModelRule $rule) {
			    return ['id' => $rule->getId(), 'equation' => $rule->getEquation()];
			})->toArray(),
			'unitDefinitions' => $model->getUnitDefinitions()->map(function (ModelUnitDefinition $unitDefinition) {
				return ['id' => $unitDefinition->getId(), 'name' => $unitDefinition->getName()];
			})->toArray()
		]);
	}

	protected function setData(IdentifiedObject $model, ArgumentParser $data): void
	{
		/** @var Model $model */
		parent::setData($model, $data);
		!$data->hasKey('userId') ?: $model->setUserId($data->getString('userId'));
		!$data->hasKey('approvedId') ?: $model->setApprovedId($data->getString('approvedId'));
		!$data->hasKey('description') ?: $model->setDescription($data->getString('description'));
		!$data->hasKey('status') ?: $model->setStatus($data->getString('status'));
		!$data->hasKey('origin') ?: $model->setOrigin($data->getString('origin'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('userId'))
			throw new MissingRequiredKeyException('userId');
		if (!$body->hasKey('sbmlId'))
			throw new MissingRequiredKeyException('sbmlId');
		return new Model;
	}

	protected function checkInsertObject(IdentifiedObject $model): void
	{
		/** @var Model $model */
		if ($model->getUserId() === null)
			throw new MissingRequiredKeyException('userId');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
		/** @var Model $model */
		$model = $this->getObject($args->getInt('id'));
		if (!$model->getCompartments()->isEmpty())
			throw new DependentResourcesBoundException('compartment');
		if (!$model->getConstraints()->isEmpty())
			throw new DependentResourcesBoundException('constraints');
		if (!$model->getEvents()->isEmpty())
			throw new DependentResourcesBoundException('events');
		if (!$model->getFunctionDefinitions()->isEmpty())
			throw new DependentResourcesBoundException('functionDefinitions');
		if (!$model->getInitialAssignments()->isEmpty())
			throw new DependentResourcesBoundException('initialAssignments');
		if (!$model->getParameters()->isEmpty())
			throw new DependentResourcesBoundException('parameters');
		if (!$model->getRules()->isEmpty())
			throw new DependentResourcesBoundException('rules');
		if (!$model->getReactions()->isEmpty())
			throw new DependentResourcesBoundException('reactions');
		if (!$model->getUnitDefinitions()->isEmpty())
			throw new DependentResourcesBoundException('unitDefinitions');
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = parent::getValidatorArray();
		return new Assert\Collection(array_merge($validatorArray, [
			'userId' => new Assert\Type(['type' => 'integer']),
			'description' => new Assert\Type(['type' => 'string']),
			'visualisation' => new Assert\Type(['type' => 'string']),
			'status' => new Assert\Type(['type' => 'string']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'model';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelRepository::Class;
	}

	protected function getSub($entity)
	{
		echo $entity;
	}

}
