<?php
/**
 * 
 *   {# for $variablename in 1..9 #}
 * 
 *   {# for $variablename as key #}
 * 
 *   {# for $variablename as key to value #}
 * 
 *   {# while $variablecheck #}
 * 
 * 
 *   {# if(test = var) #}
 * 
 *   {# if(test) #}
 * 
 *   {{ @block('aasd') }} // block çağırma
 * 
 *   {{ $variable }} 
 * 
 *   {{ $variable|lower }}
 * 
 *   {{ $variable|upper }}
 * 
 *   {{ $variable|title }}
 * 
 */


namespace Myfc;
use Myfc\Stream;
use Exception;
use Myfc\MyfcTemplate;
use Myfc\Template\Exceptions\SyntaxException;
/**
 * Description of Compiler
 *
 * @author vahitşerif
 */
class Compiler {
 
    private $content;
    
    private $parsedLine;
    
    public $system;
    
    public $block;
    
    public function __construct($content = '', MyfcTemplate $system ) {
    
        $this->content = $content;
        $this->system = $system;
        
    }
    
    
          /**
           * 
           * @param type $blockName
           * @param type $blockContent
           * @return $this
           */
          
          public function addBlock($blockName, $blockContent){
              
              $this->block[$blockName] = $blockContent;
              return $this;
              
          }
          
          public function getBlock($blockName){
 
              return $this->block[$blockName];
              
          }

    
    /**
     * İçerik ataması yapar
     * @param string $content
     * @return \Myfc\Compiler
     */
    
    public function setContent($content = ''){
 
        $this->content = $content;
        return $this;
        
    }
    
    /**
     * içeriği döndürür
     * @return string
     */
    public function getContent(){
        
        return $this->content;
        
    }
    
    /**
     * Satırlara parçalar
     * @return \Myfc\Compiler
     */
    public function parseLine(){
        
        $parsedLine = explode("\n",$this->getContent());
        $this->parsedLine =  $this->cleanNullsFromParsedLines($this->parseEndLine($parsedLine));
        return $this;
        
    }
    
 
    /**
     * 
     * @param type $content
     * @return array
     */
    public function getTemplateFromParsedLines($content = null){
        
       
        $array = [];
       
        (is_null($content)) ? $content = $this->content:$content;
        
        
        foreach($this->parsedLine as $key => $parsed){
            
            if(strstr($parsed,  Stream::SPECIAL_TAG_OPEN) && strstr($parsed, Stream::SPECIAL_TAG_CLOSE) ||
            strstr($parsed, Stream::STANDART_TAG_OPEN) && strstr($parsed, Stream::STANDART_TAG_CLOSE))    
            {
                
                $baslangic = strpos($this->content, $parsed);
                $lenght=strlen($parsed);
                $array[] = [
                    'start' => $baslangic,
                    'lenght'   => $lenght,
                    'end'  => $baslangic + $lenght,
                    'content' => $parsed,
                    'line' => $key
                ];
                
                $content = str_replace($parsed, "", $content);
                
            }
            
        }
       
        return $array;
        
    }
    
    /**
     * Bir satırda iki tane veya daha fazla veri varsa parçalar
     * @param array $explode
     */
    
