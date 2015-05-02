<?php


namespace Myfc\Template\MyfcTemplate\Extensions;
use Myfc\Support\Str;
use Myfc\Template\MyfcTemplate\Interfaces\MyfcTemplateExtensionInterface;
use Myfc\MyfcTemplate;
/**
 * Description of System
 *
 * @author vahitşerif
 */
class System implements MyfcTemplateExtensionInterface {
    
    public function getname(){
        
        return "System";
        
    }
    
    public function boot(){
        
        //
        
    }
    
    public function get($name){
    
        return $name;
        
    }

        /**
     * Uzunluğu döndürür
     * @param type $param
     * @return type
     */
    public function length($param){
        
        return strlen($param);
        
    }
    
    /**
     * Girilen metni küçültür
     * @param type $value
     * @return type
     */
    public function lower($value){
        
        return mb_convert_case($value, MB_CASE_LOWER,'utf-8');
        
    
    }
    
    /**
     * Girilen metni büyütür
     * @param type $value
     * @return type
     */
    public function upper($value){
        
       return mb_convert_case($value, MB_CASE_UPPER,'utf-8');
        
    }
    
    /**
     * Başharfleri büyütür sadece
     * @param type $value
     * @return type
     */
    public function title($value){
        
        return mb_convert_case($value, MB_CASE_TITLE,'utf-8');
        
    }
    
    public function escape($value){
        
        return filter_var($value, FILTER_SANITIZE_STRING);
        
    }
    
    public function extend($name,  MyfcTemplate $system){
        
        
        $content = $system->loader->load($name);
        return $content;
        
    }
    
    public function block($name, $compiler){
        return $compiler->getBlock($name);
        
    }
    
    
}
