<?php

/**
 * Dashboard controller.
 *
 * @category   apps
 * @package    dashboard
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2014 ClearFoundation
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
 * @copyright  2014 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/dashboard/
 */

class Settings extends ClearOS_Controller
{
    /**
     * Dashboard summary view.
     *
     * @return view
     */

    function index()
    {
        $this->_view_edit('view');
    }

    /**
     * Dashboard settings edit controller
     *
     * @return view
     */

    function edit()
    {
        $this->_view_edit('edit');
    }

    /**
     * Dashboard view/edit controller
     *
     * @param string $mode mode
     *
     * @return view
     */

    function _view_edit($mode = NULL)
    {
        // Load libraries
        //---------------

        $this->lang->load('dashboard');
        $this->load->library('dashboard/Dashboard', array('username' => $this->session->userdata('username')));

        // Set validation rules
        //---------------------

        $this->form_validation->set_policy('layout', 'dashboard/Dashboard', 'validate_rows', TRUE);

        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $layout = array();
                // Get the old layout
                $layout_old = $this->dashboard->get_layout();
                // Get the new layout
                $rows = $this->input->post('layout');
                // This contains just the number of rows/columns in each
                // Need to go through and fetch any controllers defined in old layout
                foreach($rows as $row => $columns) {
                    for ($col = 0; $col < $columns; $col++) {
                        if (isset($layout_old[$row]['columns'][$col]['controller']))
                            $layout[$row]['columns'][$col]['controller'] = $layout_old[$row]['columns'][$col]['controller'];
                        else
                            $layout[$row]['columns'][$col]['controller'] = "dashboard/placeholder";
                    }
                }
                $this->dashboard->set_layout($layout);
                $this->page->set_status_updated();
                redirect('/dashboard');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //---------------
        $data = array (
            'mode' => $mode,
            'max_rows' => $this->dashboard->get_max_rows(),
            'layout' => $this->dashboard->get_layout()
        );

        // Load views
        //-----------

        $this->page->view_form('dashboard/settings', $data, lang('base_settings'));

    }

    /**
     * Set a dashboard widget
     *
     * @return void
     */

    function set_widget()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        // Load libraries
        //---------------

        $this->lang->load('dashboard');
        $this->load->library('dashboard/Dashboard', array('username' => $this->session->userdata('username')));

        try {
            list($row, $col) = explode('-', preg_replace('/grid-/', '', $this->input->post('grid')));
            $my_controller = $this->input->post('controller');
            $this->dashboard->set_widget($row, $col, $my_controller);
            echo json_encode(array('code' => 0, 'errmsg' => ''));
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Delete a dashboard widget
     *
     * @return void
     */

    function delete_widget()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        // Load libraries
        //---------------

        $this->lang->load('dashboard');
        $this->load->library('dashboard/Dashboard', array('username' => $this->session->userdata('username')));

        try {
            $my_controller = preg_replace(array('/ci_/', '/-/'), array('', '/'), $this->input->post('controller'));
            $this->dashboard->delete_widget($my_controller);
            echo json_encode(array('code' => 0, 'errmsg' => ''));
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Reorder a row's widget set
     *
     * @return view
     */

    function reorder()
    {

        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        // Load libraries
        //---------------

        $this->lang->load('dashboard');
        $this->load->library('dashboard/Dashboard', array('username' => $this->session->userdata('username')));

        // The controller index orders controllers on a per row basis
        try {
            $ci = json_decode($this->input->post('controllers'));
            $row = $this->input->post('row');
            foreach ($ci as $col => $url) {
                // A URL with a plain integer is just a placeholder
                if (is_numeric(substr($url, 3)))
                    $this->dashboard->set_widget($row, $col, 'dashboard/placeholder');
                else
                    $this->dashboard->set_widget($row, $col, preg_replace('/-/', '/', substr($url, 3)));
            }

            echo json_encode(array('code' => 0, 'errmsg' => ''));
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Dashboard setup default layout.
     *
     * @return void
     */

    function default_layout()
    {
        // Load libraries
        //---------------

        $this->load->library('dashboard/Dashboard', array('username' => $this->session->userdata('username')));

        $this->dashboard->set_default_layout();
        redirect('dashboard');
    }

}
