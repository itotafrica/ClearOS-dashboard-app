<?php

/**
 * Dashboard view.
 *
 * @category   apps
 * @package    dashboard
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011-2013 ClearFoundation
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
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('dashboard');
if ($layout == NULL)
    echo infobox_info(
        lang('dashboard_no_widget'),
        lang('dashboard_configure'),
        array('buttons' =>
            array(
                anchor_custom('/app/dashboard/settings', lang('base_configure')),
                anchor_custom('/app/dashboard/settings/default_layout', lang('base_use_default'))
            )
        )
    );
foreach ($layout as $row => $meta) {
    if (count($meta['columns']) == 0)
        continue;
    echo row_open(count($meta['columns']) > 1 ? array('id' => 'row_' . $row, 'class' => 'grid') : NULL);
    // Based on Bootstrap 12 columns grid, take 12 / colnum
    foreach ($meta['columns'] as $col) {
        $id = 'ci_' . preg_replace('/\//', '-', $col['controller']);
        $add_class = ' db-widget';
        if (preg_match('/.*\/placeholder$/', $col['controller'])) {
            $id = 'ci_' . rand();
            $add_class = ' placeholder';
        }
        echo column_open(12 / count($meta['columns']), NULL, NULL, array('id' => $id, 'class' => 'sortable' . $add_class));
        echo $widgets[$col['controller_index']];
        echo column_close();
    }
    echo row_close();
}
