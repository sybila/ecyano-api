<?php

namespace App\Controllers;

use App\Entity\Organism;
use App\Entity\Repositories\OrganismRepository;
use App\Entity\Repositories\OrganismRepositoryImpl;
use App\Exceptions\
{
	ApiException, InternalErrorException, NonExistingObjectException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use Slim\Container;
use Slim\Http\{Request, Response};

/**
 * @property-read OrganismRepository $repository
 * @method Organism getObject(int $id)
 */
final class OrganismController extends RepositoryController
{
	protected static function getAllowedSort(): array
	{
		return ['id', 'name', 'code'];
	}

	/**
	 * @param Organism $entity
	 * @return array
	 */
	protected function getData($entity): array
	{
		return [
			'id' => $entity->getId(),
			'name' => $entity->getName(),
			'code' => $entity->getCode(),
		];
	}

	protected static function getRepositoryClassName(): string
	{
		return OrganismRepositoryImpl::class;
	}

	protected static function getObjectName(): string
	{
		return 'organism';
	}
}
