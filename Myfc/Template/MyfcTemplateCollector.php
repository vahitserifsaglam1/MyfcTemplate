<?php



namespace Myfc\Template\MyfcTemplate;
use stdClass;
/**
 * Description of MyfcTemplateCollector
 *
 * @author vahitşerif
 */
class MyfcTemplateCollector {
   
    private $collection = array();
    
    public function __construct() {
        
    }
  
    /**
     * Diziyi objeye döndürür
     * @param array $arr
     * @return stdClass
     */
 
    private function convertToObject(array $arr =[] ){
        
        $object = new stdClass();
        
        foreach($arr as $key => $value){
            
            if(is_array($value)){
                
                $value = $this->convertToObject($value);
                
            }
            
            $object->$key = $value;
            
        }
        
        return $object;
        
        
    }

        
    /**
     * Sınıfa collection eklemesi yapar
     * @param mixed $params
     * @param mixed $vall
     * @return \Myfc\Template\MyfcTemplate\MyfcTemplateCollector
     */
    public function addCollection($params, $vall = null){
        
        if(is_null($vall) && is_array($params)){
            
            $array = [];
            
            foreach($params as $key => $value){
               
          
                  if(is_array($value)){
                      
                      $array[$key] = $this->convertToObject($value);
                      
                  }else{
                      
                      $array[$key] = $value;
                      
                  }
                   
                
            }
  
            
            $this->collection = array_merge($this->collection, $array);
            
        }elseif(!is_null($vall) && !is_array($params)){
            
           $this->collection[$params] = $vall;
            
        }
        
        return $this;
        
    }
    
    /**
     * Collectionları döndürür
     * @return array
     */
    public function getCollections(){
        
        return $this->collection;
        
    }
    
    /**
     * 
     * @param string $name
     * @return mixed
     */
    
    public function get($name = ''){
        
        
        if(isset($this->collection[$name])){
            
             return $this->collection[$name];
            
        }
        
    }
    
    /**
     * 
     * @param string $name
     */
    public function delete($name){
        
        if(isset($this->collection[$name])){
            
            unset($this->collection[$name]);
            
        }
        
    }


    public function flush(){
        
        foreach($this->collection as $key => $value){
            
            unset($this->collection[$key]);
            
        }
        
    }
    
}
