<?php
 
 namespace Myfc;
/**
 * Class Config
 *
 *   S�n�flar i�inden configleri �ekmek i�in kullan�lacak s�n�f
 */

  class Config{

      protected $check;

      protected static $configPath;

      protected static $ins;

      protected static $configs = null;

      /**
       *
       *  Ba�lat�c� S�n�f
       *
       *   configs dosyalar�n�n yolunu g�sterir
       */

      public function __construct()
      {

          static::$configPath = 'app/Configs';





      }

      /**
       * @return mixed
       *  Static olarak s�n�f� ba�latmak i�in kullan�l�r
       */

      public static function boot()
      {

          if(!static::$ins)
          {

              static::$ins = new static();

          }

          return static::$ins;

      }

      /**
       * @param $name
       * @param null $config
       * @return bool|mixed
       *  Conif i �ekmek i�in kullan�l�r
       */

      public static  function get($name,$config = null)
      {

          if(!static::$ins) static::boot();
          
          $path = static::$configPath.'/'.$name.'.php';
          
          if( !isset(static::$configs[$name]))
          {
              if(file_exists($path))
              {
                  
                  static::$configs[$name] = include $path;
                  
              }
             
             
          }
          
          if($config !== null)
          {
              
              return static::$configs[$name][$config];
              
          }else{
              
              return static::$configs[$name];
              
          }

           



      }

      /**
       * @param $name
       * @param null $configs
       * @param null $value
       * @return null
       *
       *  Configi ayarlamak i�in kullan�l�r
       */
      public static function set($name,$configs = null,$value = null)
      {

          if(!static::$ins) static::boot();

          if( is_array($configs) )
          {

              static::$configs = $configs;

          }
          elseif(  is_string($configs) && $value && $value !== null )
          {

              static::$configs[$configs] = $value;

          }

          return $value;

      }

  }