<?php

/**
 * Event listener for dispatched event
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\observers;

defined('MOODLE_INTERNAL') || die;

use core\event\base as baseevent;
use local_evokegame\customfield\mod_handler as extrafieldshandler;
use local_evokegame\util\game;
use local_evokegame\util\point;

class submissionsent {
    public static function observer(baseevent $event) {
        $handler = extrafieldshandler::create();

        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        if (!is_enrolled($event->get_context(), $event->relateduserid)) {
            return;
        }

        $cmid = $event->contextinstanceid;

        $data = $handler->export_instance_data_object($cmid);

        if (!preg_grep('/^submission_/', array_keys((array)$data))) {
            // For performance.
            return;
        }

        $points = new point($event->courseid, $event->relateduserid);

        foreach ($data as $skill => $value) {
            if (!$value || empty($value) || $value == 0) {
                continue;
            }

            if (substr($skill, 0, 11) != 'submission_') {
                continue;
            }

            // String submission_ length == 11.
            $submissionskill = substr($skill, 11);

            $points->add_points('module', 'submission', $event->contextinstanceid, $submissionskill, $value);
        }
    }
}
