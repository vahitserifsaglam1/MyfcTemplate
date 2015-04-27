<?php

 namespace Myfc\Adapter;

 /**
  * Interface AdapterInterface
  * @package Adapter
  */

 interface AdapterInterface
 {

     /**
      * @return mixed
      *
      *  S�n�f�n g�r�necek ve �a�r�lacak ad�
      */

      public function getName();

     /**
      * @return mixed
      *  Adaptere eklenen s�n�flar�n ba�lat�lmas�n� sa�lar
      */
      public function boot();

 }