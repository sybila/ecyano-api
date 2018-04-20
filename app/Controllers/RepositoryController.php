<?php

namespace App\Controllers;

use App\Entity\IdentifiedObject;
use App\Entity\Repositories\IRepository;
use App\Exceptions\ApiException;
use App\Exceptions\InternalErrorException;
use App\Exceptions\MalformedInputException;
use App\Exceptions\NonExistingObjectException;
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class RepositoryController extends AbstractController
{
	use SortableController;
	use PageableController;

	/** @var IRepository */
	protected $repository;

	/**
	 * function(Request $request, Response $response, ArgumentParser $args)
	 * @var callable[]
	 */
	protected $beforeRequest = [];

	abstract protected static function getRepositoryClassName(): string;
	abstract protected static function getObjectName(): string;
	abstract protected function getData($entity): array;

	/**
	 * @param array $events
	 * @param array ...$args
	 * @internal
	 */
	protected function runEvents(array $events, ...$args)
	{
		foreach ($events as $event)
			call_user_func_array($event, $args);
	}

	protected function getReadIds(ArgumentParser $args): array
	{
		// id parameter format should be checked in route
		return array_map(function($item)
		{
			return (int)$item;
		}, explode(',', $args->getString('id')));
	}

	protected function getFilter(ArgumentParser $args): array
	{
		return [];
	}

	public function __construct(Container $c)
	{
		parent::__construct($c);
		$className = static::getRepositoryClassName();
		$this->repository = new $className($c['em']);
	}

	public function read(Request $request, Response $response, ArgumentParser $args)
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);

		$filter = static::getFilter($args);
		$numResults = $this->repository->getNumResults($filter);
		$limit = static::getPaginationData($args, $numResults);
		$response = $response->withHeader('X-Count', $numResults);
		$response = $response->withHeader('X-Pages', $limit['pages']);

		return self::formatOk($response, $this->repository->getList($filter, self::getSort($args), $limit));
	}

	public function readIdentified(Request $request, Response $response, ArgumentParser $args): Response
	{
		$this->runEvents($this->beforeRequest, $request, $response, $args);

		$data = [];
		foreach ($this->getReadIds($args) as $id)
			$data[] = $this->getData($this->getObject((int)$id));

		return self::formatOk($response, $data);
	}

	/**
	 * @param int $id
	 * @return mixed
	 * @throws ApiException
	 */
	protected function getObject(int $id)
	{
		try {
			$ent = $this->repository->get($id);
			if (!$ent)
				throw new NonExistingObjectException($id, static::getObjectName());
		}
		catch (ORMException $e) {
			throw new InternalErrorException('Failed getting ' . static::getObjectName() . ' ID ' . $id, $e);
		}

		return $ent;
	}

	// ============================================== HELPERS

	protected static function identifierGetter(): \Closure
	{
		return function(IdentifiedObject $object) { return $object->getId(); };
	}
}
