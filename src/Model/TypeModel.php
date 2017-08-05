<?php

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

class TypeModel {

    private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }

    public function getAllType() {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('t.id_Type', 't.libelle')
            ->from('types', 't')
            ->addOrderBy('t.libelle', 'ASC');
        return $queryBuilder->execute()->fetchAll();
    }
}