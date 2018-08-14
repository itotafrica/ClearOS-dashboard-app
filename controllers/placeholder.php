<?php

/**
 * Placeholder controller.
 *
 * @category   apps
 * @package    dashboard
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2015 ClearFoundation
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
 * Placeholder controller.
 *
 * @category   apps
 * @package    dashboard
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2015 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/dashboard/
 */

class Placeholder extends ClearOS_Controller
{
    /**
     * Placeholder view.
     *
     * @param $position table position
     *
     * @return view
     */

    function index($position = NULL)
    {
        // Load libraries
        //---------------

        $this->lang->load('dashboard');
        $this->load->library('dashboard/Dashboard', array('username' => $this->session->userdata('username')));

        $data = array(
            'row' => NULL,
            'col' => NULL,
        );

        if ($position != NULL)
            list($data['row'], $data['col']) = preg_split('/-/', $position);

        // This is a bit odd.  In CodeIgniter 3.1.x, the name of the Dashboard object created in $this->load->library()
        // is set set to my_dashboard.  This custom object name was registered in index.php.
        $options = $this->my_dashboard->get_registered_widgets();

        foreach ($options as $category => $widget) {
            foreach ($widget as $controller => $option) {
                // TODO - Hardcode root.  What if superuser changes?  What about ACL override?
                if ($option['restricted'] && $this->session->userdata('username') != 'root')
                    continue;
                $data['widget_options'][$category][$controller] = $option['title'];
            }
        }
            
        $this->page->view_form('dashboard/placeholder', $data, lang('dashboard_placeholder'), array('type' => MY_Page::TYPE_DASHBOARD));
    }
}
