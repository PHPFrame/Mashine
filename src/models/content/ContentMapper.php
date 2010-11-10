<?php
/**
 * src/models/content/ContentMapper.php
 *
 * PHP version 5
 *
 * @category  PHPFrame_Applications
 * @package   Mashine
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 E-NOISE.COM LIMITED
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/E-NOISE/Mashine
 */

/**
 * Content mapper class
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class ContentMapper extends PHPFrame_Mapper
{
    private $_db, $_cache_dir;

    /**
     * Constructor.
     *
     * @param PHPFrame_Database $db        Instance of PHPFrame_Database.
     * @param string            $cache_dir Absolute path to cache dir.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Database $db, $cache_dir)
    {
        $this->_db = $db;
        $this->_cache_dir = (string) $cache_dir;

        if (!is_dir($this->_cache_dir)) {
            PHPFrame_Filesystem::ensureWritableDir($this->_cache_dir);
        }

        parent::__construct("Content", $db, "#__content", "type");
    }

    /**
     * Find content objects. We override the default find() method to order
     * results correctly.
     *
     * @param PHPFrame_IdObject $id_obj [Optional]
     *
     * @return PHPFrame_PersistentObjectCollection
     * @since  1.0
     */
    public function find(PHPFrame_IdObject $id_obj=null)
    {
        if (is_null($id_obj)) {
            $id_obj = $this->getIdObject();
            $id_obj->orderby("c.pub_date DESC, c.id", "DESC");
        }

        $collection = parent::find($id_obj);
        foreach ($collection as $obj) {
            if ($obj instanceof FeedContent && $this->_cache_dir) {
                $feeds_cache_dir = $this->_cache_dir.DS."feeds";
                PHPFrame_Filesystem::ensureWritableDir($feeds_cache_dir);
                $obj->cacheDir($feeds_cache_dir);
            }
        }

        return $collection;
    }

    public function findOne($id_or_slug)
    {
        $id_obj = $this->getIdObject();

        if ($this->getFactory()->getDB()->isSQLite()) {
            $select  = array(
                "c.*",
                "cd.params AS params",
                "u.email AS author_email",
                "(uc.first_name || ' ' ||  uc.last_name) AS author"
            );
        } else {
            $select  = array(
                "c.*",
                "cd.params AS params",
                "u.email AS author_email",
                "CONCAT(uc.first_name, ' ', uc.last_name) AS author"
            );
        }

        $select[] = "cd.description AS description";
        $select[] = "cd.keywords AS keywords";
        $select[] = "cd.body AS body";

        $id_obj->select($select);

        if (is_int($id_or_slug)) {
            $id_obj->where("c.id", "=", ":id");
        } else {
            $id_obj->where("c.slug", "=", ":id");
        }

        $id_obj->params(":id", $id_or_slug);

        $collection = $this->find($id_obj);
        if (count($collection) > 0) {
            return $collection->getElement(0);
        } else {
            return null;
        }
    }

    public function insert(Content $obj)
    {
        $obj->validateAll();

        if ($obj->id() <= 0) {
            $obj->ctime(time());
            $build_query_method = "buildInsertQuery";
        } else {
            $build_query_method = "buildUpdateQuery";
        }

        $obj->mtime(time());

        $array     = array();
        $data      = array();
        $data_keys = array("description", "keywords", "body", "params");
        foreach (iterator_to_array($obj) as $key=>$value) {
            if (in_array($key, $data_keys)) {
                $data[$key] = $value;
            } else {
                $array[$key] = $value;
            }
        }

        // Insert main content stuff in #__content table
        $db     = $this->getFactory()->getDB();
        $sql    = $this->getFactory()->getAssembler()->$build_query_method($array);
        $params = $this->getFactory()->getAssembler()->buildQueryParams($array);

        $db->query($sql, $params);

        if ($obj->id() <= 0) {
            $obj->id($db->lastInsertId());

            // Insert data in separate table
            $sql  = "INSERT INTO #__content_data (`content_id`, `";
            $sql .= implode("`, `", $data_keys);
            $sql .= "`) VALUES (".$obj->id().", :";
            $sql .= implode(", :", $data_keys);
            $sql .= ")";
        } else {
            // Update data in separate table
            $sql = "UPDATE #__content_data SET ";
            $i   = 0;
            foreach ($data_keys as $data_key) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $sql .= "`".$data_key."` = :".$data_key;
                $i++;
            }
            $sql .= " WHERE `content_id` = ".$obj->id();
        }

        $params = $this->getFactory()->getAssembler()->buildQueryParams($data);

        $db->query($sql, $params);

        $obj->markClean();
    }

    public function delete($id_or_obj)
    {
        if ($id_or_obj instanceof Content) {
            $obj = $id_or_obj;
        } else {
            $obj = $this->findOne($id_or_obj);
            if (!$obj instanceof Content) {
                $msg = "Can not delete. Content not found.";
                throw new RuntimeException($msg);
            }
        }

        if ($obj instanceof PostsCollectionContent) {
            $id_obj = $this->getIdObject();
            $id_obj->where("parent_id", "=", $obj->id());
            $children = $this->find($id_obj);
            foreach ($children as $child) {
                $this->delete($child);
            }
        } else {
            $sql  = "UPDATE #__content SET `parent_id` = ".$obj->parentId();
            $sql .= " WHERE `parent_id` = ".$obj->id();
            $this->getFactory()->getDB()->query($sql);
        }

        $sql = "DELETE FROM #__content_data WHERE `content_id` = ".$obj->id();
        $this->getFactory()->getDB()->query($sql);

        return parent::delete($obj);
    }

    public function getIdObject()
    {
        $id_obj = parent::getIdObject();

        if ($this->getFactory()->getDB()->isSQLite()) {
            $select  = array(
                "c.*",
                "cd.params AS params",
                "u.email AS author_email",
                "(uc.first_name || ' ' ||  uc.last_name) AS author"
            );
        } else {
            $select  = array(
                "c.*",
                "cd.params AS params",
                "u.email AS author_email",
                "CONCAT(uc.first_name, ' ', uc.last_name) AS author"
            );
        }

        $id_obj->select($select);
        $id_obj->from("#__content AS c");
        $id_obj->join("LEFT JOIN #__content_data cd ON cd.content_id = c.id");
        $id_obj->join("LEFT JOIN #__users u ON u.id = c.owner");
        $id_obj->join("LEFT JOIN #__contacts uc ON uc.owner = c.owner");
        $id_obj->where("uc.preferred", "=", "1");
        $id_obj->orderby("c.parent_id ASC, c.pub_date", "DESC");

        return $id_obj;
    }
}
