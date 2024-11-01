<?php

class OnDateSorter {
    private $entries;

    public function __construct($repEntries) {
        $this->entries = $repEntries;
    }

    private function dateForId($id) {
        foreach($this->entries as $entry) {
            if ($entry['post_id'] == $id) {
                return $entry['on_date'];
            }
        }
        return 0;
    }

    public function compare_on_date($a, $b) {
        $da = $this->dateForId($a['answer_id']);
        $db = $this->dateForId($b['answer_id']);
        if ($da == $db) return 0;
        return ($da < $db) ? 1 : -1;
    }
}

?>
