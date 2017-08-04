<?php

namespace mmmfestBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ComponentLoader extends Loader
{
    private $loaded = false;

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $components = [
          'projet'    => 'Project',
          'evenement' => 'Event',
          'proposal' => 'Proposal',
          'document' => 'Document',
          'documentType' => 'DocumentType',
        ];

        $routes = new RouteCollection();

        foreach ($components as $path => $component) {
            $componentLowerCase = strtolower($component);

            $routes->add(
              $componentLowerCase.'List',
              new Route(
                '/mon-compte/'.$path.'/'
                , [
                  '_controller' => 'mmmfestBundle:'.$component.':list',
                  'component'   => $component,
                ]
              )
            );

            $routes->add(
              $componentLowerCase.'Add',
              new Route(
                '/mon-compte/'.$path.'/ajouter'
                , [
                  '_controller' => 'mmmfestBundle:'.$component.':add',
                  'component'   => $component,
                ]
              )
            );

            $routes->add(
              $componentLowerCase.'Edit',
              new Route(
                '/mon-compte/'.$path.'/modifier'
                , [
                  '_controller' => 'mmmfestBundle:'.$component.':add',
                  'component'   => $component,
                ]
              )
            );
        }

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'component' === $type;
    }
}
