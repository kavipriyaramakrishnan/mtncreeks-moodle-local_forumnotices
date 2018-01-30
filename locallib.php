<?php
/*
 * MtnCreeks Forum Notices
 *
 * Local library function
 *
 * @package    : local_forumnotices
 * @copyright  : 2017 Pukunui
 * @author     : Priya Ramakrishnan, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/*
 * Get the list of all forum notices 
 *
 * @uses $DB
 * @return $table html group type list
 */
function local_forumnotices_list() {
    global $DB, $CFG;
    // Create the table headings.
    $table = new html_table();
    $table->width = '100%';
    // Set the row heading object.
    $row = new html_table_row();
    // Create the cell.
    $cell = new html_table_cell();
    $cell->header = true;
    $cell->text = get_string('subject', 'local_forumnotices');
    $cell->style = 'text-align:left';
    $row->cells[] = $cell;
    // Create the cell.
    $cell = new html_table_cell();
    $cell->header = true;
    $cell->text = get_string('message', 'local_forumnotices');
    $cell->style = 'text-align:left';
    $row->cells[] = $cell;
    // Create the cell.
    $cell = new html_table_cell();
    $cell->header = true;
    $cell->text = get_string('user', 'local_forumnotices');
    $cell->style = 'text-align:left';
    $row->cells[] = $cell;
    // Create the cell.
    $cell = new html_table_cell();
    $cell->header = true;
    $cell->text = get_string('action', 'local_forumnotices');
    $cell->style = 'text-align:left';
    $row->cells[] = $cell;
    $table->data[] = $row;
    $sql = "SELECT fn.id, 
            fn.subject,
            fn.message, 
      	    CONCAT(u.firstname, ' ', u.lastname) as user
            FROM {local_forumnotices} fn
            JOIN {user} u
	        ON u.id = fn.userid 
            WHERE fn.pastnotices = 0
      	    ORDER BY 2";    
    $forumlist = $DB->get_records_sql($sql);
    foreach ($forumlist as $fl) {
       $editlink = "<a href='$CFG->wwwroot/local/forumnotices/forumnotices.php?id=$fl->id&action=edit'>".
                    get_string('edit', 'local_forumnotices')."</a>";
       $deletelink = "<a href='$CFG->wwwroot/local/forumnotices/forumnotices.php?id=$fl->id&action=delete'>".
                    get_string('delete', 'local_forumnotices')."</a>";
       // Set the row heading object.
        $row = new html_table_row();
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = $fl->subject;
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = $fl->message;
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = $fl->user;
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = $editlink;
        $row->cells[] = $cell;
        // Add header to the table.
        $table->data[] = $row; 
    }
    // Add to the table.
    echo  html_writer::table($table);
}

/*
 * To print the forum notices
 *
 * @uses $DB
 * @return $table html group type list
 */
