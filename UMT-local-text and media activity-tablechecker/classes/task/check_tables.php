<?php
namespace local_tablechecker\task;

defined('MOODLE_INTERNAL') || die();

class check_tables extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('checktablestask', 'local_tablechecker');
    }

    public function execute() {
        global $DB;

        mtrace("--- Starting Table Checker Task (v6 - Definitive Unicode Fix) ---");

        $labels = $DB->get_records('label');
        mtrace("Found " . count($labels) . " total text and media activities to check.");

        foreach ($labels as $label) {
            $cm = get_coursemodule_from_instance('label', $label->id, 0, false, MUST_EXIST);
            if (!$cm) {
                continue;
            }

            mtrace("Processing activity ID: {$cm->id}, Name: '{$label->name}'...");

            $content = $label->intro;
            // The last modification time is stored in the label record itself.
            $timemodified = $label->timemodified; 

            $status = $this->get_table_status($content, $cm->id);
            $existing_record = $DB->get_record('local_tablechecker_status', ['cmid' => $cm->id]);

            if ($status === 'no_table') {
                mtrace(" -> Result: No table found. Deleting record if it exists.");
                if ($existing_record) {
                    $DB->delete_records('local_tablechecker_status', ['cmid' => $cm->id]);
                }
            } else {
                mtrace(" -> Result: Status is '{$status}'. Updating/inserting record.");
                if ($existing_record) {
                    $existing_record->status = $status;
                    $existing_record->timechecked = time();
                    $existing_record->timemodified = $timemodified; // Update the timemodified field
                    $DB->update_record('local_tablechecker_status', $existing_record);
                } else {
                    $new_record = new \stdClass();
                    $new_record->cmid = $cm->id;
                    $new_record->status = $status;
                    $new_record->timechecked = time();
                    $new_record->timemodified = $timemodified; // Set the timemodified field
                    $DB->insert_record('local_tablechecker_status', $new_record);
                }
            }
        }
        mtrace("--- Table Checker Task Finished ---");
    }

    private function is_cell_visually_empty(\DOMNode $node) {
        $content = $node->textContent;
        $decoded_content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        $no_nbsp_content = str_replace("\xC2\xA0", "", $decoded_content);
        $final_content = preg_replace('/\s/u', '', $no_nbsp_content);
        return empty($final_content);
    }

    private function get_table_status($html, $cmid) {
        if (empty(trim($html))) {
            return 'no_table';
        }
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xpath = new \DOMXPath($dom);
        if ($xpath->query('//table')->length === 0) {
            return 'no_table';
        }
        $rows = $xpath->query('//tr');
        if ($rows->length < 2) {
            mtrace(" (Debug CMID {$cmid}): Table found, but has fewer than 2 rows. Marking as empty.");
            return 'empty';
        }
        $data_cells_to_check = 0;
        $empty_data_cells = 0;
        foreach ($rows as $row) {
            $cells_in_row = $xpath->query('.//td', $row);
            if ($cells_in_row->length > 1) {
                $is_first_cell = true;
                foreach ($cells_in_row as $cell) {
                    if ($is_first_cell) {
                        $is_first_cell = false;
                        continue;
                    }
                    $data_cells_to_check++;
                    if ($this->is_cell_visually_empty($cell)) {
                        $empty_data_cells++;
                    }
                }
            }
        }
        mtrace(" (Debug CMID {$cmid}): Total data cells checked (ignoring first column): {$data_cells_to_check}");
        mtrace(" (Debug CMID {$cmid}): Empty data cells found: {$empty_data_cells}");
        if ($data_cells_to_check === 0) {
            mtrace(" (Debug CMID {$cmid}): No valid data cells found. Marking as 'empty'.");
            return 'empty';
        }
        $emptiness_percentage = $empty_data_cells / $data_cells_to_check;
        mtrace(" (Debug CMID {$cmid}): Emptiness percentage is: " . round($emptiness_percentage * 100) . "%");
        if ($emptiness_percentage > 0.90) {
            return 'empty';
        } else {
            return 'filled';
        }
    }
}