<?php

/**
 * Widget Not Available controller.
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

class Widget_Not_Available extends ClearOS_Controller
{
    /**
     * Widget Not Available view.
     *
     * @param $basename the basename where the widget originates from
     *
     * @return view
     */

    function index($basename)
    {
        // Load libraries
        //---------------

        $this->lang->load('dashboard');
        $this->load->library('dashboard/Dashboard', array('username' => $this->session->userdata('username')));

        $data = array(
            'basename' => $basename
        );

        $this->page->view_form('dashboard/widget_not_available', $data, lang('dashboard_placeholder'), array('type' => MY_Page::TYPE_DASHBOARD));
    }
}
