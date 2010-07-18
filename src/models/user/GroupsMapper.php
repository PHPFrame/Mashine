<?php
/**
 * src/models/users/GroupsMapper.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_Applications
 * @package   PHPFrame_CmsAppTemplate
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/lupomontero/PHPFrame_CmsAppTemplate
 */

/**
 * Users groups mapper class
 *
 * @category PHPFrame_Applications
 * @package  PHPFrame_CmsAppTemplate
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     https://github.com/lupomontero/PHPFrame_CmsAppTemplate
 * @since    1.0
 */
class GroupsMapper extends PHPFrame_Mapper
{
    /**
     * Constructor.
     *
     * @param PHPFrame_Database $db Instance of PHPFrame_Database.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Database $db)
    {
        parent::__construct("PHPFrame_Group", $db, "#__groups");
    }
}