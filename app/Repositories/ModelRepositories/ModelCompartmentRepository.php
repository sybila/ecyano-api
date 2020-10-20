<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\ModelCompartment;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Exception;

class ModelCompartmentRepository implements IDependentEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;

	/** @var Model */
	private $model;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelCompartment::class);
	}

	public function get(int $id)
	{
		return $this->em->find(ModelCompartment::class, $id);
	}

    protected static function alias(): string
    {
        return 'c';
    }

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(c)')
			->getQuery()
			->getSingleScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('c.id, c.name, c.sbmlId, c.sboTerm, c.notes, c.annotation, c.spatialDimensions, c.size, c.isConstant');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
        return $query->getQuery()->getArrayResult();
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelCompartment::class, 'c')
			->where('c.modelId = :modelId')
			->setParameter('modelId', $this->model->getId());
        $query = $this->addFilterDql($query, $filter);
		return $query;
	}

    public function getParent(): IdentifiedObject
    {
        return $this->model;
    }

    /**
     * @param IdentifiedObject $object
     * @throws Exception
     */
    public function setParent(IdentifiedObject $object): void
    {
        $className = Model::class;
        if (!($object instanceof $className))
            throw new Exception('Parent of compartment must be ' . $className);
        $this->model = $object;
    }

    public function add($object): void
    {
        // TODO: Implement add() method.
    }

    public function remove($object): void
    {
        // TODO: Implement remove() method.
    }
}
