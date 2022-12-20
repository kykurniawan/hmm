<?php

namespace Kykurniawan\Hmm\Database;

use Kykurniawan\Hmm\Helpers\DB;
use Kykurniawan\Hmm\Interfaces\MigrationInterface;
use PDO;

class Migration implements MigrationInterface
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = DB::connection();
    }

    public function query(string $query)
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute();
    }

    public function up()
    {
        // 
    }

    public function down()
    {
        // 
    }
}
