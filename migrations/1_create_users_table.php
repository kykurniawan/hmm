<?php

use Kykurniawan\Hmm\Database\Migration;

return new class extends Migration
{
    public function up()
    {
        $this->query('create table users (id int not null primary key auto_increment, name varchar(128) not null)');
    }

    public function down()
    {
        $this->query('drop table if exists users');
    }
};
