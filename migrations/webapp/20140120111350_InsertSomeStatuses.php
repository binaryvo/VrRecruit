<?php

$cliIndex = implode(DIRECTORY_SEPARATOR, ['vreasy', 'application', 'cli', 'cliindex.php']);
require_once($cliIndex);

use Vreasy\Models\Status;

class InsertSomeStatuses extends Ruckusing_Migration_Base
{
    public function up()
    {
        $s = Status::instanceWith([
            'name' => 'pending',
            'order' => 0
        ]);
        $s->save();
        
        $s = Status::instanceWith([
            'name' => 'accepted',
            'order' => 1
        ]);
        $s->save();
        
        $s = Status::instanceWith([
            'name' => 'refused',
            'order' => 2
        ]);
        $s->save();
        
    }//up()

    public function down()
    {
    }//down()
}
