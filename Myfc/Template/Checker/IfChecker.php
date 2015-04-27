<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Myfc\Template\Checker;

/**
 * Description of IfChecker
 *
 * @author vahitşerif
 */
class IfChecker {
    
    public function checkWithParser($parameter, $parser, $checker){
        
        
        

        
   
        
        if(is_string($parameter)) $parameter = "'$parameter'";
      
       return eval("if($parameter $parser= $checker) return true;");
        
    }
    
    public function checkWithOutParser($parameters){
        
        if($parameters){
            
            return true;
            
        }
        
    }
}
