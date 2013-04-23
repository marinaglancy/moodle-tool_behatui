<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Making screenshots and other functions for testing course listings UI
 *
 * @package   core
 * @category  test
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given,
        Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
        Behat\Gherkin\Node\PyStringNode as PyStringNode;

/**
 * Making screenshots and other functions for testing course listings UI
 *
 * $CFG->behat_screenshots_dir must be specified in config.php
 *
 * @package   core
 * @category  test
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_ui extends behat_base {
    static $lasttheme = 'Standard';

    /**
     * Saves screenshot with default filename
     *
     * @Then /^I save a screenshot$/
     * @param string $filename
     */
    public function i_save_a_screenshot() {
        $this->save_a_screenshot_as(null);
    }

    /**
     * Saves screenshot with specified filename
     *
     * @Then /^I save a screenshot as (.*)$/
     * @param string $filename
     */
    public function i_save_a_screenshot_as($filename) {
        $this->save_a_screenshot_as($filename);
    }

    /**
     * Saves screenshot with default filename
     *
     * @Then /^Save a screenshot$/
     * @param string $filename
     */
    public function save_a_screenshot() {
        $this->save_a_screenshot_as(null);
    }

    /**
     * Saves screenshot with specified filename
     *
     * @Then /^Save a screenshot as (.*)$/
     * @param string $filename
     */
    public function save_a_screenshot_as($filename) {
        global $CFG;
        if (empty($CFG->behat_screenshots_dir)) {
            // no error, just skip making screenshots
            return true;
        }

        // get filepath unique for current test run
        static $filepath = null;
        if ($filepath === null) {
            if (!is_writable($CFG->behat_screenshots_dir)) {
                throw coding_exception('$CFG->behat_screenshots_dir is not writtable');
            }
            $path = date('Y-m-d_H:i') . '_';
            for ($cnt = 0; $cnt < 10000; $cnt++) {
                $filepath = $CFG->behat_screenshots_dir . DIRECTORY_SEPARATOR . $path. sprintf('%04d', $cnt);
                if (!file_exists($filepath)) {
                    break;
                }
            }
            mkdir($filepath);
        }

        $filename = preg_replace('/\{themename\}/', self::$lasttheme, $filename);
        // find unused file name
        $filename = trim($filename);
        if (!empty($filename) && !preg_match('/\.png$/i', $filename)) {
            $filename .= '.png';
        }
        if (empty($filename) || file_exists($filepath.DIRECTORY_SEPARATOR.$filename)) {
            if (empty($filename)) {
                // default filename should always have _0000 postfix
                $filename = 'screenshot.png';
            }
            for ($cnt = 0; $cnt < 10000; $cnt++) {
                $newfilename = preg_replace('/(\.png)$/i', '_'.  sprintf('%03d', $cnt). '$1', $filename);
                if (!file_exists($filepath. DIRECTORY_SEPARATOR. $newfilename)) {
                    break;
                }
            }
            $filename = $newfilename;
        }

        // save screenshot
        $this->wait(self::TIMEOUT, '(document.readyState === "complete")');
        $this->saveScreenshot($filename, $filepath);
        return true;
    }

    /**
     * Changes the site theme (default device) and proceeds to homepage
     *
     * @When /^I change theme to (.*)$/
     * @param string $themename
     */
    public function i_change_theme_to($themename) {
        if (self::$lasttheme === $themename) {
            return true;
        }
        self::$lasttheme = $themename;
        return array(
            new Given('I am on homepage'),
            new Given('I expand "Site administration" node'),
            new Given('I expand "Appearance" node'),
            new Given('I expand "Themes" node'),
            new Given('I follow "Theme selector"'),
            new Given('I press "Change theme"'),
            new Given('I click on "Use theme" "button" in the "'.$themename.'" table row'),
            new Given('I am on homepage')
        );
    }

    /**
     * Loops throught themes and repeat sequence of commands in each of them
     *
     * @Then /^Repeat in themes "([^"]*)":$/
     */
    public function repeat_in_themes($themes, PyStringNode $commands)
    {
        $rv = array();
        foreach (preg_split('/\s*,\s*/', $themes, 0, PREG_SPLIT_NO_EMPTY) as $theme) {
            $rv[] = new Given("I change theme to $theme");
            foreach (preg_split('/\s*\n\s*/', $commands->getRaw(), 0, PREG_SPLIT_NO_EMPTY) as $command) {
                $command = preg_replace('/[\s]*\#.*$/', '', $command);
                $command = preg_replace(array('/^When /','/^Given /','/^Then /','/^And /'), '', $command);
                if (strlen(trim($command))) {
                    $rv[] = new Given($command);
                }
            }
        }
        $this->wait(self::TIMEOUT);
        return $rv;
    }

    /**
     * Waits with the provided params if we are running a JS session.
     *
     * @param int $timeout
     * @param string $javascript
     * @return void
     */
    protected function wait($timeout, $javascript = false) {
        if ($this->running_javascript()) {
            $this->getSession()->wait($timeout, $javascript);
        }
    }

    /**
     * Changes the $CFG->frontpageloggedin
     *
     * @When /^I change frontpage display to (.*)$/
     * @param string $value
     */
    public function i_change_frontpage_display_to($value) {
        $this->change_admin_multiselect('frontpageloggedin', $value);
    }

    /**
     * Changes the $CFG->frontpage
     *
     * @When /^I change guest frontpage display to (.*)$/
     * @param string $value comma-separated list of frontpage sections
     */
    public function i_change_guest_frontpage_display_to($value) {
        $this->change_admin_multiselect('frontpage', $value);
    }

    /**
     * Changes value in admin setting that consists of several select elements
     * 
     * @param string $label either 'frontpage' or 'frontpageloggedin'
     * @param string $value comma-separated list of frontpage sections
     */
    private function change_admin_multiselect($label, $value) {
        // Unfortunately we can't use behat_admin::i_set_the_following_administration_settings_values() here because the structure is different

        // We expect admin block to be visible, otherwise go to homepage.
        if (!$this->getSession()->getPage()->find('css', '.block_settings')) {
            $this->getSession()->visit($this->locate_path('/'));
            $this->wait(self::TIMEOUT, '(document.readyState === "complete")');
        }

        // Search by label.
        $searchbox = $this->find_field('Search in settings');
        $searchbox->setValue($label);
        $submitsearch = $this->find('css', 'form.adminsearchform input[type=submit]');
        $submitsearch->press();

        $this->wait(self::TIMEOUT, '(document.readyState === "complete")');

        // Admin settings does not use the same DOM structure than other moodle forms
        // but we also need to use lib/behat/form_field/* to deal with the different moodle form elements.
        $exception = new ElementNotFoundException($this->getSession(), '"' . $label . '" administration setting ');

        $values = preg_split('/\s*,\s*/', $value, 0, PREG_SPLIT_NO_EMPTY);
        $fieldnodes = $this->find_all('css', "#admin-$label select", $exception);
        $i = 0;
        foreach ($fieldnodes as $fieldnode) {
            $field = behat_field_manager::get_field_instance('select', $fieldnode, $this->getSession());
            $field->set_value(isset($values[$i]) ? $values[$i] : 'None');
            $i++;
        }

        $this->find_button('Save changes')->press();
    }

    /**
     * Changes browser window size
     *
     * Example: I change browser size to 1024x768px (all non-digits will be ignored or treated as separators)
     *
     * @Given /^I change browser size to ([\d]+)[^\d]+([\d]+)[^\d]*$/
     * @param string $width
     * @param string $height
     */
    public function i_change_browser_size_to($width, $height) {
        $this->getSession()->resizeWindow((int)$width, (int)$height, null);
        return true;
    }

    /**
     * @Given /^I expand all course overviews$/
     */
    public function i_expand_all_overviews() {
        $links = $this->find_all('css', '.collapsibleregion .collapsibleregioncaption > a');
        foreach ($links as $link) {
            $link->click();
            sleep(2);
        }
        return true;
    }
}
