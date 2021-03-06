<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entity\AnnotationSource;
use App\Entity\Bioquantity;
use App\Entity\Attribute;
use App\Entity\ExperimentValues;
//use App\Entity\ModelUnitDefinition;
use App\Entity\Repositories\BioquantityRepository;
use App\Entity\IdentifiedObject;
use App\Exceptions\MissingRequiredKeyException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandra Stanová stanovaalex@mail.muni.cz
 * @property-read BioquantityRepository $repository
 * @method Bioquantity getObject(int $id, IEndpointRepository $repository = null, string $objectName = null)
 */
class BioquantityController extends WritableRepositoryController
{


	protected function checkInsertObject(IdentifiedObject $object): void
	{
		/* TODO: Restrictions - discuss */
	}


	protected function createObject(ArgumentParser $body): \App\Entity\IdentifiedObject
	{
		if (!$body->hasKey('name'))
			throw new MissingRequiredKeyException('name');
		if (!$body->hasKey('isValid'))
			throw new MissingRequiredKeyException('isValid');
		if (!$body->hasKey('value'))
			throw new MissingRequiredKeyException('value');
		return new Bioquantity;
	}


	protected function getData(IdentifiedObject $bioquantity): array
	{
		/** @var Bioquantity $bioquantity */
		return [
			'id' => $bioquantity->getId(),
			'name' => $bioquantity->getName(),
			'isValid' => $bioquantity->getIsValid(),
			'userId' => $bioquantity->getUserId(),
            'organism' => $bioquantity->getOrganism()!= null ? OrganismController::getData($bioquantity->getOrganism()):null,
			'value' => $bioquantity->getValue(),
			'link' => $bioquantity->getLink(),
			'timeFrom' => $bioquantity->getTimeFrom(),
			'timeTo' => $bioquantity->getTimeTo(),
			'valueFrom' => $bioquantity->getValueFrom(),
			'valueTo' => $bioquantity->getValueTo(),
			'valueStep' => $bioquantity->getValueStep(),
			// FIX: table `unit_attribute` is not present in production db, but referenced in Attribute object ??
			/* 'attributes' => $bioquantity->getAttributes()->map(function (Attribute $attributes) {
			  return ['id' => $attributes->getId(), 'name' => $attributes->getName()];
			  })->toArray(), */
			'variables' => $bioquantity->getVariables()->map(function (VariableValues $variables) {
					return ['id' => $variables->getId(), 'name' => $variables->getName()];
				})->toArray(),
			// NOTE: Commented this one out since unit definition module is being remade
			// DISCUSS: What should bioquantity map to in the new module?
			/* 'unitDefinitions' => $bioquantity->getUnitDefinitions()->map(function (UnitDefinition $unitDefinition) {
			  return ['id' => $unitDefinition->getId(), 'name' => $unitDefinition->getName()];
			  })->toArray(), */
            'annotations' => $bioquantity->getAnnotations($this->orm)
                ->map(function (AnnotationSource $ann) {
                    return ['id' => $ann->getId(),
                        'link' => $ann->getLink()];
                })->toArray()
		];
	}


	protected function getValidator(): \Symfony\Component\Validator\Constraints\Collection
	{
		return new Assert\Collection([
			'organismId' => new Assert\Type(['type' => 'integer']),
			'userId' => new Assert\Type(['type' => 'integer']),
			'name' => new Assert\Type(['type' => 'string']),
			'isValid' => new Assert\Type(['type' => 'integer']),
			'value' => new Assert\Type(['type' => 'float']),
			'link' => new Assert\Type(['type' => 'string']),
			'timeFrom' => new Assert\Type(['type' => 'float']),
			'timeTo' => new Assert\Type(['type' => 'float']),
			'valueFrom' => new Assert\Type(['type' => 'float']),
			'valueTo' => new Assert\Type(['type' => 'float']),
			'valueStep' => new Assert\Type(['type' => 'float'])
		]);
	}


	protected function setData(IdentifiedObject $bioquantity, ArgumentParser $data): void
	{
		/** @var Bioquantity $bioquantity */
		!$data->hasKey('organismId') ?: $bioquantity->setOrganism($data->getInt('organismId'));
		!$data->hasKey('userId') ?: $bioquantity->setUserId($data->getInt('userId'));
		!$data->hasKey('name') ?: $bioquantity->setName($data->getString('name'));
		!$data->hasKey('isValid') ?: $bioquantity->setIsValid($data->getInt('isValid'));
		!$data->hasKey('value') ?: $bioquantity->setValue($data->getFloat('value'));
		!$data->hasKey('link') ?: $bioquantity->setLink($data->getString('link'));
		!$data->hasKey('timeFrom') ?: $bioquantity->setTimeFrom($data->getFloat('timeFrom'));
		!$data->hasKey('timeTo') ?: $bioquantity->setTimeTo($data->getFloat('timeTo'));
		!$data->hasKey('valueFrom') ?: $bioquantity->setValueFrom($data->getFloat('valueFrom'));
		!$data->hasKey('valueTo') ?: $bioquantity->setValueTo($data->getFloat('valueTo'));
		!$data->hasKey('valueStep') ?: $bioquantity->setValueStep($data->getFloat('valueStep'));
	}


	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'organism'];
	}


	protected static function getObjectName(): string
	{
		return 'bioquantity';
	}


	protected static function getRepositoryClassName(): string
	{
		return BioquantityRepository::class;
	}


	protected static function getAlias(): string
	{
		return 'bq';
	}

    public function canList(?int $role, ?int $id): bool
    {
        return true;
    }

    public function canDetail(?int $role, ?int $id): bool
    {
        return true;
    }
}