    private function parseEndLine(array $explode = [] ){
    
            
            $pattern = "{{\s([a-zA-Z0-9]+)\s}}";
            $array = [];
            foreach($explode as $key => $value){
           
          
            if($ex = explode("}",$value)){
                
                $ex = $this->cleanNullsFromParsedLines($ex);
               
 
               foreach($ex as $v){
                   
                   if(strstr($v,"{{")) $type = Stream::STANDART;
                   elseif(strstr($v,"{#")) $type = Stream::SPECIAL;
                   
                   $v = str_replace("{{", "", $v);
                   $v = str_replace("{#", "", $v);
                   $v = str_replace("#", "", $v);
                   $v = trim($v);
                   
                   switch($type){
                       
                       case Stream::STANDART:
                           
                           $v = Stream::STANDART_TAG_OPEN." $v ".Stream::STANDART_TAG_CLOSE; 
                           
                           break;
                       
                        case Stream::SPECIAL:
                           
                           $v = Stream::SPECIAL_TAG_OPEN." $v ".Stream::SPECIAL_TAG_CLOSE; 
                           
                           break;
               
               
                   }
                   
                   $array[] = $v;

               }
               
            
                
            }else{
                
                $array[] = $value;
                
            }
                
            }

            
    return $array;
        
    }
    

    
    /**
     * if- for gibi parçalama işlemlerinde iç kısımdaki verileri algılamak için kullanılacak
     * @param int $startIndex
     * @param int $endIndex
     * @param array $lines
     * @return type
     */
    public function getSubParseFromParsedLines($startIndex = 0, $endIndex = 0, array $lines = array() ){
            
   
        $start = $lines[$startIndex]['start'];
        $end = $lines[$endIndex]['end'];

       

        $content = $this->getSubParseContent($start,$end);
        return [
                'lines' => $this->getTemplateFromParsedLines($content),
                'content' => $content,
                'newcontent' => $this->getSubParseContent($lines[$startIndex]['end'], $lines[$endIndex]['start'])
                ];
        
    }
    
    /**
     * arada kalan içeriği alır
     * @param type $start
     * @param type $end
     */
    public function getSubParseContent($start, $end){
        
   
      return  substr($this->content, $start, $end-$start);
        
    }
    
    /**
     * 
     *  Parçalamayı yapar ve oluşan veriyi return eder
     * 
     */
    public function compile($type, $lines, $content,$cleaned, Stream $stream){
        
        // otomatik parçalayacak fonksiyona yönlendirir
       return call_user_func_array([$this,$type."Compiler"], $this->unsetTypeFromArgs(func_get_args()));
        
        
    }
    
    private function unsetTypeFromArgs($args){ unset($args[0]); return $args; }
    
