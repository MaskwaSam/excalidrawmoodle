<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 */
class mod_excalidraw_mod_form extends moodleform_mod {

    /**
     * Define the form.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // General settings.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // Grading settings.
        $mform->addElement('header', 'gradingsettings', get_string('gradingsettings', 'excalidraw'));

        $mform->addElement('text', 'grade', get_string('grade'));
        $mform->setType('grade', PARAM_INT);
        $mform->setDefault('grade', 100);
        $mform->addHelpButton('grade', 'grade', 'excalidraw');

        // Standard coursemodule elements.
        $this->standard_coursemodule_elements();

        // Buttons.
        $this->add_action_buttons();
    }

    /**
     * Add any custom completion rules to the form.
     *
     * @return array
     */
    public function add_completion_rules() {
        $mform = $this->_form;
        return [];
    }

    /**
     * Determines if completion is enabled for this module.
     *
     * @param array $data
     * @return bool
     */
    public function completion_rule_enabled($data) {
        return false;
    }
}
