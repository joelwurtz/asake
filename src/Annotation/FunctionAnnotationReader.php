<?php

namespace Asake\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\PhpParser;
use PhpDocReader\AnnotationException;

class FunctionAnnotationReader
{
    /**
     * Global map for imports.
     *
     * @var array
     */
    private static $globalImports = array(
        'ignoreannotation' => 'Doctrine\Common\Annotations\Annotation\IgnoreAnnotation',
    );

    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names are case sensitive.
     *
     * @var array
     */
    private static $globalIgnoredNames = array(
        // Annotation tags
        'Annotation' => true, 'Attribute' => true, 'Attributes' => true,
        /* Can we enable this? 'Enum' => true, */
        'Required' => true,
        'Target' => true,
        // Widely used tags (but not existent in phpdoc)
        'fix' => true , 'fixme' => true,
        'override' => true,
        // PHPDocumentor 1 tags
        'abstract'=> true, 'access'=> true,
        'code' => true,
        'deprec'=> true,
        'endcode' => true, 'exception'=> true,
        'final'=> true,
        'ingroup' => true, 'inheritdoc'=> true, 'inheritDoc'=> true,
        'magic' => true,
        'name'=> true,
        'toc' => true, 'tutorial'=> true,
        'private' => true,
        'static'=> true, 'staticvar'=> true, 'staticVar'=> true,
        'throw' => true,
        // PHPDocumentor 2 tags.
        'api' => true, 'author'=> true,
        'category'=> true, 'copyright'=> true,
        'deprecated'=> true,
        'example'=> true,
        'filesource'=> true,
        'global'=> true,
        'ignore'=> true, /* Can we enable this? 'index' => true, */ 'internal'=> true,
        'license'=> true, 'link'=> true,
        'method' => true,
        'package'=> true, 'param'=> true, 'property' => true, 'property-read' => true, 'property-write' => true,
        'return'=> true,
        'see'=> true, 'since'=> true, 'source' => true, 'subpackage'=> true,
        'throws'=> true, 'todo'=> true, 'TODO'=> true,
        'usedby'=> true, 'uses' => true,
        'var'=> true, 'version'=> true,
        // PHPUnit tags
        'codeCoverageIgnore' => true, 'codeCoverageIgnoreStart' => true, 'codeCoverageIgnoreEnd' => true,
        // PHPCheckStyle
        'SuppressWarnings' => true,
        // PHPStorm
        'noinspection' => true,
        // PEAR
        'package_version' => true,
        // PlantUML
        'startuml' => true, 'enduml' => true,
        // Symfony 3.3 Cache Adapter
        'experimental' => true
    );

    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names are case sensitive.
     *
     * @var array
     */
    private static $globalIgnoredNamespaces = array();

    /**
     * Add a new annotation to the globally ignored annotation names with regard to exception handling.
     *
     * @param string $name
     */
    static public function addGlobalIgnoredName($name)
    {
        self::$globalIgnoredNames[$name] = true;
    }

    /**
     * Add a new annotation to the globally ignored annotation namespaces with regard to exception handling.
     *
     * @param string $namespace
     */
    static public function addGlobalIgnoredNamespace($namespace)
    {
        self::$globalIgnoredNamespaces[$namespace] = true;
    }

    /**
     * Annotations parser.
     *
     * @var \Doctrine\Common\Annotations\DocParser
     */
    private $parser;

    /**
     * Annotations parser used to collect parsing metadata.
     *
     * @var \Doctrine\Common\Annotations\DocParser
     */
    private $preParser;

    /**
     * PHP parser used to collect imports.
     *
     * @var \Doctrine\Common\Annotations\PhpParser
     */
    private $phpParser;

    /**
     * In-memory cache mechanism to store imported annotations per class.
     *
     * @var array
     */
    private $imports = array();

    /**
     * In-memory cache mechanism to store ignored annotations per class.
     *
     * @var array
     */
    private $ignoredAnnotationNames = array();

    /**
     * Constructor.
     *
     * Initializes a new AnnotationReader.
     *
     * @param DocParser $parser
     *
     * @throws AnnotationException
     */
    public function __construct(DocParser $parser = null)
    {
        if (extension_loaded('Zend Optimizer+') && (ini_get('zend_optimizerplus.save_comments') === "0" || ini_get('opcache.save_comments') === "0")) {
            throw AnnotationException::optimizerPlusSaveComments();
        }

        if (extension_loaded('Zend OPcache') && ini_get('opcache.save_comments') == 0) {
            throw AnnotationException::optimizerPlusSaveComments();
        }

        if (PHP_VERSION_ID < 70000) {
            if (extension_loaded('Zend Optimizer+') && (ini_get('zend_optimizerplus.load_comments') === "0" || ini_get('opcache.load_comments') === "0")) {
                throw AnnotationException::optimizerPlusLoadComments();
            }

            if (extension_loaded('Zend OPcache') && ini_get('opcache.load_comments') == 0) {
                throw AnnotationException::optimizerPlusLoadComments();
            }
        }

        $this->parser = $parser ?: new DocParser();
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctionAnnotations(\ReflectionFunction $function)
    {
        $context = 'function ' . $function->getName() . '()';

        $this->parser->setTarget(Target::TARGET_METHOD);
        $this->parser->setIgnoredAnnotationNamespaces(self::$globalIgnoredNamespaces);

        return $this->parser->parse($function->getDocComment(), $context);
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctionAnnotation(\ReflectionFunction $method, $annotationName)
    {
        $annotations = $this->getFunctionAnnotations($method);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }
}