    /**
     * For parçalama işlemi yapar
     * @param type $lines
     * @param type $content
     * @param type $cleabed
     * @param Stream $steam
     */
    private function forCompiler($lines, $content, $cleaned, Stream $steam){



        // içeriği barındırır
        $cont = '';
        
        // tip belirlenir
        
        if(strstr($lines[0]['content'],"..")){
            
            $type = Stream::OnlyFor;
            
        }else{
            
            $type = Stream::ForWithEach;
            
        }
        
        // tipe göre parçalama başlar

        
        switch ($type){
            
            case Stream::OnlyFor:
                
            $pattern = "/for\s([a-zA-Z0-9]+)\sin\s([0-9]+)..([0-9]+)/";
                
                $parse = preg_match($pattern, $lines[0]['content'],$finded);
                
                if($parse){
               
           
               
            list(, $variable, $start,$end) = $finded;
            
            if($start>$end){
                
                $checker = ">=";
                $a = "--";
                
            }else{
                
                $checker = "<=";
                $a = "++";
            }
            
            if(strstr($variable,"'")){
                
                throw new SyntaxException("for döngüsünde sayısal işlem kullanım yapacaksanız ' kullanamassınız");
                
            }
            
            if($ch = $this->getParameterValue($start)){
                
                $start = $ch;
                
            }
            
            if($cd = $this->getParameterValue($end)){
                
                $end = $cd;
                
            }
            
            // eval kodu oluşturuldu
            $evalString = '$cont = "";for($'.$variable.'='.$start.';$'.$variable.' '.$checker.' '.$end.';$'.$variable.$a.')'
                    . '{'
                    . '$this->system->collector->addCollection("'.$variable.'",$'.$variable.');'
                    . '$cont .= $steam->startParseContent($content,$cleaned);'
                    . '}'
                    . 'return $cont;';
            
 

            // eval tetiklendi
             $cont = eval($evalString);
             
            //oluşturulan veriler silindi
             $this->system->collector->delete($variable);
                }else{
                    
                    throw new SyntaxException("For döngünüzde bir hata var");
                    
                }
                
                
                break;
            
            case Stream::ForWithEach:
                
               
                
                if(strstr($lines[0]['content'],' to ')){
                    
                    // forparçalaması
                    
                   $pattern = "/for\s([a-zA-Z0-9]+)\sas\s([a-zA-Z0-9]+)\sto\s([a-zA-Z0-9]+)/";
                   
             
                   $parse = preg_match($pattern, $lines[0]['content'], $finded);
                   
                   if($parse){
                       
                       list(, $variable, $keys, $values) = $finded;
                       
                  
                       
                       foreach((array) $this->getParameterValue($variable) as $key => $value){
                           
                           $this->system->collector->addCollection($keys, $key);
                           $this->system->collector->addCollection($values, $value);
                           
                           $cont .= $steam->startParseContent($content, $cleaned);
                           
                       }
                       
                       $this->system->collector->delete($keys);
                       $this->system->collector->delete($value);
                       
                   }
                   
                   // forparçalaması bitti
                    
                }else{
                    
                    // foreach parçalaması
                    
                     $pattern = "/for\s([a-zA-Z0-9]+)\sas\s([a-zA-Z0-9]+)/";
                     
                     $parse = preg_match($pattern, $lines[0]['content'], $finded);
                     
           
                   
                   if($parse){
                      
                    
                       
                       list(, $variable, $keys) = $finded;
                      
            
                       
                       $array = (array) $this->checkParameterOrStringAndGetValue($variable);
           
                       foreach($array as $key){
                         
                           $this->system->collector->addCollection($keys, $key);
                           $cont .= $steam->startParseContent($content, $cleaned);
                           
                       }
                    
                       $this->system->collector->delete($keys);
                       
                   }
                     
                     // foreach parçalaması bitti
                    
                }
                
                
                
            
        }
      
        return $cont;
        
    }
    /**
     * 
     * @param type $lines
     * @param type $content
     * @param type $cleaned
     * @param Stream $stream
     * @return type
     */
    public function blockCompiler($lines,$content,$cleaned,  Stream $stream){
       
            $blockName = $this->replaceParameter($lines[0]['content'], Stream::SPECIAL_TAG_OPEN." block", Stream::SPECIAL_TAG_CLOSE);
            $blockContent =  $stream->startParseContent($content,$cleaned);
            $this->addBlock($blockName, $blockContent);
            return $blockContent;
    }


    /**
     * While parçalaması yapılır
     * @param type $lines
     * @param type $content
     * @param type $cleaned
     * @param Stream $stream
     */
    
    public function whileCompiler($lines, $content, $cleaned, Stream $stream){
        
         $cont = '';
        
         $parseContent = $this->replaceParameter($lines[0]['content'],Stream::SPECIAL_TAG_OPEN,Stream::SPECIAL_TAG_CLOSE);
            
         $pattern = "/while\s([a-zA-Z0-9]+)/";
         
         $parse = preg_match($pattern, $lines[0]['content'],$finded);
         
         if($parse){
             
             list(, $parseContent) = $finded;
             
         }else{
             
             throw new SyntaxException("While yapıya uygun değildir");
             
         }
         
         $variable = $parseContent;

            if($cd = $this->checkParameterOrStringAndGetValue($parseContent,true)){
                
             
                $value = $cd;
                
            }
            
      
       
                
           while($value){
               
               $this->system->collector->addCollection($variable, $cd);
               $cont .= $stream->startParseContent($content, $cleaned);
               
           }
            
         
           
           return $cont;
        
    }
    
