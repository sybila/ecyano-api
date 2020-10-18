<?php

namespace App\Entity\Repositories;

use App\Entity\AnalysisTask;
use App\Helpers\QueryRepositoryHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class AnalysisToolRepository implements IEndpointRepository
{

    use QueryRepositoryHelper;

    /** @var EntityManager * */
    protected $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(AnalysisTask::class);
    }

    public function get(int $id)
    {
        return $this->em->find(AnalysisTask::class, $id);
    }

    protected static function alias(): string
    {
        return 'at';
    }

    public function getNumResults(array $filter): int
    {
        return $this->buildListQuery($filter)
            ->select('COUNT(at)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getList(array $filter, array $sort, array $limit): array
    {
        $query = $this->buildListQuery($filter)
            ->select('at.id, at.name, at.description, at.annotation, at.location');
        $query = $this->addPagingDql($query, $limit);
        $query = $this->addSortDql($query, $sort);
        return $query->getQuery()->getArrayResult();
    }

    private function buildListQuery(array $filter): QueryBuilder
    {
        $query = $this->em->createQueryBuilder()
            ->from(AnalysisTask::class, 'at');
        $query = $this->addFilterDql($query, $filter);
        return $query;
    }
}