<?php

class AddTaskStatusTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $tasks = $this->create_table('task_status', ['id' => false, 'options' => 'Engine=InnoDB']);
        $tasks->column(
            'id',
            'integer',
            [
                'primary_key' => true,
                'auto_increment' => true,
                'null' => false
            ]
        );
        $tasks->column('task_id','integer');
        $tasks->column('status_id','integer');
        $tasks->column('created_at','datetime');
        $tasks->finish();
    }//up()

    public function down()
    {
        $this->drop_table("task_status");
    }//down()
}
