<?php

/**
 * Dashboard controller.
 *
 * @category   apps
 * @package    dashboard
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/dashboard/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Dashboard controller.
 *
 * @category   apps
 * @package    dashboard
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/dashboard/
 */

class Dashboard extends ClearOS_Controller
{
    /**
     * Dashboard summary view.
     *
     * @return view
     */

    function index()
    {
        // Load libraries
        //---------------

        $this->lang->load('dashboard');
        $this->load->library('dashboard/Dashboard', array('username' => $this->session->userdata('username')), 'my_dashboard');
        // Load controllers
        //-----------------

        $data = array(
            'rows' => $this->my_dashboard->get_max_rows(),
            'layout' => $this->my_dashboard->get_layout()
        );

        $index = 0;
        // Verify to know if the configuration file exist
        $data['file_conf_exist'] = false;
        if($this->my_dashboard->configuration_file_exist())
        {
            $data['file_conf_exist'] = true;
        }
        else
        {
            $this->my_dashboard->set_default_layout();
        }
        // Get registered widgets...an app may have been removed, taking widget with it.
        $registered = $this->my_dashboard->get_registered_widgets(FALSE);

        $available_controllers = array();
        foreach ($registered as $registered_widget) {
            $available_controllers = array_merge($available_controllers, array_keys($registered_widget));
        }
        foreach ($data['layout'] as $row_num => $row) {
            foreach ($row['columns'] as $col => $meta) {
                if (isset($meta['controller'])) {
                    $parts = explode('/', $meta['controller']);
                    if (!in_array($meta['controller'], $available_controllers) && !preg_match('/\/placeholder$/', $meta['controller'])) {
                        // Insert deleted app placeholder
                        $dashboard_widgets[] = array(
                            'controller' => 'dashboard/widget_not_available',
                            'method' => 'index',
                            'params' => $parts[0]
                        );
                    } else {
                        $dashboard_widgets[] = array(
                            'controller' => $parts[0] . '/' . $parts[1],
                            'method' => (isset($parts[2]) ? $parts[2] : 'index'),
                            'params' => $row_num . '-' . $col . '-' . count($row['columns'])
                        );
                    }
                    $data['layout'][$row_num]['columns'][$col]['controller_index'] = $index;
                    $index++;
                }
            }
        }

        if (!empty($dashboard_widgets)) {
            $data['widgets'] = $this->page->view_controllers($dashboard_widgets, lang('dashboard_app_name'), array('type' => MY_Page::TYPE_DASHBOARD_WIDGET));

            // Load default helper javascript files from app widget
            foreach ($dashboard_widgets as $widget) {
                $basename = preg_replace('/\/.*/', '', $widget['controller']);

                // Skip internal dashboard widgets, javascript already included
                if ($basename === 'dashboard')
                    continue;

                // Add javascript if it exists
                $javascript_path = clearos_app_base($basename) . '/htdocs/' . $basename . '.js.php' ;
                if (file_exists($javascript_path))
                    $options['javascript'][] = clearos_app_htdocs($basename) . '/' . $basename . '.js.php';
            }
        }

        // KLUDGE: hook in reports javascript
        $report_engine = clearos_driver('reports');

        if (!empty($report_engine))
            $options['javascript'][] = clearos_app_htdocs($report_engine) . '/' . $report_engine . '.js.php';

        // Add settings and delete widget to breadcrumb trail
        $options['breadcrumb_links'] = array(
            'settings' => array('url' => '/app/dashboard/settings', 'tag' => lang('base_settings')),
            'delete' => array('url' => '#', 'tag' => lang('base_delete'), 'class' => 'dashboard-delete'),
            'restart' => array('url' => '/app/base/shutdown/confirm/restart','tag' => lang('dashboard_restart'), 'class' => '' )
        );
        $options['type'] = MY_Page::TYPE_DASHBOARD;

        $this->page->view_form('dashboard/canvas', $data, lang('dashboard_app_name'), $options);
    }
}
