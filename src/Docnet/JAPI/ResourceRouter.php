<?php

namespace Docnet\JAPI;

use Docnet\JAPI\Exceptions\Routing;

/**
 * Router for a RESTful Resource
 *
 * GET /products   => Products\Index
 * GET /products/1 => Products\View(1)
 *
 * @package Docnet\App
 */
class ResourceRouter
{
    /**
     * URL to route
     *
     * @var string
     */
    protected $str_url = '';

    /**
     * Output from parse_url()
     *
     * @var array|mixed
     */
    protected $arr_url = [];

    /**
     * Controller class as determined by parseController()
     *
     * @var string
     */
    protected $str_controller;

    /**
     * @var string
     */
    private $str_controller_namespace = '\\';

    /**
     * The resource identifier (if present)
     *
     * @var string|null
     */
    protected $str_resource_id;

    /**
     * We need to know the base namespace for the controller
     *
     * @param string $str_controller_namespace
     */
    public function __construct($str_controller_namespace = '\\')
    {
        $this->str_controller_namespace = $str_controller_namespace;
    }

    /**
     * Route the request.
     *
     * This means "turn the URL into a Controller (class) for execution.
     *
     * Keep URL string and parse_url array response as member vars in case we
     * want to evaluate later.
     *
     * @param string $str_method
     * @param string $str_url
     * @throws Routing The URL could not be matched against the regex
     * @throws \InvalidArgumentException The HTTP Method is not supported
     * @return $this
     */
    public function route($str_method, $str_url) {
        $this->arr_url = parse_url($str_url);
        if (!(bool)preg_match('#/(?<controller>[\w\-]+)(?:\/(?<resourceId>[\w\-]+))?#', $this->arr_url['path'], $arr_matches)) {
            throw new Routing('URL parse error (preg_match): ' . $str_url);
        }

        switch ($str_method) {
            case 'GET':
                if (array_key_exists('resourceId', $arr_matches)) {
                    $str_resource_action = 'View';
                } else {
                    $str_resource_action = 'Index';
                }
                break;
            default:
                throw new \InvalidArgumentException('Unsupported method: ' . $str_method);
        }

        $this->setup($arr_matches['controller'], true, $str_resource_action);
        if (array_key_exists('resourceId', $arr_matches)) {
            $this->str_resource_id = $arr_matches['resourceId'];
        }

        return $this;
    }

    /**
     * Check & store controller from URL parts
     *
     * @param $str_controller
     * @param bool $bol_parse
     * @param string $suffix The suffix to add to the class name
     * @throws Routing
     */
    protected function setup($str_controller, $bol_parse = true, $suffix)
    {
        $this->str_controller = ($bol_parse ? $this->parseController($str_controller) : $str_controller) . "\\{$suffix}";
        if (!method_exists($this->str_controller, 'dispatch')) {
            throw new Routing("Could not find controller: {$this->str_controller}");
        }
    }

    /**
     * Translate URL controller name into name-spaced class
     *
     * @param $str_controller
     * @return string
     */
    protected function parseController($str_controller)
    {
        return $this->str_controller_namespace . str_replace([' ', "\t"], ['', '\\'], ucwords(str_replace('-', ' ', strtolower($str_controller))));
    }

    /**
     * Get the routed controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->str_controller;
    }

    /**
     * @return mixed
     */
    public function getResourceId()
    {
        return $this->str_resource_id;
    }

}
