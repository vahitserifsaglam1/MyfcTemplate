<?php 

include "vendor/autoload.php";

use Myfc\MyfcTemplate;


$template = new MyfcTemplate();
$template->assing([
        'deneme' => 'test'
       ])
        ->display('index');

$dizi = [];

