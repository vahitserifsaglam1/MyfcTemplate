<?php

namespace Myfc;
use Myfc\Compiler;
use Exception;
/**
 * Description of Stream
 *
 * @author vahitşerif
 */
class Stream {
    
    private $lines;
    
    private $currentLine;
    
    private $content;
    
    private $compiler;
    
    private $patterns = [
      
        
    ];
    
    const STANDART_TAG_OPEN = '{{';
    const STANDART_TAG_CLOSE = '}}';
    const SPECIAL_TAG_OPEN = '{#';
    const SPECIAL_TAG_CLOSE = '#}';
    const STANDART = 1;
    const SPECIAL = 2;
    const OnlyFor = 'for';
    const ForWithEach = 'foreach';
    
    public function __construct(Compiler $compiler = null) {
        
        $this->compiler = $compiler;
        
        $this->patterns = [
              
        'if' => static::SPECIAL_TAG_OPEN.' if(',
        'elseif' => static::SPECIAL_TAG_OPEN.' elseif(',
        'for' => static::SPECIAL_TAG_OPEN.' for',
        'while' => static::SPECIAL_TAG_OPEN.' while',
        'endif' => 'endif',
        'endfor' => 'endfor',
        'endwhile' => 'endwhile',
            'endline' => '(;)'
        ];
        
        $this->getAll();
        
    }
    
    
    private function getAll(){
      
        $content = $this->content = $this->compiler->getContent();
        $lines = $this->lines = $this->compiler->parseLine()->getTemplateFromParsedLines();

    }
    
    /**
     * 
     * Şuanda bulunan satırı döndürür
     * @return mixed
     */
    
    public function getCurrentLine(){
        
        return ($current = $this->currentLine) ? $current:0;
        
    }


    /**
     * Bir Sonraki satırı döndürür
     * @return string
     */
    
    public function next(){
        
        return ($current =$this->lines[++$this->currentLine]) ? $current:false;
        
    }
    
    /**
     * İç kısmı döndürür
     * @param int $startLine
     * @param int $endLine
     * @return mixed
     */
    
    public function getSubParse($startLine, $endLine){
        
        return $this->compiler->getSubParseFromParsedLines($startLine, $endLine, $this->lines);
        
        
    }
    
    /**
     * Verilerin uyup uymadığını kontrol eder
     * @param string $content
     * @param mixed $values
     * @return boolean
     */
    
    public function test($lines , $search = ''){
        
 
        $var = '';
        
            
        
        if(is_array($lines)){
            
         foreach($lines  as $key => $value){
    
         
            if(strstr($value['content'], $this->patterns[$search])){
                
                $var = $key;
                       
                break;
                
            }
            
        }
            
        }else{
            
             if(strstr($lines, $this->patterns[$search])){
                    
                $var = 1;
                       
               
                
            }
            
        }
        
       

        
        
        return $var;
        
        
    }
    

     /**
      * Bir sonraki aranan satırı getirir
      * @param int $startIndex
      * @param array $searchValues
      * @return mixed
      */
    
      public function getNextSearchValue($startIndex, $searchValues){
          
          $lines = array_slice($this->lines, $startIndex);
          
          foreach($lines as $key => $value){
              
              if($this->test($value['content'], $searchValues))
              {
                  
                 $next = $key;
                  break;
              }
              
          }
          
          
          return $next;
          
      }
      
      
      /**
       * 
       *  Parçalama İşlemi Başlar
       * 
       */
      
      public function boot(){
         
           if(is_array($this->lines) && count($this->lines) > 0 && $this->content !== ''){
           
              $return =  $this->startParseContent($this->content, $this->lines);
               
           }else{
               
               throw new Exception("Yorumlanacak bir satır bulunamadı");
               
           }
         
           return $return;
          
          
      }
      
      public function startParseContent($content = '', array $lines = []) {
     
     
          
 
          foreach($lines as $key => $line){
              
              $lineContent = $line['content'];

              // if parçalaması bitti
              if(is_integer($this->test($lineContent, 'if'))){
               
                    list($oldContent, $newContent,$lines) = $this->ifFind($lines, $line, $key);
                 
                    $content = $this->replaceFullContent($oldContent, $newContent,$content);
                    
                   } 
                   elseif(is_integer($this->test($lineContent, 'elseif'))){
                   
                    list($oldContent, $newContent, $lines) = $this->elseIfFind($lines, $line, $key);
                       
                    $content = $this->replaceFullContent( $oldContent, $newContent, $content);
                    
                   }
                   
            
                  
              elseif(is_integer($this->test($lineContent, 'for'))){
            
                    list($oldContent, $newContent,$lines) = $this->forFind($lines, $line, $key);
                   
                    $content = $this->replaceFullContent($oldContent, $newContent,$content);
                  
              }elseif(is_integer($this->test($lineContent,'while'))){
                  
                  list($oldContent, $newContent,$lines) = $this->whileFind($lines, $line, $key);
                       
                    $content = $this->replaceFullContent($oldContent, $newContent,$content);
                  
              }
          
              elseif(strstr($lineContent, Stream::STANDART_TAG_OPEN)&& strstr($lineContent, Stream::STANDART_TAG_CLOSE)){
  
                   $find = $this->parameterFind($lineContent);
                   $k = $this->compiler->replaceParameter($lineContent, Stream::STANDART_TAG_OPEN,  Stream::STANDART_TAG_CLOSE);
                   $k = $this->checkForReplace($k);
                   $content = $this->replaceFullContent($k, $find, $content);
  
              }
                  
              }
              
          if(strstr($content,$this->patterns['endline'])){
              
             $content = str_replace($this->patterns['endline'], "", $content);
              
          }

                  return $content;
              }
              
