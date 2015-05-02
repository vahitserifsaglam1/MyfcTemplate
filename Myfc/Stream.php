<?php

namespace Myfc;
use Myfc\Compiler;
use Exception;
use Myfc\Template\MyfcTemplate\Tags;
/**
 * Description of Stream
 *
 * @author vahitşerif
 */
class Stream {
    
    /**
     * Satırları tutar
     * @var type 
     */
    public $lines;
    

    
    public $currentLine;
    
    /**
     * İçeriği tutar
     * @var type 
     */
    public $content;
    
    public $compiler;
    
    public $patterns = [
      
        
    ];
    
    public $tags;
    
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
        'block' => static::SPECIAL_TAG_OPEN.' block',
        'end' => 'end'
        ];
        
        $this->getAll();
        
        $this->tags = new Tags($this);
        
    }
    
    
    public function getAll(){
      
  
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
     
                   return $this->tags->parse($content,$lines);
                  
              }
              
              /**
               * Parametreleri parçalar
               * @param type $content
               * @return type
               */
              
          public function parameterFind($content){
              
               
               $cleaned = $this->compiler->replaceParameter($content, self::STANDART_TAG_OPEN, self::STANDART_TAG_CLOSE);
               return $this->compiler->checkParameterOrStringAndGetValue($cleaned);
              
          }
          
          public function checkForReplace($conten){
              
              return "{{ $conten }}";
              
          }


          /**
           * İçeriği değiştitirir
           * @param string $fullContent
           * @param type $oldContent
           * @param type $newContent
           * @return type
           */
          
          public function replaceFullContent($oldContent, $newContent,$content){
            
            return str_replace($oldContent, $newContent, $content);
              
          }
          
  
          /**
           * Arrayı temizler
           * @param type $lines
           * @param type $newLines
           * @return type
           */
          public function arrayClean($lines, $newLines){
              
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
           * Content içeriğini oluşturur
           * @param array $lines
           * @return array
           */
          
          public function genareteLinesContentArray($lines){
              
              $array = [];
              
              foreach($lines as $key => $value){
                  
                  $array[] = $value['content'];
                  
              }
              
              return $array;
              
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
