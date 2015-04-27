<?php 

include "vendor/autoload.php";

use Myfc\MyfcTemplate;
use Myfc\Stream;


$template = new MyfcTemplate();
$template->assing(['item' => [
    'keytest' => 'keyvalue'
]
,
        'deneme' => 'test'])
        ->display('index');
