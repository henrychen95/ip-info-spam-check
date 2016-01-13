<?php
define('APPPATH', getcwd());
define('SMARTY_DIR', 'smarty/libs/');
require_once(SMARTY_DIR.'Smarty.class.php');

class Core extends Smarty
{
    function __construct()
    {
        parent::__construct();

        $this->template_dir = APPPATH. '/views/';
        $this->compile_dir  = APPPATH. '/cache/';
        $this->cache_dir    = APPPATH. '/cache/smarty/cached';

        $this->left_delimiter = "<{";
        $this->right_delimiter = "}>";
    }
}