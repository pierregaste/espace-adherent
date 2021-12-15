<?php

namespace App\Normalizer\Indexer;

use App\JeMengage\Timeline\TimelineFeedTypeEnum;

abstract class AbstractJeMengageTimelineFeedNormalizer extends AbstractIndexerNormalizer
{
    final public function normalize($object, $format = null, array $context = [])
    {
        return [
            'type' => $this->getType(),
            'is_local' => $this->isLocal($object),
            'uuid' => $object->getUuid()->toString(),
            'title' => $this->getTitle($object),
            'description' => $this->getDescription($object),
            'image' => $this->getImage($object),
            'date' => $this->formatDate($this->getDate($object)),
            'time_zone' => $this->getTimeZone($object),
            'author' => $this->getAuthor($object),
        ];
    }

    protected function getTimeZone(object $object): ?string
    {
        return 'Europe/Paris';
    }

    protected function getImage(object $object): ?string
    {
        return null;
    }

    final private function getType(): string
    {
        return TimelineFeedTypeEnum::CLASS_MAPPING[$this->getClassName()];
    }

    abstract protected function getTitle(object $object): string;

    abstract protected function getDescription(object $object): ?string;

    abstract protected function isLocal(object $object): bool;

    abstract protected function getDate(object $object): ?\DateTime;

    abstract protected function getAuthor(object $object): ?string;
}
