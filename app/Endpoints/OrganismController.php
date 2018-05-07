<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Entity\Organism;
use App\Entity\Repositories\OrganismRepository;
use App\Exceptions\MalformedInputException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read OrganismRepository $repository
 * @method Organism getObject(int $id)
 */
final class OrganismController extends WritableRepositoryController
{
	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'code'];
	}

	protected function getData(IdentifiedObject $object): array
	{
		/** @var Organism $object */
		return [
			'id' => $object->getId(),
			'name' => $object->getName(),
			'code' => $object->getCode(),
		];
	}

	protected static function getRepositoryClassName(): string
	{
		return OrganismRepository::class;
	}

	protected static function getObjectName(): string
	{
		return 'organism';
	}

	protected function setData(IdentifiedObject $organism, ArgumentParser $body): void
	{
		/** @var Organism $organism */
		if ($body->hasKey('name'))
			$organism->setName($body->getString('name'));
		if ($body->hasKey('code'))
			$organism->setCode($body->getString('code'));
	}

	protected function createObject(ArgumentParser $body): IdentifiedObject
	{
		return new Organism;
	}

	protected function getValidator(): Assert\Collection
	{
		return new Assert\Collection([
			'name' => new Assert\Type(['type' => 'string']),
			'code' => new Assert\Type(['type' => 'string']),
		]);
	}

	protected function checkInsertObject(IdentifiedObject $organism): void
	{
		/** @var Organism $organism */
		if ($organism->getName() == '' || $organism->getCode() == '')
			throw new MalformedInputException('Input doesn\'t contain all required fields');
	}
}