              /**
               * Parametreleri parçalar
               * @param type $content
               * @return type
               */
              
          private function parameterFind($content){
              
               
               $cleaned = $this->compiler->replaceParameter($content, self::STANDART_TAG_OPEN, self::STANDART_TAG_CLOSE);
               return $this->compiler->checkParameterOrStringAndGetValue($cleaned);
              
          }
          
          private function checkForReplace($conten){
              
              return "{{ $conten }}";
              
          }


          /**
           * İçeriği değiştitirir
           * @param string $fullContent
           * @param type $oldContent
           * @param type $newContent
           * @return type
           */
          
          private function replaceFullContent($oldContent, $newContent,$content){
            
            return str_replace($oldContent, $newContent, $content);
              
          }
          
          /**
           * 
           * @param type $lines
           * @param type $line
           * @param type $key
           * @return mixed
           */
          private function forFind($lines, $line, $key){
                    
          
              $endFor = $this->findEndFor($lines);
              $getLinesAndContent = $this->compiler->getSubParseFromParsedLines($key, $endFor, $lines);
              $newLines = $getLinesAndContent['lines'];
              $cleaned = $newLines;
              $newContent = $getLinesAndContent['newcontent'];
              unset($cleaned[$key]);
              unset($cleaned[$endFor]);  
              $linesContent = $this->genareteLinesContentArray($lines);
              $cleanedArray = $this->arrayClean($lines, $newLines);
              return  array($getLinesAndContent['content'],$this->compiler->compile('for', $newLines, $newContent,$cleaned, $this),$cleanedArray);
          }
          
          /**
           * Arrayı temizler
           * @param type $lines
           * @param type $newLines
           * @return type
           */
          private function arrayClean($lines, $newLines){
              
              $a =  array_map(function($lineA, $newLinesB){
                  
                  if(!array_search($newLinesB['start'], $lineA)){
                      
                      return $lineA;
                  }
                  
              }, $lines,$newLines);
             
              
              $array = [];
              
              foreach($a as $key => $value){
                  
               
                  if($value !== '' && $value !== null){
                      
                      $array[] = $value;
                      
                  }
                  
              }
              
              return $array;
              
          }


          /**
           * While parçalama işlemini gerçekleştirir
           * @param type $lines
           * @param type $line
           * @param type $key
           */
          private function whileFind($lines, $line, $key){
              
              $endWhile = $this->findEndWhile($lines);
              $getLinesAndContent = $this->compiler->getSubParseFromParsedLines($key, $endWhile, $lines);
              $newLines = $getLinesAndContent['lines'];
              $cleaned = $newLines;
              $newContent = $getLinesAndContent['newcontent'];
              unset($cleaned[$key]);
              unset($cleaned[$endWhile]);  
              $linesContent = $this->genareteLinesContentArray($lines);
                $cleanedArray = $this->arrayClean($lines, $newLines);
              return  array($getLinesAndContent['content'],$this->compiler->compile('while', $newLines, $newContent,$cleaned, $this),$cleanedArray);
              
          }
          
          /**
           * Content içeriğini oluşturur
           * @param array $lines
           * @return array
           */
          
          private function genareteLinesContentArray($lines){
              
              $array = [];
              
              foreach($lines as $key => $value){
                  
                  $array[] = $value['content'];
                  
              }
              
              return $array;
              
          }

                    /**
           * Eğer if e denk gelinirse tetiklenecek ve işlem başlanacak
           * @param type $lines
           * @param type $line
           * @param type $key
           * @return type
           */
          
      private function ifFind($lines, $line , $key){
          
     
           $endIf = $this->findEndIf($lines);
    
        
           $getLinesAndContent = $this->compiler->getSubParseFromParsedLines($key, $endIf, $lines);
    
           $newLines = $getLinesAndContent['lines'];
           $cleaned = $newLines;
           $newContent = $getLinesAndContent['newcontent'];
           unset($cleaned[$key]);
           unset($cleaned[$endIf]);
          $linesContent = $this->genareteLinesContentArray($lines);
            $cleanedArray = $this->arrayClean($lines, $newLines);
           return  array($getLinesAndContent['content'],$this->compiler->compile('if', $newLines, $newContent,$cleaned, $this),$cleaned);
           
         
   
      }
      
      private function elseiIfFind($lines, $line, $key){
          
           $endIf = $this->findEndIf($lines);
           $getLinesAndContent = $this->compiler->getSubParseFromParsedLines($key, $endIf, $lines);
    
           $newLines = $getLinesAndContent['lines'];
           $cleaned = $newLines;
           $newContent = $getLinesAndContent['newcontent'];
           unset($cleaned[$key]);
           unset($cleaned[$endIf]);
             $cleanedArray = $this->arrayClean($lines, $newLines);
           return  array($getLinesAndContent['content'],$this->compiler->compile('if', $newLines, $newContent,$cleaned, $this),$cleanedArray);
           
         
   
          
      }
      
      public function findEndWhile($lines= []){
          
          return $key = $this->test($lines, 'endwhile');
          
      }


      public function findEndFor($lines = []){
          
          return $key = $this->test($lines, 'endfor');
          
      }

            /**
       * 
       * @param array $lines
       * @return integer
       */
      public function findEndIf($lines = []){
          
          
       return   $key =  $this->test($lines, 'endif');
       
          
      }
      /**
       * 
       * @return array
       */
      
      public function getPatterns(){
          
          return $this->patterns;
          
      }
      
      public function getPattern($name){
          
          return (isset($this->patterns[$name])) ? $this->patterns[$name]:false;
          
      }
      
    
}