    /**
     * İf parçalanması yapılır
     * @param type $type
     * @param type $lines
     * @param type $content
     * @param Stream $stream
     */
    public function ifCompiler($lines, $content, $cleaned,Stream $stream){
        
        
        $if = $lines[0];
        
        $parse = preg_match("/if\(([^)]+)\)/", $lines[0]['content'], $finded);
        
        $cont = $finded[1];
        
        if($parse){
            
            if(strstr($cont, "=")){
                
                list($cont, $checker) = explode("=", $cont);
             
                $parser = "=";
                
            }else{
                
                $checker = true;
                $parser = "=";
            }
            
           if(strstr($cont, "!")){
              
              $parser = "!";
              
            }    
                $contas = $cont;
                $checkeras = $checker;
            // eğer tanımlıysa değeri çek eğer değilse direk ata
            
              if($checker != "true" || $checker != "false") {
                
            $checker = $this->checkParameterOrStringAndGetValue($checker);
                      
                  }
            
                 
            
              $cont = $this->checkParameterOrStringAndGetValue($cont);
             
            $evalString = '$co = "";'
                    . 'if($cont ='.$parser.' $checker){'
                    . 'return true;'
                    . '}';
    
            
           
              $check = eval($evalString);
                    
                  
            // eğer işlem true döndürüyorsa
            if($check){
                
               
               return $stream->startParseContent($content, $cleaned);
                
            }
            
            
        }else{
            
            throw new SyntaxException("uygun bir if tanımı bulunamadı. Satır->". $lines[0]['line']);
            
        }
         
        #$content = $stream->startParseContent($content, $lines);
        
        
    }
    
    /**
     * 
     * @param string $cleaned
     * @return string
     */
    
    private function checkIfSystemCompilerFunction($cleaned){
        
        
        return (strstr($cleaned,"@")) ? str_replace("@","",$cleaned):null;
        
    }
    
    /**
     * 
     * @param array $params
     * @return array
     */
    
    private function genareteFunctionParams($params){
       
        
        $params = explode(",",$params);
       
        $array = [];
        
        foreach($params as $key){
            
            $array[] = $this->getParameterValue($key);
            
        }
        
        return $array;
        
        
    }

    
    /**
     * 
     * @param array $matches
     * @return array
     */

    private function genareteFunctionNameAndParams($matches = []){
        
            list(, $name,$params) = $matches;
            
            return [
                'name' => $name,
                'params' => $this->genareteFunctionParams($params),
            ];
        
    }
    
    /**
     * 
     * @param string $param
     * @return array
     */
    
    private function findFunctionParams($param = ''){
        
            $pattern= "/([a-zA-Z]+).*\(([^)]*)\)/";
             // pregmatch
             $parse = preg_match($pattern, $param, $matches);
                     
             return $matches;
        
    }

    /**
     * Parametrelerin değerlerini alır
     * @param string $value
     * @param Stream $stream
     * @return string
     */
        public function getParameterValue($value,$stream = null){
        
         $param = $this->replaceParameter($value, Stream::STANDART_TAG_OPEN,Stream::STANDART_TAG_CLOSE);
         
         if($check = $this->checkIfSystemCompilerFunction($param)){
            
             $extension = $this->system->extensionManager->getExtension('System');
         
             $param = $check;
             
             $parse = $this->genareteFunctionNameAndParams($this->findFunctionParams($param));
            
           
             $function = $parse['name'];
             $params = $parse['params'];
             $params[] = $this->system;
            
            return call_user_func_array([$extension,$function],$params);

         }
         if(strstr($param,"(") && strstr($param,")")){
             
             $parse = $this->genareteFunctionNameAndParams($this->findFunctionParams($param));
            
             $function = $parse['name'];
             $params = $parse['params'];
            
             return call_user_func_array($function, $params);
          
             
         }else{
                   
      
                 
                   // kontrolu  
                   if(!strstr($param,"|")){
                       
                      $param .= "|System.get";
                       
                   }
               
               
                   
                   list($parameter, $extensionNameAndFunction) = explode("|",$param);
                   
                    $parameter = $this->paramStringOrParamNameChecker($parameter);
                   
                    $params[] = $parameter;
                    
        
                    
                    list($extension, $function) = $this->getExtensionNameAndFunction($extensionNameAndFunction);

             }
             
         
    
        
         
         $extension = $this->system->extensionManager->getExtension($extension);
            
         return call_user_func_array([$extension,$function],$params);
        
    }
    
