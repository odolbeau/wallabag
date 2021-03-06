<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add starred_at column and set its value to updated_at for is_starred entries.
 */
class Version20170824113337 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf($entryTable->hasColumn('starred_at'), 'It seems that you already played this migration.');

        $entryTable->addColumn('starred_at', 'datetime', [
            'notnull' => false,
        ]);
    }

    public function postUp(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));
        $this->skipIf(!$entryTable->hasColumn('starred_at'), 'Unable to add starred_at colum');

        $this->connection->executeQuery(
            'UPDATE ' . $this->getTable('entry') . ' SET starred_at = updated_at WHERE is_starred = :is_starred',
            [
                'is_starred' => true,
            ]
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $entryTable = $schema->getTable($this->getTable('entry'));

        $this->skipIf(!$entryTable->hasColumn('starred_at'), 'It seems that you already played this migration.');

        $entryTable->dropColumn('starred_at');
    }

    private function getTable($tableName)
    {
        return $this->container->getParameter('database_table_prefix') . $tableName;
    }
}
