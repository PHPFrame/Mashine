<?php
/**
 * src/models/oauth/ApiMethodsMapper.php
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
 * OAuth methods mapper class.
 *
 * @category PHPFrame_Applications
 * @package  Mashine
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/E-NOISE/Mashine
 * @since    1.0
 */
class ApiMethodsMapper
{
    private $_db;

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
        $this->_db = $db;
    }

    /**
     * Find API methods auth info.
     *
     * @return array
     * @since  1.0
     */
    public function find()
    {
        return $this->_db->fetchAssocList("SELECT * FROM #__api_methods");
    }

    /**
     * Find API auth info for a given method by id.
     *
     * @param int $id The method row id in the db.
     *
     * @return array
     * @since  1.0
     */
    public function findOne($id)
    {
        $sql  = "SELECT * FROM #__api_methods";
        $sql .= " WHERE id = :id";

        $raw = $this->_db->fetchAssocList($sql, array(":id"=>$id));
        if (is_array($raw) && !empty($raw)) {
            return $raw[0];
        } else {
            return null;
        }
    }

    /**
     * Find API auth info for a given method by method name.
     *
     * @param string $method The API method name.
     *
     * @return array|null
     * @since  1.0
     */
    public function findByMethod($method)
    {
        $sql  = "SELECT * FROM #__api_methods";
        $sql .= " WHERE method = :method";

        $raw = $this->_db->fetchAssocList($sql, array(":method"=>$method));
        if (is_array($raw) && !empty($raw)) {
            return $raw[0];
        } else {
            return null;
        }
    }

    /**
     * Insert or update auth info for a given method.
     *
     * @param string $method The API method name for which to save auth info.
     * @param int    $oauth  [Optional] Whether to allow OAuth. 3 possible
     *                       values: 0 = No, 2 = 2 legged, 3 = 3 legged. Note
     *                       that value 1 is not allowed. Default is 0.
     * @param int    $cookie [Optional] Whether or not to allow cookie based
     *                       auth. Two possible values: 0 = No, 1 = Yes.
     *                       Default value is 0.
     *
     * @return void
     * @since  1.0
     */
    public function insert($method, $oauth=null, $cookie=null)
    {
        $row = $this->findByMethod($method);

        if (count($row) > 0) {
            $sql    = "UPDATE #__api_methods SET ";
            $params = array();

            if (!is_null($oauth)) {
                $sql .= "oauth = :oauth";
                $params[":oauth"] = (int) $oauth;
            }

            if (!is_null($cookie)) {
                if (!is_null($oauth)) $sql .= ", ";

                $sql .= "cookie = :cookie";
                $params[":cookie"] = (int) $cookie;
            }

            $sql .= " WHERE method = :method";
            $params[":method"] = $method;

        } else {
            $sql  = "INSERT INTO #__api_methods (method, oauth, cookie)";
            $sql .= " VALUES (:method, :oauth, :cookie)";
            $params = array(
                ":method" => $method,
                ":oauth"  => (int) $oauth,
                ":cookie"  => (int) $cookie
            );
        }

        $this->_db->query($sql, $params);
    }
}
