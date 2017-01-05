<?php

namespace DocnetTest\Resources\Products;

use Docnet\JAPI\Controller;

class Index extends Controller
{
    public function dispatch()
    {
        $this->setResponse(['test' => true]);
    }
}

class View extends Controller
{
    public function dispatch()
    {
        $this->setResponse(['test' => true]);
    }
}
