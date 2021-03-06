<?php

namespace App\Entity\Repositories;

use App\Entity\Experiment;
use App\Entity\ExperimentValues;
use App\Entity\ExperimentVariable;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ExperimentValueRepository implements IDependentEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	private $em;

	/** @var \Doctrine\ORM\ValueRepository */
	private $repository;

	/** @var ExperimentVariable */
	private $experimentVariable;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ExperimentValues::class);
	}

	protected static function getParentClassName(): string
	{
		return ExperimentVariable::class;
	}

    protected static function alias(): string
    {
        return 'v';
    }

	public function get(int $id)
	{
		return $this->em->find(ExperimentValues::class, $id);
	}

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(v)')
			->getQuery()
			->getScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('v.id, v.time, v.value');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
        return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->experimentVariable;
	}

	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new \Exception('Parent of variable must be ' . $className);
		$this->experimentVariable = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ExperimentValues::class, 'v')
            ->where('v.variableId = :variableId')
            ->setParameters([
                'variableId' => $this->experimentVariable->getId()
            ]);
        $query = $this->addFilterDql($query, $filter);
		return $query;
	}

    /**
     * @param ExperimentVariable $object
     */
    public function add($object): void
    {
    }

    public function remove($object): void
    {
    }
}
