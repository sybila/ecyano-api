<?php

namespace App\Controllers;

use IAuthWritableRepositoryController;
use App\Entity\{Model,
    ModelCompartment,
    ModelSpecie,
    ModelReaction,
    ModelRule,
    ModelUnitDefinition,
    IdentifiedObject,
    Repositories\IEndpointRepository,
    Repositories\ModelRepository,
    Repositories\ModelCompartmentRepository};
use App\Exceptions\{DependentResourcesBoundException, MissingRequiredKeyException, WrongParentException};
use App\Helpers\ArgumentParser;
use SBaseControllerCommonable;
use Slim\Container;
use Slim\Http\{
	Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read ModelCompartmentRepository $repository
 * @method ModelCompartment getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
final class ModelCompartmentController extends ParentedRepositoryController implements IAuthWritableRepositoryController
{
    use SBaseControllerCommonable;

    protected static function getAlias(): string
    {
        return 'c';
    }

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	protected function getData(IdentifiedObject $compartment): array
	{
		/** @var ModelCompartment $compartment */
		$sBaseData = $this->getSBaseData($compartment);
		return array_merge ($sBaseData, [
			'spatialDimensions' => $compartment->getSpatialDimensions(),
			'size' => $compartment->getSize(),
			'isConstant' => $compartment->getIsConstant(),
			'species' => $compartment->getSpecies()->map(function (ModelSpecie $specie) {
				return ['id' => $specie->getId(), 'name' => $specie->getName()];
			})->toArray(),
			'reactions' => $compartment->getReactions()->map(function (ModelReaction $reaction) {
				return ['id' => $reaction->getId(), 'name' => $reaction->getName()];
			})->toArray(),
			'rules' => $compartment->getRules()->map(function (ModelRule $rule) {
				return ['id' => $rule->getId(), 'equation' => $rule->getEquation()];
			})->toArray(),
			'unitDefinitions' => $compartment->getUnitDefinitions()->map(function (ModelUnitDefinition $unit) {
				return ['id' => $unit->getId(), 'symbol' => $unit->getSymbol()];
			})->toArray(),
		]);
	}

	protected function setData(IdentifiedObject $compartment, ArgumentParser $data): void
	{
		/** @var ModelCompartment $compartment */
		$this->setSBaseData($compartment, $data);
		$compartment->getModelId() ?: $compartment->setModelId($this->repository->getParent()->getId());
		!$data->hasKey('spatialDimensions') ?: $compartment->setSpatialDimensions($data->getString('spatialDimensions'));
		!$data->hasKey('size') ?: $compartment->setSize($data->getString('size'));
		!$data->hasKey('isConstant') ?: $compartment->setIsConstant($data->getInt('isConstant'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		if (!$body->hasKey('sbmlId'))
			throw new MissingRequiredKeyException('sbmlId');
		if (!$body->hasKey('isConstant'))
			throw new MissingRequiredKeyException('isConstant');
		return new ModelCompartment;
	}

	protected function checkInsertObject(IdentifiedObject $compartment): void
	{
		/** @var ModelCompartment $compartment */
		if ($compartment->getModelId() === null)
			throw new MissingRequiredKeyException('modelId');
		if ($compartment->getSbmlId() === null)
			throw new MissingRequiredKeyException('sbmlId');
		if ($compartment->getIsConstant() === null)
			throw new MissingRequiredKeyException('isConstant');
	}

	public function delete(Request $request, Response $response, ArgumentParser $args): Response
	{
        $compartment = $this->getObject($args->getInt('id'));
		if (!$compartment->getSpecies()->isEmpty())
			throw new DependentResourcesBoundException('specie');
		if (!$compartment->getRules()->isEmpty())
			throw new DependentResourcesBoundException('rules');
		if (!$compartment->getReactions()->isEmpty())
			throw new DependentResourcesBoundException('reaction');
		if (!$compartment->getUnitDefinitions()->isEmpty())
			throw new DependentResourcesBoundException('unitDefinitions');
		return parent::delete($request, $response, $args);
	}

	protected function getValidator(): Assert\Collection
	{
		$validatorArray = $this->getSBaseValidator();
		return new Assert\Collection(array_merge($validatorArray, [
			'modelId' => new Assert\Type(['type' => 'integer']),
			'isConstant' => new Assert\Type(['type' => 'integer']),
			'spatialDimensions' => new Assert\Type(['type' => 'double']),
			'size' => new Assert\Type(['type' => 'double']),
		]));
	}

	protected static function getObjectName(): string
	{
		return 'modelCompartment';
	}

	protected static function getRepositoryClassName(): string
	{
		return ModelCompartmentRepository::Class;
	}

	protected static function getParentRepositoryClassName(): string
	{
		return ModelRepository::class;
	}

	protected function getParentObjectInfo(): ParentObjectInfo
	{
	    return new ParentObjectInfo('model-id',Model::class);
	}

    protected function checkParentValidity(IdentifiedObject $parent, IdentifiedObject $child)
    {
        // TODO: Implement checkParentValidity() method.
    }
}
