<?php

class WebBase extends FrontBase {

    private $api = null;

    private function fetchApi($uri) {
        $arr = explode("/", $uri);
        $arr = array_diff($arr, array(null));
        if (count($arr) < 2) {
            return $this->feedback(false, "没有方法名");
        }
        $uri_low_case = strtolower($uri);
        $class = $arr[count($arr) - 2];
        $method = $arr[count($arr) - 1];
        //$package = str_replace("/" . $arr[count($arr) - 1], "", $uri);
        $package=$arr['0'].'/'.$arr['1'];
        
        $file = strtolower(APP . "/classes/c/${package}.php");
        if (!file_exists($file)) {
            return $this->feedback(false, "接口路径不存在");
        }
//        $classpath = str_replace("/", "\\", $uri);
//        $classpath = str_replace("\\${method}", "", $classpath);
        $classpath=str_replace("/", "\\", $package);
        $data = array(
            "api" => $class,
            "method" => $method,
            "file" => $file,
            "exists" => false,
            "className" => $classpath
        );
        if (class_exists($class)) {
            $data['exists'] = true;
        }
        return $this->feedback(true, null, $data);
    }

    protected function call($uri, $param=null) {
        if (empty($uri)) {
            return $this->feedback(false, "没告诉我URI");
        }
        $apiConfig = $this->fetchApi($uri);
        if ($apiConfig->success == false) {
            return $this->feedback(false, $apiConfig->msg);
        }

        $api = null;
        $class = $apiConfig->className;
        $method = $apiConfig->method;
        if (!empty($this->api["${class}"])) {
            //echo "11111<br/>";
            $api = &$this->api["${class}"];
        } else {
            if ($apiConfig->exists) {
                  //echo "22222<br/>";
                $api = new $class();
            } else {
                  //echo "33333<br/>";
                require_once($apiConfig->file);
                $api = new $class();
            }
            $this->api["${class}"] = &$api;
        }
        if (empty($api)) {
            return false;
        }
        if (method_exists($api, $method)) {
            return $api->$method($param);
        }
        return $this->feedback(false, "接口不存在");
    }

    /**
     * 分页
     * 
     * @param int $total  总页数
     * @param int $page   当前页
     * @return string 
     */
    protected function page($total, $page) {
        $url = $this->_set_url();
        $html = '<div id="page"><div class="pagination pagination-centered"><ul><li ' . ($page == 1 ? 'class="disabled"' : '') . '><a href="' . ($page == 1 ? '#javascript:void(0);' : $url . 'p=' . ($page > 2 ? $page - 1 : $page)) . '">上一页</a></li>';
        if ($total <= 10) {
            for ($i = 1; $i <= $total; $i++) {
                $html .='<li ' . ($i == $page ? 'class="active"' : '') . '><a href="' . ($i == $page ? '#javascript:void(0);' : $url . 'p=' . $i) . '">' . $i . '</a></li>';
            }
        } else {
            for ($i = 1; $i <= $total; $i++) {
                if ($i == $page) {
                    $html .='<li ' . ($i == $page ? 'class="active"' : '') . '><a href="' . ($i == $page ? '#javascript:void(0);' : $url . 'p=' . $i) . '">' . $i . '</a></li>';
                } else {
                    if ($page - $i >= 4 && $i != 1) {
                        $html.='<li><a href="#javascript:void(0);"> ...... </a></li>';
                        $i = $page - 3;
                    } else {
                        if ($i >= $page + 5 && $i != $total) {
                            $html.='<li><a href="#javascript:void(0);"> ...... </a></li>';
                            $i = $total;
                        }
                        $html .='<li ' . ($i == $page ? 'class="active"' : '') . '><a href="' . ($i == $page ? '#javascript:void(0);' : $url . 'p=' . $i) . '">' . $i . '</a></li>';
                    }
                }
            }
        }
        $html .='<li ' . ($page == $total ? 'class="disabled"' : '') . '><a href="' . ($page == $total ? '#javascript:void(0);' : $url . 'p=' . ($page < $total ? $page + 1 : $page)) . '">下一页</a></li></ul></div></div>';
        return $html;
    }
    
    /**
     * 分页-seo
     * 
     * @param int $total  总页数
     * @param int $page   当前页
     * @return string 
     */
    protected function page1($total, $page) {
        $url = $this->_set_url();
        
        $arr = explode("/",$url);
        unset($arr[count($arr)-1]);
        $url = implode("/",$arr);
        
        $html = '<div id="page"><div class="pagination pagination-centered"><ul><li ' . ($page == 1 ? 'class="disabled"' : '') . '><a href="' . ($page == 1 ? '#javascript:void(0);' : ($url . '/' . ($page >=2 ? $page - 1 : $page)) .'.html'). '">上一页</a></li>';
        if ($total <= 10) {
            for ($i = 1; $i <= $total; $i++) {
                $html .='<li ' . ($i == $page ? 'class="active"' : '') . '><a href="' . ($i == $page ? '#javascript:void(0);' :( $url . '/' . $i) .'.html'). '">' . $i . '</a></li>';
            }
        } else {
            for ($i = 1; $i <= $total; $i++) {
                if ($i == $page) {
                    $html .='<li ' . ($i == $page ? 'class="active"' : '') . '><a href="' . ($i == $page ? '#javascript:void(0);' :( $url . '/' . $i) . '.html').'">' . $i . '</a></li>';
                } else {
                    if ($page - $i >= 4 && $i != 1) {
                        $html.='<li><a href="#javascript:void(0);"> ...... </a></li>';
                        $i = $page - 3;
                    } else {
                        if ($i >= $page + 5 && $i != $total) {
                            $html.='<li><a href="#javascript:void(0);"> ...... </a></li>';
                            $i = $total;
                        }
                        $html .='<li ' . ($i == $page ? 'class="active"' : '') . '><a href="' . ($i == $page ? '#javascript:void(0);' :( $url . '/' . $i) .'.html'). '">' . $i . '</a></li>';
                    }
                }
            }
        }
        $html .='<li ' . ($page == $total ? 'class="disabled"' : '') . '><a href="' . ($page == $total ? '#javascript:void(0);' :( $url . '/' . ($page < $total ? $page + 1 : $page)) .'.html'). '">下一页</a></li></ul></div></div>';
        return $html;
    }

    /**
     * 设置当前页面链接
     */
    protected function _set_url() {
        $url = $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') ? '' : "?");
        $parse = parse_url($url);
        if (isset($parse['query'])) {
            parse_str($parse['query'], $params);
            //unset($params[$this->page_name]);
            if(isset($params['p'])){
                unset($params['p']);
            }
            $url = $parse['path'] . '?' . http_build_query($params);
        }
        if (!empty($params)) {
            $url .= '&';
        }
        return $url;
    }

    /**
     * 包含页面 如公共页面 footer、header ......
     * 
     * @param string $view 包含的页面（只能包含页面）
     * @param array $param 页面需要传入的变量
     * @return boolean 
     */
    protected function include_page($view, $param = null) {
        if (!$view) {
            return false;
        }
        if (strpos($view, '/')) {
            $page = VIEW . "/" . "${view}.php";
        } else {
            $class = strtolower(get_class($this));
            $page = VIEW . "/" . $class . "/" . "${view}.php";
        }
        if (file_exists($page)) {
            if (!empty($param) && is_array($param)) {
                extract($param);
                unset($flag);
            }
            require(strtolower($page));
        } else {
            die("This Page Is Not Found");
        }
    }

}

?>