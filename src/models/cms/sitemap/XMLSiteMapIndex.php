<?php
/**
 * src/models/cms/sitemap/XMLSiteMapIndex.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_AppTemplates
 * @package   PHPFrame_CmsAppTemplate
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/lupomontero/PHPFrame_CmsAppTemplate
 */

/**
 * XMLSiteMapIndex class
 *
 * @category PHPFrame_AppTemplates
 * @package  PHPFrame_CmsAppTemplate
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/PHPFrame_CmsAppTemplate
 * @since    1.0
 */
class XMLSiteMapIndex implements IteratorAggregate
{
    private $_sitemaps;

    /**
     * Constructor.
     *
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        $this->_sitemaps = new SplObjectStorage();
    }

    public function getIterator()
    {
        return $this->_sitemaps;
    }
}
