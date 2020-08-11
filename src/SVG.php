<?php

namespace SVG;

use SVG\Nodes\Structures\SVGDocumentFragment;
use SVG\Rasterization\SVGRasterizer;
use SVG\Reading\SVGReader;
use SVG\Writing\SVGWriter;

/**
 * This is the main class for any SVG image, as it hosts the document root and
 * offers conversion methods.
 */
class SVG
{
    /** @var SVGReader $reader The singleton reader used by this class. */
    private static $reader;

    /** @var SVGDocumentFragment $document This image's root `svg` node/tag. */
    private $document;

    /**
     * @param string   $width      The image's width (any CSS length).
     * @param string   $height     The image's height (any CSS length).
     */
    public function __construct($width, $height)
    {
        $this->document = new SVGDocumentFragment($width, $height);
    }

    /**
     * @return SVGDocumentFragment The document/root node of this image.
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Converts this image into a rasterized GD resource of the given size.
     *
     * The resulting image resource supports transparency and represents the
     * SVG as accurately as possible (with PHP's limited imaging functions).
     * Note that, since images in SVG have an innate size, the given size only
     * scales the output canvas and does not influence element positions.
     *
     * @param int $width         The target canvas's width, in pixels.
     * @param int $height        The target canvas's height, in pixels.
     * @param string $background The background color (hex/rgb[a]/hsl[a]/...).
     *
     * @return resource The rasterized image as a GD resource (with alpha).
     */
    public function toRasterImage($width, $height, $background = null)
    {
        $docWidth  = $this->document->getWidth();
        $docHeight = $this->document->getHeight();
        $viewBox = $this->document->getViewBox();

        $rasterizer = new SVGRasterizer($docWidth, $docHeight, $viewBox, $width, $height, $background);
        $this->document->rasterize($rasterizer);

        return $rasterizer->finish();
    }

    /**
     * @see SVG::toXMLString() For the implementation (this is a wrapper).
     */
    public function __toString()
    {
        return $this->toXMLString();
    }

    /**
     * Converts this image's document tree into an XML source code string.
     *
     * Note that an image parsed from an XML string might not have the exact
     * same XML string generated by this method, since the output is optimized
     * and formatted according to specific rules.
     *
     * @param bool $standalone optional, false omits the leading <?xml tag
     *
     * @return string This image's document tree as an XML string.
     */
    public function toXMLString($standalone = true)
    {
        $writer = new SVGWriter($standalone);
        $writer->writeNode($this->document);

        return $writer->getString();
    }

    /**
     * Parses the given XML string into an instance of this class.
     *
     * @param string $string The XML string to parse.
     *
     * @return SVG A new image, with the nodes parsed from the XML.
     */
    public static function fromString($string)
    {
        return self::getReader()->parseString($string);
    }

    /**
     * Reads the file at the given path as an XML string, and then parses it
     * into an instance of this class.
     *
     * @param string $file The path to the file to parse.
     *
     * @return SVG A new image, with the nodes parsed from the XML.
     */
    public static function fromFile($file)
    {
        return self::getReader()->parseFile($file);
    }

    /**
     * @return SVGReader The singleton reader shared across all instances.
     */
    private static function getReader()
    {
        if (!isset(self::$reader)) {
            self::$reader = new SVGReader();
        }
        return self::$reader;
    }
}
