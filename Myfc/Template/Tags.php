<?php

namespace Myfc\Template\MyfcTemplate;
use Myfc\Stream;
/**
 * Description of Tags
 *
 * @author vahitşerif
 */
class Tags {
    
   
    private $stream;
    
  
    
    public function __construct($stream) {

        $this->stream = $stream;
        
    }
    
 
    
    public function parse($content, array $lines = []){
        
         
          $stream = $this->stream;
 
          foreach($lines as $key => $line){
              
              $lineContent = $line['content'];

              // if parçalaması bitti
              if(is_integer($this->test($lineContent, 'if'))){
               
                    list($oldContent, $newContent,$lines) = $this->parseTagsStartAndEnd('if',$lines, $line, $key,$stream);
                 
                    $content = $stream->replaceFullContent($oldContent, $newContent,$content);
                    
                   } 
                   elseif(is_integer($this->test($lineContent, 'elseif'))){
                   
                    list($oldContent, $newContent, $lines) = $this->parseTagsStartAndEnd('elseif',$lines, $line, $key,$stream);
                       
                    $content = $stream->replaceFullContent( $oldContent, $newContent, $content);
                    
                   }
                   

                  
              elseif(is_integer($this->test($lineContent, 'for'))){
            
                    list($oldContent, $newContent,$lines) = $this->parseTagsStartAndEnd('for',$lines, $line, $key,$stream);
                   
                    $content = $stream->replaceFullContent($oldContent, $newContent,$content);
                  
              }elseif(is_integer($this->test($lineContent,'while'))){
                  
                  list($oldContent, $newContent,$lines) = $this->parseTagsStartAndEnd('while',$lines, $line, $key,$stream);
                       
                    $content = $stream->replaceFullContent($oldContent, $newContent,$content);
                  
              }elseif(is_integer($this->test($lineContent, 'block'))){
                  
                    list($oldContent, $newContent,$lines) = $this->parseTagsStartAndEnd('block',$lines, $line, $key,$stream);
                       
                    $content = $stream->replaceFullContent($oldContent, $newContent,$content);
                  
              }elseif($this->extensionTags($lineContent,$content,$lines,$line,$key)){
                  
                  
              }
          
              elseif(strstr($lineContent, Stream::STANDART_TAG_OPEN)&& strstr($lineContent, Stream::STANDART_TAG_CLOSE)){
  
                   $find = $stream->parameterFind($lineContent);
                   $k = $stream->compiler->replaceParameter($lineContent, Stream::STANDART_TAG_OPEN,  Stream::STANDART_TAG_CLOSE);
                   $k = $stream->checkForReplace($k);
                   $content = $stream->replaceFullContent($k, $find, $content);
  
              }
                  
              }

        return $content;
        
    }
    private function extensionTags($lineContent,$fullContent,$lines,$line,$key){
        
        
    }

    /**
     * Verilerin uyup uymadığını kontrol eder
     * @param string $content
     * @param mixed $values
     * @return boolean
     */
    
    public function test($lines , $search = ''){
        
        $stream = $this->stream;
        $var = '';
        if(is_array($lines)){
            
         foreach($lines  as $key => $value){
    
         
            if(strstr($value['content'], $stream->patterns[$search])){
                
                $var = $key;
                       
                break;
                
            }
            
        }
            
        }else{
            
             if(strstr($lines, $stream->patterns[$search])){
                    
                $var = 1;
                       
            }
            
        }
          
        return $var;
        
    }
    
    /**
     * 
     * @param string $type
     * @param array $fullLines
     * @param string $line
     * @param integer $key
     * @param Stream $stream
     * @return array
     */
    
    private function parseTagsStartAndEnd($type,$fullLines,$line, $key,$stream){
        
              $end = $this->findEnd($fullLines);
              $getLinesAndContent = $stream->compiler->getSubParseFromParsedLines($key, $end, $fullLines);
              $newLines = $getLinesAndContent['lines'];
              $cleaned = $newLines;
              $newContent = $getLinesAndContent['newcontent'];
              unset($cleaned[$key]);
              unset($cleaned[$end]);  
              $linesContent = $stream->genareteLinesContentArray($fullLines);
              $cleanedArray = $stream->arrayClean($fullLines, $newLines);
              return  array($getLinesAndContent['content'],$stream->compiler->compile($type, $newLines, $newContent,$cleaned, $stream),$cleanedArray);
        
    }
    
    /**
     * Kapanan end tagını bulur
     * @param array $lines
     * @return integer
     */
    
    private function findEnd($lines = []){
        
         return $key = $this->test($lines, 'end');
        
    }
    
    
    
}
