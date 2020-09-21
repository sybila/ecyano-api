<?php

namespace App\Controllers;

use App\Entity\AnalysisType;
use App\Entity\IdentifiedObject;
use App\Entity\Repositories\AnalysisTypeRepository;
use App\Exceptions\MissingRequiredKeyException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property-read AnalysisTypeRepository $repository
 * @method AnalysisType getObject(int $id)
 */
class AnalysisTypeController extends WritableRepositoryController
{

    protected static function getRepositoryClassName(): string
    {
        return AnalysisTypeRepository::class;
    }

    protected static function getObjectName(): string
    {
        return 'analysisType';
    }

    protected function getData(IdentifiedObject $object): array
    {
        /** @var AnalysisType $object */
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'description' => $object->getDescription(),
            'annotation' => $object->getAnnotation()
        ];
    }

    protected static function getAlias(): string
    {
        return 'at';
    }

    protected static function getAllowedSort(): array
    {
        return ['id', 'name'];
    }

    protected function setData(IdentifiedObject $object, ArgumentParser $body): void
    {
        /** @var AnalysisType $analysisType */
        if ($body->hasKey('name'))
            $analysisType->setName($body->getString('name'));
        if ($body->hasKey('description'))
            $analysisType->setDescription($body->getString('description'));
        if ($body->hasKey('annotation'))
            $analysisType->setAnnotation($body->getString('annotation'));
    }

    protected function createObject(ArgumentParser $body): IdentifiedObject
    {
        return new AnalysisType;
    }

    protected function checkInsertObject(IdentifiedObject $object): void
    {
        /** @var AnalysisType $analysisType */
        if ($analysisType->getName() == '')
            throw new MissingRequiredKeyException('name');
        if ($analysisType->getDescription() == '')
            throw new MissingRequiredKeyException('description');
        if ($analysisType->getAnnotation() == '')
            throw new MissingRequiredKeyException('annotation');
    }

    protected function getValidator(): Assert\Collection
    {
        return new Assert\Collection([
            'name' => new Assert\Type(['type' => 'string']),
            'description' => new Assert\Type(['type' => 'string']),
            'annotation' => new Assert\Type(['type' => 'string']),
        ]);
    }
}