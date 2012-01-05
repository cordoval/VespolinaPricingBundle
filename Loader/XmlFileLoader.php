<?php

/**
 * (c) 2011 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\PricingBundle\Loader;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Loader\FileLoader;

use Vespolina\PricingBundle\Model\PricingConfigurationCollection;
use Vespolina\PricingBundle\Model\PricingConfiguration;
use Vespolina\PricingBundle\Model\PricingElementConfiguration;
use Vespolina\PricingBundle\Model\PricingSetConfiguration;


/**
 * XmlFileLoader loads pricing configuration files.
 *
 * @author Daniel Kucharski <daniel@xerias.be>
 */
class XmlFileLoader extends FileLoader
{
    /**
     * Loads an XML file.
     *
     * @param string $file An XML file path
     * @param string $type The resource type
     *
     * @return PricingConfigurationCollection A PricingConfigurationCollection instance
     *
     * @throws \InvalidArgumentException When a tag can't be parsed
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $xml = $this->loadFile($path);

        $collection = new PricingConfigurationCollection();
        $collection->addResource(new FileResource($path));

        // process pricing configurations and imports
        foreach ($xml->documentElement->childNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            $this->parseNode($collection, $node, $path, $file);
        }

        return $collection;
    }

    /**
     * Parses a node from a loaded XML file.
     *
     * @param PricingConfigurationCollection $collection the collection to associate with the node
     * @param DOMElement      $node the node to parse
     * @param string          $path the path of the XML file being processed
     * @param string          $file
     */
    protected function parseNode(PricingConfigurationCollection $collection, \DOMElement $node, $path, $file)
    {
        switch ($node->tagName) {
            case 'pricing_configuration':
                $this->parsePricingConfiguration($collection, $node, $path);
                break;
            case 'import':
                $resource = (string) $node->getAttribute('resource');
                $type = (string) $node->getAttribute('type');
                $prefix = (string) $node->getAttribute('prefix');
                $this->setCurrentDir(dirname($path));
                $collection->addCollection($this->import($resource, ('' !== $type ? $type : null), false, $file), $prefix);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unable to parse tag "%s"', $node->tagName));
        }
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'xml' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'xml' === $type);
    }

    /**
     * Parses a pricing configuration and add it to the PricingConfigurationCollection.
     *
     * @param PricingConfigurationCollection $collection A PricingConfigurationCollection instance
     * @param \DOMElement     $definition PricingConfigurationCollection definition
     * @param string          $file       An XML file path
     *
     * @throws \InvalidArgumentException When the definition cannot be parsed
     */
    protected function parsePricingConfiguration(PricingConfigurationCollection $collection, \DOMElement $definition, $file)
    {
        $defaults = array();
        $requirements = array();
        $options = array();

        $pricingConfiguration = new PricingConfiguration($definition->getAttribute('id'));

        $pricingSetConfiguration = new PricingSetConfiguration();
        $pricingConfiguration->setPricingSetConfiguration(($pricingSetConfiguration));

        foreach ($definition->childNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            switch ($node->tagName) {

                case 'pricing_execution':
                    $this->parsePricingExecution($pricingSetConfiguration, $node);
                    break;
                case 'pricing_set':
                    $this->parsePricingSet($pricingSetConfiguration, $node);
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unable to parse tag "%s"', $node->tagName));
            }
        }

        $collection->add((string) $definition->getAttribute('id'), $pricingConfiguration);
    }

    
    protected function parsePricingExecution(PricingSetConfiguration $pricingSetConfiguration, \DOMElement $definition )
    {

        foreach ($definition->childNodes as $node) {

            $class = '';
            $executionEvent = '';
            $executionOptions = array();
            $name =  '';

            if (!$node instanceof \DOMElement) {
                continue;
            }

            switch ($node->tagName) {

                case 'step':

                    $name = trim($node->getAttribute('name'));

                    foreach($node->childNodes as $stepNode)
                    {

                        if (!$stepNode instanceof \DOMElement) {
                            continue;
                        }

                        switch($stepNode->tagName)
                        {

                            case 'class':
                                $class = trim($stepNode->nodeValue);
                                break;
                            case 'execution_event':
                                $executionEvent = trim($stepNode->nodeValue);
                                break;
                            default:
                                $executionOptions[$stepNode->tagName] = trim($stepNode->nodeValue);
                        }

                    }

                    $pricingSetConfiguration->addPricingExecutionStep(
                        new $class($name, $executionOptions),
                        array('execution_event' => $executionEvent));

                    break;

                default:
                    throw new \InvalidArgumentException(sprintf('Unable to parse tag "%s"', $node->tagName));
            }
        }
    }

    protected function parsePricingSet(PricingSetConfiguration $pricingSetConfiguration, \DOMElement $definition )
    {
        foreach ($definition->childNodes as $node) {

            $class = '';
            $executionEvent = '';
            $name =  '';

            if (!$node instanceof \DOMElement) {
                continue;
            }

            switch ($node->tagName) {

                case 'dimension':

                    $name = trim($node->getAttribute('name'));

                    foreach($node->childNodes as $dimensionNode)
                    {

                        if (!$dimensionNode instanceof \DOMElement) {
                            continue;
                        }

                        switch ($dimensionNode->tagName)
                        {

                            case 'class':
                                $class = trim($dimensionNode->nodeValue);
                                break;
                        }

                        $pricingSetConfiguration->addPricingDimension(
                            new $class($name));
                    }

                    break;

                case 'element':

                    $name = trim($node->getAttribute('name'));

                    foreach($node->childNodes as $elementNode)
                    {

                        if (!$elementNode instanceof \DOMElement) {
                           continue;
                        } else{
                        }

                        switch ($elementNode->tagName)
                        {
                            case 'class':
                                $class = trim($elementNode->nodeValue);
                                break;
                            case 'execution_event':
                                $executionEvent = trim($elementNode->nodeValue);
                        }
                    }

                    $pricingElementConfiguration = new PricingElementConfiguration($name, $class, $executionEvent);

                    $pricingSetConfiguration->addPricingElementConfiguration($pricingElementConfiguration);

                    break;

                default:
                    throw new \InvalidArgumentException(sprintf('Unable to parse pricing set tag "%s"', $node->tagName));
            }
        }
    }
    
    /**
     * Loads an XML file.
     *
     * @param string $file An XML file path
     *
     * @return \DOMDocument
     *
     * @throws \InvalidArgumentException When loading of XML file returns error
     */
    protected function loadFile($file)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!$dom->load($file, LIBXML_COMPACT)) {
            throw new \InvalidArgumentException(implode("\n", $this->getXmlErrors()));
        }
        $dom->validateOnParse = true;
        $dom->normalizeDocument();
        libxml_use_internal_errors(false);
        //$this->validate($dom);

        return $dom;
    }

    /**
     * Validates a loaded XML file.
     *
     * @param \DOMDocument $dom A loaded XML file
     *
     * @throws \InvalidArgumentException When XML doesn't validate its XSD schema
     */
    protected function validate(\DOMDocument $dom)
    {
        $location = __DIR__.'/schema/pricing-1.0.xsd';

        $current = libxml_use_internal_errors(true);
        if (!$dom->schemaValidate($location)) {
            throw new \InvalidArgumentException(implode("\n", $this->getXmlErrors()));
        }
        libxml_use_internal_errors($current);
    }

    /**
     * Retrieves libxml errors and clears them.
     *
     * @return array An array of libxml error strings
     */
    private function getXmlErrors()
    {
        $errors = array();
        foreach (libxml_get_errors() as $error) {
            $errors[] = sprintf('[%s %s] %s (in %s - line %d, column %d)',
                LIBXML_ERR_WARNING == $error->level ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ? $error->file : 'n/a',
                $error->line,
                $error->column
            );
        }

        libxml_clear_errors();

        return $errors;
    }
}
