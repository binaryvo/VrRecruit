<?php

class AddMessageTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $tasks = $this->create_table('message', ['id' => false, 'options' => 'Engine=InnoDB']);
        $tasks->column(
            'id',
            'integer',
            [
                'primary_key' => true,
                'auto_increment' => true,
                'null' => false
            ]
        );
        $tasks->column('sid','string', array("limit" => 34));
        $tasks->column('task_id','integer');
        $tasks->column('text','text');
        $tasks->column('recipient_number','string', array("limit" => 13));
        $tasks->column('direction','string');
        $tasks->column('created_at','datetime');
        $tasks->finish();
    }//up()

    public function down()
    {
        $this->drop_table("message");
    }//down()
}
