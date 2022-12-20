<?php

namespace Kykurniawan\Hmm\Database;

use Kykurniawan\Hmm\Helpers\DB;
use PDO;

class Repository
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected string $entity;

    public function getTableName()
    {
        return $this->table;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function all()
    {
        $table = $this->getTableName();

        $statement = DB::connection()->prepare("SELECT * FROM $table");
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_CLASS, $this->getEntity());
    }

    public function find($id)
    {
        $table = $this->getTableName();
        $primaryKey = $this->getPrimaryKey();

        $statement = DB::connection()->prepare("SELECT * FROM $table WHERE $primaryKey = ?");
        $statement->execute([$id]);

        return $statement->fetchObject($this->getEntity());
    }
}
