<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

/**
 * Enables useage of the Twig http://twig.sensiolabs.org/ templating library
 * 
 * This library functions as a complete replacement for CodeIgniter's Output library
 *
 * @author andre bluehs <hello@andrebluehs.net>
 */

class Twig
{
    private $CI;
    private $_twig;
    private $_template_dir;
    private $_cache_dir;
    private $_data = array();
    private $_headers = array();
    private $_helpers = array();
    private $_template_filetype = '.html.twig';
    private $_helpers_added = false;
    
    function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('twig');
        
        // let Twig do it's thing
        require_once BASEPATH . "../vendor/twig/twig/lib/Twig/Autoloader.php";
        Twig_Autoloader::register();
        log_message('debug', "Twig Autoloader Loaded");
        
        // set up Twig Loader    
        $this->_template_dir = $this->CI->config->item('template_dir');
        $this->_cache_dir = $this->CI->config->item('cache_dir');
        $loader = new Twig_Loader_Filesystem($this->_template_dir, $this->_cache_dir);

        // set up Twig Environment
        $this->_twig = new Twig_Environment($loader);
        
        // globals available to all templates
        $this->_twig->addGlobal('env', ENVIRONMENT);
        
        if ($this->CI->config->item('autoload_csrf')) {
            $this->_twig->addGlobal('csrf_name', $this->CI->security->get_csrf_token_name());
            $this->_twig->addGlobal('csrf_token', $this->CI->security->get_csrf_hash());
        }
        
        // set up autoloaded funcs
        $ah = $this->CI->config->item('autoloaded_helpers');
        if (!empty($ah)) {
            $this->_helpers = $ah;
        }
        
        $tf = $this->CI->config->item('template_filetype');
        if (!empty($tf)) {
            $this->_template_filetype = $tf;
        }
    }
    
    /**
     * Make a helper available to the template
     *
     * @param string $file - name of helper file
     * @param string $func - name of function contained in $file
     */
    public function add_helper($file, $func) {
        $this->_helpers[] = array($file, $func);
    }
    
    /**
     * Attach helper functions to template
     * Only called right before output is rendered
     */
    private function add_helpers() {
        if ($this->_helpers_added) {
            return;
        }
        
        $this->_helpers_added = true;
        $loaded = array();
        foreach ($this->_helpers as $func_arr) {
            list($file, $func) = $func_arr;
            // make sure the helper file is loaded
            if (!isset($loaded[$file])) {
                $this->CI->load->helper($file);
                $loaded[$file] = array();
            }
            
            // make sure we haven't already loaded this function
            if (!in_array($func, $loaded[$file])) {
                $f = new Twig_SimpleFunction($func, $func, array('is_safe' => array('html')));
                $this->_twig->addFunction($f);
                $loaded[$file][] = $func;
            }
        }
    }
    
    /**
     * Set a value to be given to the template
     * if you really don't want to give it to the rendering function
     *
     * @param string $key - name of variable made available to the template
     * @param mixed $value
     */
    public function data($key, $value) {
        $this->_data[$key] = $value;
    }
    
    /**
     * Add a header to be sent to the browser
     * does not actually send anything when called
     *
     * @see output_headers
     * @param string $header
     * @param boolean $replace
     */
    public function add_header($header, $replace = true) {
        $this->_headers[] = array($header, $replace);
    }
    
    /**
     * Send headers to the browser
     */
    public function output_headers() {
        // Are there any server headers to send?
        if (count($this->_headers) > 0)
        {
            foreach ($this->_headers as $header)
            {
                @header($header[0], $header[1]);
            }
        }
    }

    /**
     * return a template's data without outputting it to the browser
     *
     * Also does not automatically output headers
     *
     * @param string $template file relative to application/views
     * @param array $data data to be injected into the template
     */
    public function render($template, $data = array()) {
        return $this->_output('render', $template, $data);
    }

    /**
     * output a template's data
     * This does not need to return anything as the rendered template
     * is immediately output to the browser
     *
     * @param string $template file relative to application/views
     * @param array $data data to be injected into the template
     */
    public function view($template, $data = array()) {
        $this->output_headers();
        
        return $this->_output('display', $template, $data);
    }
    
    /**
     * Output a standard error page
     *
     * @param string $message - message to display
     * @param string $template - override the default template
     */
    public function error($message = 'There was an error, please try again later', $template = null) {
        if (is_null($template)) {
            $template = $this->_default_error_template;
        }
        
        return $this->view($template, compact('message'));
    }
    
    /**
     * Output JSON
     *
     * @param boolean $success
     * @param array $extra - additional data to output
     * @param boolean $display - output or return rendered template
     */
    public function json($success, $extra, $display = true) {
        // let the browser know what's coming at it
        $this->add_header('Content-Type: application/json');
        
        $data = array_merge(array('success' => $success), $extra);
        $func = $display ? 'display' : 'render';
        return $this->_output($func, $this->CI->config->item('json_template'), compact('data'));
    }
    
    /**
     * JSON helper function
     * always outputs at least {"success": true}
     *
     * @param array $data - additional data to be output as json
     * @return string
     */
    public function success($data = array()) {
        return $this->json(true, $data);
    }
    
    /**
     * JSON helper function
     * always outputs at least {"success": false}
     *
     * @param array $data - additional data to be output as json
     * @return string
     */
    public function fail($data = array()) {
        if (is_string($data)) {
            $data = array('msg' => $data);
        }
        
        return $this->json(false, $data);
    }
    
    /** 
     * Used mostly by cli
     * displays a message with a newline at the end
     */
    public function plain($msg = array()) {
        if (!is_array($msg)) {
            $msg = array($msg);
        }
        
        // browser or command line
        $end = $this->CI->input->is_cli_request() ? "\n" : "<br>";
        
        foreach ($msg as $m) {
            echo "$m$end";
        }
    }
    
    /**
     * returns output of a template
     *
     * @param string $func - which twig output function to use
     * @param string $template - which view template to use
     * @param array $data - data to be given to the template
     * @return string
     */
    private function _output($func, $template, $data) {
        // add any functions
        $this->add_helpers();
        
        $loaded_template = $this->_twig->loadTemplate($template . $this->_template_filetype);
        return $loaded_template->{$func}(array_merge($this->_data, $data));
    }
}

?>