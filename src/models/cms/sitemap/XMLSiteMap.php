<?php
/**
 * src/models/cms/sitemap/XMLSiteMap.php
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
 * XMLSiteMap class
 *
 * @category PHPFrame_AppTemplates
 * @package  PHPFrame_CmsAppTemplate
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/PHPFrame_CmsAppTemplate
 * @since    1.0
 */
class XMLSiteMap extends PHPFrame_XMLDocument
{
    private $_node, $_base_url;

    /**
     * Constructor.
     *
     * @param Content $node     A Content object for the homepage with all it's
     *                          child nodes.
     * @param string  $base_url Base URL for links.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(Content $node, $base_url)
    {
        $this->_node     = $node;
        $this->_base_url = (string) $base_url;

        parent::__construct();

        $urlset = $this->addNode("urlset");
        $this->addNodeAttr(
            $urlset,
            "xmlns",
            "http://www.sitemaps.org/schemas/sitemap/0.9"
        );
        $this->addNodeAttr(
            $urlset,
            "xmlns:xsi",
            "http://www.w3.org/2001/XMLSchema-instance"
        );
        $this->addNodeAttr(
            $urlset,
            "xsi:schemaLocation",
            "http://www.sitemaps.org/schemas/sitemap/0.9 "
           ."http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
        );

        $this->_addSiteMapNode($this->_node, $urlset);
    }

    /**
     * Iterate through sitemap node and add XML nodes to represent them.
     *
     * @param Content $node
     * @param DomNode $parent_xml_node
     *
     * @return void
     * @since  1.0
     */
    private function _addSiteMapNode(Content $node, $parent_xml_node, $depth=0)
    {
        if (!$node->robotsIndex()) {
            return;
        }

        $url = $this->addNode("url", $parent_xml_node);
        $this->addNode("loc", $url, null, $this->_base_url.$node->slug());

        $lastmod = date("Y-m-d", $node->mtime());

        switch (get_class($node)) {
        case "MVCContent" :
            $changefreq = "weekly";
            $priority   = 1;
            break;
        case "PageContent" :
            $changefreq = "monthly";
            $priority   = 1;
            break;
        case "PostContent" :
            $changefreq = "monthly";
            $priority   = 1;
            break;
        case "PostsCollectionContent" :
            $changefreq = "daily";
            $priority   = 1;
            //$lastmod = latest child?;
            break;
        default :
            $changefreq = null;
            $priority   = null;
            break;
        }

        $this->addNode("lastmod", $url, null, $lastmod);

        if ($changefreq) {
            $this->addNode("changefreq", $url, null, $changefreq);
        }

        if ($priority) {
            $priority = ($depth) ? 2*$priority/($depth+1.5) : $priority;
            $priority = round($priority, 1);
            $this->addNode("priority", $url, null, $priority);
        }

        foreach ($node->getChildren() as $child) {
            $this->_addSiteMapNode($child, $parent_xml_node, ($depth+1));
        }
    }
}
