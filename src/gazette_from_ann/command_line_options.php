<?php

class CommandLineOptions {
    
    var $options=[];
    
    var $types=["STRING","INTEGER","BOOLEAN"];
    
    public function defineStringValue($name,$description){
        $this->defineOption($name,"STRING",true,false,false,null,$description);
    }

    public function defineMultiStringValue($name,$description){
        $this->defineOption($name,"STRING",true,true,false,null,$description);
    }
    
    public function defineOption($name,$type,$hasValue,$allowMultiple,$optional,$default,$description){
        $found=false;
        foreach($this->types as $t)
          if(strcasecmp($type,$t)===0){$found=true;break;}
          
        if(!$found){
            die("Error: defineOption invalid type [${type}]\n");
        }
    
        $this->options[]=[
          "name"=>$name,
          "type"=>$type,
          "hasValue"=>$hasValue,
          "allowMultiple"=>$allowMultiple,
          "optional"=>$optional,
          "default"=>$default,
          "validation"=>[],
          "description"=>$description
        ];
    }
    
    public function addValidation($name,$type,$data){
        $found=false;
        foreach($this->options as &$o){
            if(strcasecmp($o['name'],$name)===0){
                $found=true;
                $o['validation'][]=["type"=>$type,"data"=>$data];
                break;
            }
        }
        
        if(!$found)$this->help_die("addValidation to unknown option [$name]");
    }
    
    public function validateValue($name){
        $found=false;
        foreach($this->options as &$o){
            if(strcasecmp($o['name'],$name)===0){
                $found=true;
                foreach($o['validation'] as $v){
                    if(strcasecmp($v['type'],"ENUM")===0){
                        $f=false;
                        foreach($v['data'] as $d){
                            if(strcasecmp($d,$o['value'])===0){$f=true;break;}
                        }
                        if($f===false)$this->help_die("Invalid value for option [$name]");
                    }
                }
                break;
            }
        }
        
        if(!$found)$this->help_die("validate unknown option [$name]");
        
        return true;
    }
    
    public function help_die($error){
        global $argv;
        
        echo "Error: $error\n\n";
        
        echo "Syntax:\n";
        echo "${argv[0]}";
        foreach($this->options as $opt){
            echo " ";
            if($opt['optional'])echo "[";
            echo $opt['name'];
            if($opt['hasValue'])echo ":VALUE";
            if($opt['optional'])echo "]";
        }
        
        echo "\n\nOptions:\n";
        
        foreach($this->options as $opt){
            echo "   ${opt['name']} = ${opt['type']} ${opt['description']}";
            if($opt['optional'])echo " (default:${opt['default']})";
            echo "\n";
        }

        echo "\n";        
        die();
    }
    
    public function parseValue($type,$value){
        if(strcasecmp($type,"STRING")===0){
            return "".$value;
        }
        
        if(strcasecmp($type,"INTEGER")===0){
            $v=intval($value);
            if(strcasecmp($v,$value)!==0)$this->help_die("Invalid INTEGER value [${value}]");
            return $v;
        }
        
        if(strcasecmp($type,"BOOLEAN")===0){
            if(strcasecmp($value,"true")===0 || strcasecmp($value,"yes")===0 || strcasecmp($value,"y")===0)return true;
            if(strcasecmp($value,"false")===0 || strcasecmp($value,"no")===0 || strcasecmp($value,"n")===0)return false;
            $this->help_die("Invalid BOOLEAN value [${value}]");
        }
        
        $this->help_die("Undefined type [$type] for value [${value}]");
    }
    
    public function parse(){
      global $argv;
    
      foreach($this->options as &$o){
          $o['value']=false;
      }
      
      foreach($argv as $k=>$a){
          if($k==0)continue;
          $a=ltrim($a,"-");
          $found=false;
          foreach($this->options as &$o){
              if(!$o['hasValue']){
                  if(strcasecmp($a,$o['name'])==0){
                      $o['value']=true;
                      $this->validateValue($o['name']);
                      $found=true;
                      break;
                  }
              }else{
                  if(startsWith($a,"${o['name']}:")){
                      if($o['allowMultiple']===false){
                          if($o['value']!==false)
                              $this->help_die("Option [".$o['name']."] appears multiple times but single value is expected");
                          $o['value']=$this->parseValue($o['type'],substr($a,strlen($o['name'])+1));
                      }else{
                          if($o['value']===false)$o['value']=[];
                          $o['value'][]=$this->parseValue($o['type'],substr($a,strlen($o['name'])+1));
                      }
                      
                      $this->validateValue($o['name']);
                      $found=true;
                      break;
                  }
              }
          }
          
          if(!$found){
              $this->help_die("Invalid option [$a]");
          }
      }
      
      foreach($this->options as &$o){
          if($o['value']===false){
              if(!$o['optional'])$this->help_die("Missing parameter [${o['name']}]");
              $o['value']=$o['default'];
          }
      }
          
    }
    
    public function getOption($name){
        foreach($this->options as $o)
            if(strcasecmp($o['name'],$name)===0)return $o['value'];
        $this->help_die("Undefined option [$name]");
    } 
}


?>