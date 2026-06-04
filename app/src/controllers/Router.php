<?php
namespace Controller;
class Router{
    private $views = array();
    private $view = null;
    public function __construct(){
        $relativePath = SRC_PATH . "/views/";
        $views = array_diff(scandir($relativePath."/"), array(".", ".."));
        foreach($views as $view){
            $viewName = explode(".", $view)[0];
            $this->views[$viewName] = $relativePath . $view;
        }
    }

    public function view($view){
        $this->view = $this->views[$view];
        return $this->views[$view];
    }

    public function getView(){
        return $this->view;
    }
}
?>