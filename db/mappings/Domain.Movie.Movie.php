<?php

declare(strict_types=1);

/** @var ClassMetadata $metadata  */

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Domain\Movie\Data\DoctrineMovieRepository;

$builder = new ClassMetadataBuilder($metadata);

$builder->setTable(DoctrineMovieRepository::TABLE);
$builder->setCustomRepositoryClass(DoctrineMovieRepository::class);

$builder->createField('id', Types::INTEGER)->makePrimaryKey()->generatedValue()->build();
$builder->addField('title', Types::STRING);
$builder->addField('link', Types::STRING);
$builder->addField('description', Types::TEXT);
$builder->addField('pubDate', Types::DATETIME_IMMUTABLE);
$builder->addField('image', Types::STRING);