function local_forumnotices_printforum($frmids) {
    global $DB, $CFG, $USER;
    $config = get_config('local_forumnotices');
    $courseid = $config->courses;
    $table = '';
    // Print the header
    $header = '<table border="1" border-width="thin">
                <tr>
                <th align="center" width="15%">'.get_string('subject', 'local_forumnotices').'</th>
                <th align="center" width="75%">'.get_string('message', 'local_forumnotices').'</th>
                <th align="center" width="10%">'.get_string('user', 'local_forumnotices').'</th>
                </tr>';
    $sql = "SELECT fn.id,
            fn.subject,
            fn.message,
            fn.forums,
            CONCAT(u.firstname, ' ', u.lastname) as user
            FROM {local_forumnotices} fn
            JOIN {user} u
            ON u.id = fn.userid
            WHERE fn.pastnotices = 0
            ORDER BY 2";
    $forumlist = $DB->get_records_sql($sql);
    $sql = "SELECT id, name
            FROM {forum}
            WHERE name NOT IN ('Announcements','Past Notices', 'Staff Notices')
            AND course = $courseid";
    if (!empty($frmids)) {
        $sql .= " AND id IN ($frmids) ";
    }
    $sql .= " ORDER BY id";
    $forums = $DB->get_records_sql_menu($sql);
    foreach ($forums as $flk => $fld) {
       $index = 0;
       foreach ($forumlist as $fl) {
           $forumarray = explode(",", $fl->forums);
           if (in_array($flk, $forumarray)) {
               $classarray[$fld][$index] = $fl->id;
           }
           $index++;
       }
       
    }
    
    require_once("$CFG->libdir/pdflib.php");
    $documentname = 'forumnotices.pdf';
    $pdf = new TCPDF('p', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetTitle($documentname);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    $font = 'helvetica';
    $pdf->setFont($font, '', 10);
    $arraykeys = array_keys($classarray);
    $index = 0;
    $today = date('d-m-Y');
    foreach ($classarray as $cla) {
       $pdf->AddPage();
       $pdf->writeHTMLCell(0, 0, 10, 10, $arraykeys[$index], '');
       $pdf->writeHTMLCell(0, 0, 150, 10, 'Date:'.$today, '');
       $table .= $header;
       foreach ($cla as $clk => $cld) {
          $noticerecord = $DB->get_record('local_forumnotices', array('id' => $cld));
          $userdet = $DB->get_record_sql("SELECT CONCAT(firstname, lastname) as name FROM {user} WHERE id = $noticerecord->userid");
          $table .= '<tr>
                     <td width="15%">'. $noticerecord->subject .'</td>
                     <td width="75%">'. $noticerecord->message .'</td>
                     <td width="10%" align="center">'. $userdet->name .'</td>
                     </tr>
                     ';
       }
       $index++;
       $table .= "</table>";
       $pdf->SetXY(10, 20);
       $pdf->writeHTML($table, true, false, true, false, "");
    }
     
    //$pdf->writeHTML($table, true, false, true, false, "");
    $pdf->Output($documentname, 'I');
}

/*
 * To print the forum notices
 *
 * @uses $DB
 * @return $table html group type list
 */
function local_forumnotices_printstaffnotices() {
    global $DB, $CFG, $USER;
    $config = get_config('local_forumnotices');
    $courseid = $config->courses;
    $table = '';
    // Print the header
    $header = '<table border="1" border-width="thin">
        <tr>
        <th align="center" width="15%">'.get_string('subject', 'local_forumnotices').'</th>
        <th align="center" width="75%">'.get_string('message', 'local_forumnotices').'</th>
        <th align="center" width="10%">'.get_string('user', 'local_forumnotices').'</th>
        </tr>';

    $sql = "SELECT fn.id,
        fn.subject,
        fn.message,
        fn.forums,
        CONCAT(u.firstname, ' ', u.lastname) as user
            FROM {local_forumnotices} fn
            JOIN {user} u
            ON u.id = fn.userid
            WHERE fn.pastnotices = 0
            ORDER BY 2";
    $forumlist = $DB->get_records_sql($sql);
    $sql = "SELECT id, name
            FROM {forum}
            WHERE name IN ('Staff Notices')
            AND course = $courseid
            ORDER BY id";
    $forums = $DB->get_records_sql_menu($sql);
    foreach ($forums as $flk => $fld) {
        $index = 0;
        foreach ($forumlist as $fl) {
            $forumarray = explode(",", $fl->forums);
            if (in_array($flk, $forumarray)) {
                $classarray[$fld][$index] = $fl->id;
            }
            $index++;
        }

    }

    require_once("$CFG->libdir/pdflib.php");
    $documentname = 'staffnotices.pdf';
    $pdf = new TCPDF('p', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetTitle($documentname);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    $font = 'helvetica';
    $pdf->setFont($font, '', 10);
    $arraykeys = array_keys($classarray);
    $index = 0;
    $today = date('d-m-Y');
    foreach ($classarray as $cla) {
        $pdf->AddPage();
        $pdf->writeHTMLCell(0, 0, 10, 10, $arraykeys[$index], '');
        $pdf->writeHTMLCell(0, 0, 150, 10, 'Date:'.$today, '');
        $table .= $header;
        foreach ($cla as $clk => $cld) {
            $noticerecord = $DB->get_record('local_forumnotices', array('id' => $cld));
            $userdet = $DB->get_record_sql("SELECT CONCAT(firstname, lastname) as name FROM {user} WHERE id = $noticerecord->userid");
            $table .= '<tr>
                       <td width="15%">'. $noticerecord->subject .'</td>
                       <td width="75%">'. $noticerecord->message .'</td>
                       <td width="10%">'. $userdet->name .'</td>
                       </tr>
                       ';
        }
        $index++;
        $table .= "</table>";
        $pdf->SetXY(10, 20);
        $pdf->writeHTML($table, true, false, true, false, "");
    }

    //$pdf->writeHTML($table, true, false, true, false, "");
    $pdf->Output($documentname, 'I');
}
