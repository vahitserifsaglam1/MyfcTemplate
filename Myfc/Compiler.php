<?php

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
    
    public function __construct($content = '', MyfcTemplate $system ) {
    
        $this->content = $content;
        $this->system = $system;
        
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
        
        $parse = explode("\n", $this->getContent());
        $parse = $this->parseEndOfLine($parse);
        $this->parsedLine = $this->cleanNullsFromParsedLines($parse);
        return $this;
        
    }
    
    private function parseEndOfLine(array $parse = array())
    {
        $array = array();
        
        $parseKey = "(;)";
        foreach($parse as $key){
            
            if(strstr($key,$parseKey)){
                
                $explode = explode($parseKey,$key);
                foreach($explode as $val){
                    
                    $array[] = $val;
                    
                }
                
            }else{
                
                $array[] = $key;
                
            }
            
        }
        
     
        return $array;
        
        
    }

    public function getTemplateFromParsedLines($content = null){
        
        $array = array();
       
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
       return call_user_func_array(array($this,$type."Compiler"), $this->unsetTypeFromArgs(func_get_args()));
        
        
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
    
    
    public function getParameterValue($value){
        
        
         $param = $this->replaceParameter($value, Stream::STANDART_TAG_OPEN,Stream::STANDART_TAG_CLOSE);
         
       
          if(!strstr($param,"(")&&!strstr($param,")")){  
          if(!strstr($param, "|")) $param .= "|get";
           $parsed = explode("|", $param);
          
           list($param , $function) = $parsed;
           if(strstr($param, ".")){
           $param = $this->paramParseObjectGenarator($param);   
           }
           //
         
           $params = array();
              if(strstr($function,"@"))
           {
               
               $params[] = $this->system;
               
           }
        
           if(!strstr($function, ".")){
               
               $function = "System.".$function;
               
           }
           
           $p = explode(".",$function);
           
           list($class , $functionName) = $p;
           
           $class = $this->system->extensionManager->getExtension($class);
           
           $param = $this->system->collector->get($param);
          
           
           if(method_exists($class,$functionName)){
               
               $paramArray = array($param);
               
         
               
               $params = array_merge($paramArray, $params);
               
            
           
               $paramVal = call_user_func_array(array($class,$functionName), $params);
               
           }else{
               
               throw new Exception(sprintf("%s çağrılabilir bir fonksiyon değil",$function));
               
           }
           
          }else{
              
              $paramVal = $this->findedFunctionStartFunctionParsing($param);
              
          }
           
           return $paramVal;
        
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


    
    private function findedFunctionStartFunctionParsing($param){
        
        $replaced = $this->replaceParameter($param,Stream::STANDART_TAG_OPEN,Stream::STANDART_TAG_CLOSE);
       
        $pattern= "/([a-zA-Z]+).*\(([^)]*)\)/";
        // pregmatch
        $parse = preg_match($pattern, $replaced, $matches);
        
        if($parse){
            
            list(, $functionName, $functionParams) = $matches;
            
           
            // function parametresi boş değilse parametreleri parçalıyoruz
            if($functionParams != ''){
              
           
            
                $paramsExplode = explode(",",$functionParams);
                $thi = $this;
                $functionParams = array_filter($paramsExplode, function($a) use($thi){
                   
                    return $thi->checkParameterOrStringAndGetValue($a);
                    
                    
                });
                 
                
            }else{
                
                $functionParams = array();
                
            }
            
            // eğer parametreler bir dizi değilse dizi oluşturuyoruz
            if(!is_array($functionParams)){
                
            $functionParams = array($functionParams);
                
             }
            
             if(function_exists($functionName)){
                 
                     return call_user_func_array($functionName, $functionParams);
                 
             }else{
                 
                 throw new Exception(sprintf("%s adında bir fonksiyon bulunamadı", $functionName));
                 
             }
            
        
            
        }
    }
    
    /**
     * Parçala behçet
     * @param type $param
     * @return type
     */
     private function paramParseObjectGenarator($param){
       
        
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
       
       foreach ($parsed as $key => $value){
           
           if($value != "" || $value !== null){
               
               $array[] = $value;
               
           }
           
       }
       
       return $array;
       
   }
  
   /**
    * Verinin bir düz verimi yoksa parametremi olup olmadığını algılar
    * @param type $a
    * @return type
    */
   public function checkParameterOrStringAndGetValue($a, $check = false){
       
            
            if($cd = $this->getParameterValue($a)){
                
                return $cd;
                
                
            }else{
                
                if($check){
                    
                                    return $this->generateFunctionParameter($a);

                    
                }
                
            }
                    
       
   }
   
   public function generateFunctionParameter($a){
       
       return "'$a'";
       
   }
  
}
