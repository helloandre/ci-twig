<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
/**
 * where all your templates are located
 */
$config['template_dir'] = APPPATH.'views';

/**
 * where you would like twig to cache compiled templates
 */
$config['cache_dir'] = APPPATH.'cache/twig';

/**
 * if you would like csrf_name and csrf_token made available
 * to all templates (useful for forms)
 */
$config['autoload_csrf'] = true;

/**
 * The filetype of templates
 */
$config['template_filetype'] = '.html.twig';

/**
 * the default template used by error()
 * relative to template_dir
 */
$config['default_error_template'] = 'errors/generic';

/**
 * default template to use to render JSON
 */
$config['json_template'] = 'layouts/json';

/**
 * Any helper functions automatically loaded to every template
 *
 * format: array(<helper_file>, <helper_function>)
 */
$config['autoloaded_helpers'] = array(array('url', 'site_url'));