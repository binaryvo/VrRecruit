<?php

class AddStatusTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $tasks = $this->create_table('status', ['id' => false, 'options' => 'Engine=InnoDB']);
        $tasks->column(
            'id',
            'integer',
            [
                'primary_key' => true,
                'auto_increment' => true,
                'null' => false
            ]
        );
        $tasks->column('name','text');
        $tasks->column('order','integer');
        $tasks->finish();
    }//up()

    public function down()
    {
        $this->drop_table("status");
    }//down()
}
