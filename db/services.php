<?php

$functions = array(
    'get_progress' => array(
        'classname'   => 'ws_progress_external',
        'methodname'  => 'get_progress',
        'classpath'   => 'blocks/completion_progress/ws_progress_external_lib.php',
        'description' => 'Get the user\'s progress for a course',
        'type'        => 'read',
        'capabilities'=> 'report/progress:view',
    )
);
