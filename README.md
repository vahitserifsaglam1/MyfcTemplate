# MyfcTemplate

MyfcTemplate php tabanlı basit bir template engine sistemidir,

  - Basittir
  - Kullanışlıdır
  - Hızlıdır

MyfcTemplate  [MyfcFramework] için [vahit Şerif Sağlam] tarafından yazılmıştır

### Version
1.0

## Ayarlar 

```sh
  // ayar dosyası app/Configs/Configs.php  dir
    'MyfcTemplate' => [
        
        'templatePath' => 'views/', // görüntü dosyalarının hangi dizinde oldukları
        'fileExtension' => '.myfc.php' // görüntü dosyasının uzantısı
        // örnek index.myfc.php
    ]
```

### Kurulum


```sh
  include "vendor/autoload.php"; // composer autoloader dosyasıdır
```

```sh
$template = new MyfcTemplate();
$template->assing([
        'deneme' => 'test' // kullanılacak değişkenler
       ])
        ->display('index'); // index dosyanın adıdır. configsdeki uzantıyla  
        //birleştirilerek çağrılır yani şuan views/index.myfc.php çağrılır


```

### Eklenti sistemi

```sh
use Myfc\Template\MyfcTemplate\MyfcTemplateExtensionManager;
 // eklentilerimizin toplandığı sınıfı çağırdık
```

```sh
$extension = new ExtensionManager();
$classNameORClassObject = new MyExtension(); 
// or $classNameORClassObject = 'MyExtension';
$extension->addExtension($classNameORClassObject);
$template = new MyfcTemplate($extension); // eklentilerimizi sınıfa atadık
$template->assing([
        'deneme' => 'test' // kullanılacak değişkenler
       ])
        ->display('index'); // index dosyanın adıdır. configsdeki uzantıyla  
        //birleştirilerek çağrılır yani şuan views/index.myfc.php çağrılır


```

```sh

// örnek bir eklenti
use Myfc\Template\MyfcTemplate\Interfaces\MyfcTemplateExtensionInterface;
class MyExtension implements MyfcTemplateExtensionInterface{
   public function getName(){
	   return "YourExtensionName";
   }
   
   public function boot(){
	   // starter function
   }
   
   public function yourFunction($param){
	   // return $param;
   }
	
}
```
### Kullanım

##### basit veri çekimi

```sh
 
 {{ variableName }}
 
```

##### iç içe dizilerde veri çekimi

```sh
 
 {{ variable.alt }}
 <!-- alt değeri dizinin anahtarıdır, ne kadar alt dizi veya objeniz varsa o kadar nokta koyarak yazabilirsiniz -->
```
##### sistem fonksiyonlarıyla veri çekimi

```sh
 
 {{ variableName|upper }}
 
 <!-- upper fonksiyon ismidir, değişebilir, baknızı: Myfc/Template/Extensions/System.php -->
```

##### Sizin eklentilerinizden veri çekme
```sh
 
 {{ variableName|YourExtension.YourFunction }}
 
 <!-- bu kısımlar kendinize göre değişir fonksiyon ismidir, değişebilir, baknızı:  -->
```

#####PHP fonksiyonlarından,sizin fonksiyonlarınızdan veya static fonksiyonlarından çekme

```sh
 
 {{ functionName(parametreler) }} {{ ClassName::functionName(parametreler) }}
 
 <!-- bu kısımlar kendinize göre değişir fonksiyon ismidir, değişebilir,   -->
```

** Döngüler **

#####Foreach
```sh
 
 {# for variableName as key to value #}
 
 <li>{{ key }}</li>{{ value }}
 
 {# end #}
 
 <!-- php karşılığı 
  
    foreach($varialbe as $key => $value )
    
    ->
 
```

```sh
 
 {# for variableName as key #}
 
 <li>{{ key }}</li>
 
 {# end #}
 
 <!-- php karşılığı 
  
    foreach($varialbe as $key )
    
    ->
 
```

##### for 

```sh
 
 {# for variableName in 1..9#} <!-- 1..9 = baslangıç..bitiş !-->
 
 <li>{{ variableName }}</li>
 
 {# end #}
 
 <!-- php karşılığı 
  
    for($variable = 1;$variable = 9; $variable++)
    
    ->
 
```

#####While

 ```sh
 
  {# while variableName #}
  
  <li>variableName.name</li>
  
  {# end #}

```
** Kontrol yapsı **

 #####if
 
 ```sh
 
 {# if(variableName) <!-- if(variable = true) / if(!variable = 1) gibi kontrollerde yapılarbilir !-->
 
 {{ variableName }}
 
 {{ end }}
 
 ```

###Diğer İşlemler


#####block yapısı

Block yapısında block tagları arasında kalan veriyi istediğiniz bir yerde tekrar kullanbilirsiniz

```sh

{# block blockName #}

 içerik 
 
 {# end #}
 
 
 <!-- başla bi yerde yeniden kullanmak için : {{ @block('blockName') }} -->

```


##### başka bir sayfanın içeriğini dahil etmek

```sh
{{ @extend('sayfadı') }} <!-- çağrılacak dosya views/sayfadı.myfc.php -->
```
