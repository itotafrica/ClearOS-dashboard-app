<?php

/**
 * Dashboard class.
 *
 * @category   apps
 * @package    dashboard
 * @subpackage libraries
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2014 ClearCenter
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/dashboard/
 */

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\dashboard;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('dashboard');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Software as Software;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Software');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Dashboard class.
 *
 * @category   apps
 * @package    dashboard
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2014 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/dashboard/
 */

class Dashboard extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_CONFIG = '/etc/clearos/dashboard.conf';
    const MAX_ROWS = 8;
    const CACHE_S = 120;

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $username = NULL;
    protected $loaded = FALSE;
    protected $config = array();

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Dashboard constructor.
     *
     * @param string $username username for preferences to be stored as
     *
     */

    function __construct($options)
    {
        clearos_profile(__METHOD__, __LINE__);
        $this->username = $options['username'];
    }
    /**
     * Verify if the configuration file exist
     * 
     * @return boolean
    */
    function configuration_file_exist()
    {
        if(file_exists(self::FILE_CONFIG))
        {
            return true;
        }
        return false;
    }
    /**
     * Set layout.
     *
     * @param array $layout Layout
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_layout($layout)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_layout($layout));

        $this->_set_parameter('layout', json_encode($layout));

        // Make sure any cache data is refreshed
        $this->_set_parameter('registered_widgets_avail', NULL, TRUE);
    }

    /**
     * Set widget.
     *
     * @param int    $row row
     * @param int    $col column
     * @param string $controller controller
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_widget($row, $col, $controller)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->loaded)
            $this->_load();

        Validation_Exception::is_valid($this->validate_widget($row, $col, $controller));

        $layout = array();

        $field = 'layout';
        if ($this->username != NULL)
            $field .= '.' . $this->username;

        if (!empty($this->config[$field]))
            $layout = json_decode($this->config[$field], TRUE);

        $columns = $layout[$row]['columns'];

        foreach ($columns as $column => $info) {
            if ($col == $column) 
                $columns[$col] = array (
                    'controller' => $controller
                );
        }
        $layout[$row]['columns'] = $columns;

        $this->_set_parameter('registered_widgets_avail', NULL, TRUE);

        $this->set_layout($layout);
    }

    /**
     * Set widget.
     *
     * @param string $controller controller
     *
     * @return void
     * @throws Engine_Exception
     */

    function delete_widget($controller)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->loaded)
            $this->_load();

        Validation_Exception::is_valid($this->validate_widget($controller));

        $layout = array();

        $field = 'layout';
        if ($this->username != NULL)
            $field .= '.' . $this->username;

        if (!empty($this->config[$field]))
            $layout = json_decode($this->config[$field], TRUE);


        foreach ($layout as $row => $rowinfo) {
            foreach ($rowinfo['columns'] as $column => $colinfo) {
                if ($colinfo['controller'] == $controller) {
                    $layout[$row]['columns'][$column]['controller'] = 'dashboard/placeholder';
                    break;
                }
            }
        }

        $this->_set_parameter('registered_widgets_avail', NULL, TRUE);

        $this->set_layout($layout);
    }

    /**
     * Get max rows.
     *
     * @return int
     * @throws Engine_Exception
     */

    function get_max_rows()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->loaded)
            $this->_load();

        $field = 'max_rows';
        if ($this->username != NULL)
            $field .= '.' . $this->username;

        if (empty($this->config[$field]))
            return self::MAX_ROWS;

        return $this->config[$field];
    }

    /**
     * Set default layout.
     *
     * @return array
     * @throws Engine_Exception
     */

    function set_default_layout()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Setup a typical layout, 3 small widgets followed by 1 large width
        $layout = array(
            0 => array(
                'columns' => array(
                    0 => array (
                        'controller' => 'dashboard/placeholder'
                    ),
                    1 => array (
                        'controller' => 'dashboard/placeholder'
                    ),
                    2 => array (
                        'controller' => 'dashboard/placeholder'
                    )
                )
            ),
            1 => array(
                'columns' => array(
                    0 => array (
                        'controller' => 'dashboard/placeholder'
                    )
                )
            )
        );
        $this->set_layout($layout);
    }

    /**
     * Get layout.
     *
     * @return array
     * @throws Engine_Exception
     */

    function get_layout()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->loaded)
            $this->_load();

        $field = 'layout';
        if ($this->username != NULL)
            $field .= '.' . $this->username;

        if (empty($this->config[$field]))
            return NULL;
        else
            return json_decode($this->config[$field], TRUE);
    }

    /**
     * Get registered widgets.
     *
     * @param boolean $remove_active removes widgets that are already implemented in Dashboard
     *
     * @return array
     * @throws Engine_Exception
     */

    function get_registered_widgets($remove_active = TRUE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $widgets = 'registered_widgets' . ($remove_active ? '_avail' : '');
        if ($_SERVER['SERVER_PORT'] != 1501) {
            if (! $this->loaded)
                $this->_load();
            if (isset($this->config[$widgets]) && $this->config[$widgets] != NULL) {
                $json = json_decode($this->config[$widgets], TRUE);
                if ($json !== FALSE && (time() - $json['lastmod']) < self::CACHE_S)
                    return $json['data'];
            }
        }
        
        $master = array(
            lang('dashboard_select_widget') => array(
                -1 => array(
                    'title' => lang('base_select'),
                    'restricted' => FALSE
                )
            )
        );
        $app_list = clearos_get_apps();
        foreach ($app_list as $app) {
            // Re-init array
            if (!isset($app['dashboard_widgets']))
                continue;
            // Check that the UI package is installed (containing widget controller)
            $software = new Software("app-" . preg_replace("/_/", "-", $app['basename']));

            if (!$software->is_installed())
                continue;
            $master = array_merge_recursive($master, $app['dashboard_widgets']);
        }

        // Get in use widget set
        if ($remove_active) {
            foreach ($master as $category => $widget) {
                $in_use = array();
                $layout = $this->get_layout();
                foreach ($layout as $row => $meta) {
                    if (count($meta['columns']) == 0)
                        continue;
                    foreach ($meta['columns'] as $col) {
                        if (!preg_match('/.*\/placeholder$/', $col['controller']))
                            $in_use[] = $col['controller'];
                            //$in_use[] = preg_replace('/\//', '-', $col['controller']);
                    }
                }

                foreach ($widget as $controller => $meta) {
                    if ($controller && in_array($controller, $in_use))
                        unset($master[$category][$controller]);
                }
            }
        }

        $this->_set_parameter($widgets, json_encode(array('lastmod' => time(), 'data' => $master)), TRUE);

        return $master;
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Load and parse configuration files.
     *
     * @access private
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _load()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->loaded = FALSE;

        // Dashboard Config file
        $configfile = new Configuration_File(self::FILE_CONFIG);

        $this->config = $configfile->load();

        $this->loaded = TRUE;
    }

    /**
     * Generic set routine.
     *
     * @param string  $key    key name
     * @param string  $value  value for the key
     * @param boolean $global user or globally applied
     *
     * @return  void
     * @throws Engine_Exception
     */

    private function _set_parameter($key, $value, $global = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            if (!$global && $this->username != NULL)
                $key .= "." . $this->username;
            $file = new File(self::FILE_CONFIG, TRUE);
            $match = $file->replace_lines("/^$key\s*=.*/", "$key = $value\n");
            if (!$match)
                $file->add_lines("$key = $value\n");
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $this->loaded = FALSE;
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine for row layout.
     *
     * @param array $rows array
     *
     * @return mixed void if rows is OK, errmsg otherwise
     */

    public function validate_rows($rows)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (!is_array($rows) && !empty($rows))
            return lang('dashboard_invalid_layout');
        foreach ($rows as $columns) {
            if (!is_numeric($columns))
                return lang('dashboard_invalid_layout');
        }
    }

    /**
     * Validation routine for adding widget.
     *
     * @param int    $row row
     * @param int    $col col
     * @param string $controller controller
     *
     * @return mixed void if widget is OK, errmsg otherwise
     */

    public function validate_widget($row, $col, $controller)
    {
        clearos_profile(__METHOD__, __LINE__);
        // TODO...ACL
    }

    /**
     * Validation routine for layout.
     *
     * @param array $layout layout array
     *
     * @return mixed void if layout is OK, errmsg otherwise
     */

    public function validate_layout($layout)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (!is_array($layout) && !empty($layout))
            return lang('dashboard_invalid_layout');
        foreach ($layout as $row => $column) {
            foreach ($column['columns'] as $entry => $info) {
                if (empty($info['controller']))
                    return lang('dashboard_controller_missing');
            }
        }
    }

}
