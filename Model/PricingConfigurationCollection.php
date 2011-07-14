<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Vespolina\PricingBundle\Model;

use Symfony\Component\Config\Resource\ResourceInterface;

use Vespolina\PricingBundle\Model\PricingConfigurationInterface;

/**
 * A PricingConfigurationCollection represents a set of PricingConfiguration instances.
 *
 * @author Daniel Kucharski <daniel@xerias.be>
 **/
class PricingConfigurationCollection implements \IteratorAggregate
{
    protected $pricingConfigurations;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->pricingConfigurations = array();
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->pricingConfigurations);
    }
    
    /**
     * Adds a pricing configuration.
     *
     * @param string $name  The pricing configuration name
     * @param PricingConfiguration  $pricingConfiguration A PricingConfigurationInterface instance
     *
     * @throws \InvalidArgumentException When pricing configuration name contains non valid characters
     */
    public function add($name, PricingConfigurationInterface $pricingConfiguration)
    {
        if (!preg_match('/^[a-z0-9A-Z_.]+$/', $name)) {
            throw new \InvalidArgumentException(sprintf('Name "%s" contains non valid characters for a pricing configuration name.', $name));
        }

        $this->pricingConfigurations[$name] = $pricingConfiguration;
    }

    /**
     * Returns the array of pricing configurations.
     *
     * @return array An array of pricing configurations
     */
    public function all()
    {
        $pricingConfigurations = array();
        foreach ($this->pricingConfigurations as $name => $pricingConfiguration) {
            if ($pricingConfiguration instanceof PricingConfigurationCollection) {
                $pricingConfigurations = array_merge($pricingConfigurations, $pricingConfiguration->all());
            } else {
                $pricingConfigurations[$name] = $pricingConfiguration;
            }
        }

        return $pricingConfiguration;
    }

    /**
     * Gets a route by name.
     *
     * @param  string $name  The route name
     *
     * @return Route  $route A Route instance
     */
    public function get($name)
    {
        // get the latest defined route
        foreach (array_reverse($this->pricingConfigurations) as $pricingConfigurations) {
            if (!$pricingConfigurations instanceof PricingConfigurationCollection) {
                continue;
            }

            if (null !== $pricingConfiguration = $pricingConfigurations->get($name)) {
                return $pricingConfiguration;
            }
        }

        if (isset($this->pricingConfigurations[$name])) {
            
            return $this->pricingConfigurations[$name];
        }
    }

    /**
     * Adds a route collection to the current set of routes (at the end of the current set).
     *
     * @param RouteCollection $collection A RouteCollection instance
     * @param string          $prefix     An optional prefix to add before each pattern of the route collection
     */
    public function addCollection(RouteCollection $collection, $prefix = '')
    {
        $collection->addPrefix($prefix);

        $this->routes[] = $collection;
    }

    /**
     * Adds a prefix to all routes in the current set.
     *
     * @param string          $prefix     An optional prefix to add before each pattern of the route collection
     */
    public function addPrefix($prefix)
    {
        // a prefix must not end with a slash
        $prefix = rtrim($prefix, '/');

        if (!$prefix) {
            return;
        }

        // a prefix must start with a slash
        if ('/' !== $prefix[0]) {
            $prefix = '/'.$prefix;
        }

        $this->prefix = $prefix.$this->prefix;

        foreach ($this->routes as $name => $route) {
            if ($route instanceof RouteCollection) {
                $route->addPrefix($prefix);
            } else {
                $route->setPattern($prefix.$route->getPattern());
            }
        }
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Returns an array of resources loaded to build this collection.
     *
     * @return ResourceInterface[] An array of resources
     */
    public function getResources()
    {
        $resources = $this->resources;
        foreach ($this as $routes) {
            if ($routes instanceof RouteCollection) {
                $resources = array_merge($resources, $routes->getResources());
            }
        }

        return array_unique($resources);
    }

    /**
     * Adds a resource for this collection.
     *
     * @param ResourceInterface $resource A resource instance
     */
    public function addResource(ResourceInterface $resource)
    {
        $this->resources[] = $resource;
    }
}
