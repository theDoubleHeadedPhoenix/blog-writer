<?php

namespace App\Router;
 
class Route {
    private $path;
    private $callable;
    private $matches = [];
    private $params = [];
    private $rules = [];

    public function __construct($path, $callable, $rules){
        $this->path = trim($path ,'/');
        $this->callable = $callable;
        $this->rules = $rules;
    }
    
    public function with($param, $regex){
        $this->params[$param] = str_replace('(', '(?:', $regex);
        return $this;
    }
    public function match($url){
      
        $url = trim($url, '/');
        $path = preg_replace_callback('#:([\w]+)#',[$this, 'paramMatch'], $this->path);
        $regex = '#^'.$path.'$#';
        //print($regex."</br>");
        //print($path."</br>");
        if(!preg_match($regex, $url, $matches)){
            return false;
        }
        array_shift($matches);
        $this->matches = $matches;
        return true;
    }

    private function paramMatch($match){
        if(isset($this->params[$match[1]])){
            return '('.$this->params[$match[1]].')';
        }
        return '([^/]+)';
    }

    public function call(){        
        if(is_string($this->callable)){
            $params = explode('.', $this->callable);
            $controller = "App\\Controller\\".$params[0]."Controller";
            $controller = new $controller();
            return call_user_func_array([$controller, $params[1]], $this->matches);
            var_dump($params);
            $action = $params[1];
            return $controller->action();
        }else{
            return call_user_func_array($this->callable, $this->matches);            
        }
    }
    public function getUrl($params){
        $path = $this->path;
        foreach($params as $k => $v){
            $path = str_replace(":$k", $v, $path);
        }
        return $path;
    }
    public function getRules(){
        return $this->rules;
    }
}