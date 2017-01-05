<?php

use Docnet\JAPI\ResourceRouter;

require_once __DIR__ . '/Resources/Products.php';
require_once __DIR__ . '/Resources/ProductReviews.php';

class ResourceRouterTest extends PHPUnit_Framework_TestCase
{
    const CONTROLLER_NAMESPACE = '\DocnetTest\Resources\\';

    /**
     * @var ResourceRouter
     */
    protected $obj_router;

    protected function setUp()
    {
        parent::setUp();

        $this->obj_router = new ResourceRouter(static::CONTROLLER_NAMESPACE);
    }

    public function dataProvider() {
        return [
            'Index'                      => ['GET', '/products', 'Products\Index'],
            'Numerical view'             => ['GET', '/products/1', 'Products\View', '1'],
            'Alpha view'                 => ['GET', '/products/item', 'Products\View', 'item'],
            'Ignore case on resource'    => ['GET', '/PrOdUcTs/item', 'Products\View', 'item'],
            'Keep case on id'            => ['GET', '/products/ItEm', 'Products\View', 'ItEm'],
            'Process hyphen on resource' => ['GET', '/product-reviews/item', 'ProductReviews\View', 'item'],
            'Keep hyphen on id'          => ['GET', '/product-reviews/it-em', 'ProductReviews\View', 'it-em'],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string      $method
     * @param string      $url
     * @param string      $controller
     * @param string|null $resourceId
     */
    public function testRoute($method, $url, $controller, $resourceId = null) {
        $this->obj_router->route($method, $url);
        $this->assertEquals($this->obj_router->getController(), static::CONTROLLER_NAMESPACE . $controller);
        if ($resourceId !== null) {
            $this->assertEquals($this->obj_router->getResourceId(), $resourceId);
        }
    }

    /**
     * Test for failed routing
     *
     * @expectedException \Docnet\JAPI\Exceptions\Routing
     */
    public function testRoutingFailure()
    {
        $this->obj_router->route('GET', '/missing-url');
    }

    /**
     * Test for failed URL parsing
     *
     * @expectedException \Docnet\JAPI\Exceptions\Routing
     */
    public function testMalformedUrl()
    {
        $this->obj_router->route('GET', 'http://:80');
    }

    /**
     * Test for failed URL regex match
     *
     * @expectedException \Docnet\JAPI\Exceptions\Routing
     */
    public function testNonUrlString()
    {
        $this->obj_router->route('GET', '-');
    }
}
