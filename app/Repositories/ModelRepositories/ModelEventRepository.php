<?php

namespace App\Entity\Repositories;

use App\Entity\Model;
use App\Entity\ModelEvent;
use App\Entity\IdentifiedObject;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Exception;

class ModelEventRepository implements IDependentEndpointRepository
{
    use QueryRepositoryHelper;

	/** @var EntityManager * */
	protected $em;

	/** @var \Doctrine\ORM\EntityRepository */
	private $repository;

    /** @var IdentifiedObject */
    private $object;

    public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->repository = $em->getRepository(ModelEvent::class);
	}

	protected static function getParentClassName(): string
	{
		return Model::class;
	}

	public function get(int $id)
	{
		return $this->em->find(ModelEvent::class, $id);
	}

    protected static function alias(): string
    {
        return 'e';
    }

	public function getNumResults(array $filter): int
	{
		return ((int)$this->buildListQuery($filter)
			->select('COUNT(e)')
			->getQuery()
			->getSingleScalarResult());
	}

	public function getList(array $filter, array $sort, array $limit): array
	{
		$query = $this->buildListQuery($filter)
			->select('e.id, e.name, e.sbmlId, e.sboTerm, e.notes, e.annotation, e.delay, e.trigger, e.priority');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
		return $query->getQuery()->getArrayResult();
	}

	public function getParent(): IdentifiedObject
	{
		return $this->object;
	}

    /**
     * @param IdentifiedObject $object
     * @throws Exception
     */
	public function setParent(IdentifiedObject $object): void
	{
		$className = static::getParentClassName();
		if (!($object instanceof $className))
			throw new Exception('Parent of event must be ' . $className);
		$this->object = $object;
	}

	private function buildListQuery(array $filter): QueryBuilder
	{
		$query = $this->em->createQueryBuilder()
			->from(ModelEvent::class, 'e')
			->where('e.modelId = :modelId')
			->setParameter('modelId', $this->object->getId());
        $query = $this->addFilterDql($query, $filter);
		return $query;
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
