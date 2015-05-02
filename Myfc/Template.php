<?php

namespace Myfc;
use Myfc\File;
use Myfc\Template\MyfcTemplate\MyfcTemplateLoader;
use Myfc\Template\MyfcTemplate\MyfcTemplateCompiler;
use Myfc\Template\MyfcTemplate\MyfcTemplateCollector;
use Myfc\Template\MyfcTemplate\MyfcTemplateExtensionManager;
use Myfc\Template\MyfcTemplate\MyfcTemplateConfigManager;
use Myfc\Config;
/**
 * MyfcTemplate Class
 *
 * @author vahitşerif
 */
class MyfcTemplate {
    
    /**
     *
     * @var MyfcTemplateLoader
     */
    public $loader;
    
    /**
     *
     * @var MyfcTemplateCompiler 
     */
    public $compiler;
    
    /**
     *
     * @var MyfcTemplateCollector 
     */
    public $collector;
    
    /**
     *
     * @var MyfcTemplateExtensionManager 
     */
    public $extensionManager;
    
    /**
     *
     * @var MyfcTemplateConfigManager 
     */
    
    public $configsManager;
    
    public function __construct(MyfcTemplateExtensionManager $extensionManager = null) {
        
        $this->configsManager = new MyfcTemplateConfigManager(Config::get('Configs', 'MyfcTemplate'));
        $this->loader =  new MyfcTemplateLoader(File::boot(), $this->configsManager->get('fileExtension'));
        $this->collector = new MyfcTemplateCollector();
        $this->extensionManager = ($extensionManager !== null) ? $extensionManager:new MyfcTemplateExtensionManager();
         $this->compiler = new Compiler('',$this);
      
    }
    
    /**
     * Kullanılacak parametreler atanır
     * @param array $parametres
     * @return mixed
     */
    public function assing($parametres = array()){
        
      
        $this->collector->addCollection($parametres);
        return $this;
        
    }


    public function display($file, $parametres = array(), $return = false){
        
  
         $this->loader->setTemplatePath($this->configsManager->get('templatePath'));
         $content = $this->loader->load($file);
         $this->compiler = new Compiler('',$this);
         $this->compiler->setContent($content);
         $stream = new Stream($this->compiler);
       
        if($content != ''){
            
            $content = $stream->boot();
                        
        }
        
        if($return){
            
            return $return;
            
        }else{
            
            echo $content;
            
        }
        
    }
    
}