    /**
     * Eklenti adını ve çağrılacak fonksiyonu döndürür
     * @param string $extensionName
     * @return array
     */
    
    private function getExtensionNameAndFunction($extensionName){
        
             if(!strstr($extensionName,".")){
            
               $extensionName = "System.$extensionName";
            
             }
        
            if($ex = explode(".", $extensionName)){
                
       
                
                if(count($ex)>2){
                    
                    $ex = array_slice($ex, 0, 2);
                    
                }
                
                $return = [$ex[0],$ex[1]];
                
                
            }
            
            return $return;
        
    }


    /**
     * Parametrenin bir string değer olup olmadığını kontrol eder
     * @param string $param
     * @return type
     */
    private function paramStringOrParamNameChecker($param){
        
        $val = $param;
        
        if(strstr($param,"'") || strstr($param,'"')){
                     
                     $param  = str_replace("'","",$param);
                     $param = str_replace('"', "", $param);
                     
                     $val = $param;
                     
                    
                     
                 }else{
                     
                     
                     if($param = $this->paramParseObjectGenarator($param)){
                         
                         $val = $param;
                         
                     }
                     
                 }
                 
               
                return $val;
        
    }


    public function replaceParameter($value,$tagOpen,$tagClose){
        
         
        
        $pattern = "$tagOpen\s(.*?)\s$tagClose";
       
        $parse = preg_match($pattern, $value, $finded);
        
        if($parse){
            
            return $finded[1];
            
        }else{
            
            return $value;
            
        }
        
      
    }

    
    /**
     * Parçala behçet
     * @param type $param
     * @return type
     */
     private function paramParseObjectGenarator($param){
       
        if(strstr($param,".")){
            
        $ex = explode(".", $param);
        $first = $ex[0];
        unset($ex[0]);
        $s = implode("->",$ex);

        $value = $this->system->collector->get($first);
        if(is_object($value)){
          
           $value = eval('return $value->'.$s.';');
           return $value;
            
        }else{
            
            return $first;
            
        }
        
            
        }else{
    
            return $this->system->collector->get($param);
            
        }

   }
   
   /**
    * Yeni bir checker instance i oluşturur
    * @param string $param
    * @return mixed
    */
   private function newCheckerClass($param) {
       
       $checkerClassName=  "Myfc\Template\Checker\\$param".'Checker';
       return new $checkerClassName;
       
   }
   
   /**
    * Yeni bir compiler oluşturur
    * @param type $param
    * @return \Myfc\compiler
    */
   private function genareteNewCompiler($param) {
       
       $compiler =  "Myfc\Template\Compiler\\$param".'Compiler';
       return new $compiler;
       
   }
   
   private function cleanNullsFromParsedLines(array $parsed = []){
       
 
       $array = [];
       $a = [];
       $array = array_filter($parsed, function($a){
           
           if($a !== "" || $a !== null){
               
               return $a;
               
           }
           
       });
       
       foreach ($array as $key => $value){

            $a[] = $value;
           
           
       }
       
       return $a;
       
   }
  
   /**
    * Verinin bir düz verimi yoksa parametremi olup olmadığını algılar
    * @param type $a
    * @return type
    */
   public function checkParameterOrStringAndGetValue($a, $check = false){
       
    
    
           
          
           
            if($cd = $this->getParameterValue($a)){
                
                
                $return = $cd;
                
                
            }else{
                
                if($check){
                    
                 $return =  $this->generateFunctionParameter($a);

                }else{
                    
                    $return = $a;
                    
                }
                
            }
          
            return $return;

   }
   
   public function generateFunctionParameter($a){
       
       return "'$a'";
       
   }
  
}
