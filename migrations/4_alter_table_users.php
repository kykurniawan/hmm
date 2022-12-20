<?php

use Kykurniawan\Hmm\Database\Migration;

return new class extends Migration
{
    public function up()
    {
        $this->query('alter table users add email varchar(128) not null');
    }

    public function down()
    {
        $this->query('alter table users drop column email');
    }
};
