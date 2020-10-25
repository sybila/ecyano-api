<?php

declare(strict_types=1);

namespace App\Entity\Repositories;

use App\Entity\Bioquantity;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Alexandra Stanová stanovaalex@mail.muni.cz
 */
class BioquantityRepository implements IEndpointRepository
{

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\BioquantityRepository */
	private $repository;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(Bioquantity::class);
	}

	public function get(int $id)
	{
		return $this->em->find(Bioquantity::class, $id);
	}

    protected static function alias(): string
    {
        return 'bq';
    }

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('bq.id, bq.name, bq.organismId, bq.userId, bq.isValid, bq.value, bq.link, bq.timeFrom, bq.timeTo, bq.valueFrom, bq.valueTo, bq.valueStep');

		/** FIX: Below statement should be used but is broken by @satanio commit 18/10/2020 */
		//$query = QueryRepositoryHelper::addPaginationSortDql($query, $sort, $limit);
		return $query->getQuery()->getArrayResult();
	}


	public function getNumResults(array $filter): int
	{
		return ((int) $this->buildListQuery($filter)
				->select('COUNT(bq)')
				->getQuery()
				->getScalarResult());
	}


	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(Bioquantity::class, 'bq');
		/** FIX: Below statement should be used but is broken by @satanio commit 18/10/2020 */
		//$query = QueryRepositoryHelper::addFilterDql($query, $filter);
		return $query;
	}

}
