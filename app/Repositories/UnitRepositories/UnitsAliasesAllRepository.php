<?php

namespace App\Entity\Repositories;

use App\Entity\UnitAlias;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class UnitsAliasesAllRepository implements IEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\UnitsAliasesAllRepository */
	private $repository;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(UnitAlias::class);
	}

	public function get(int $id)
	{
		return $this->em->find(UnitAlias::class, $id);
	}

    protected static function alias(): string
    {
        return 'a';
    }

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(a)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('a.id, IDENTITY(a.unitId) AS unit_id, a.alternative_name');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
		return $query->getQuery()->getArrayResult();
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(UnitAlias::class, 'a');
        $query = $this->addFilterDql($query, $filter);
		return $query;
	}
}
