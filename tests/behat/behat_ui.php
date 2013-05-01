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
        if (preg_match('/\{lang\}/', $filename)) {
            $filename = preg_replace('/\{lang\}/', $this->find('css', 'html')->getAttribute('lang'), $filename);
        }
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

        if (($subpath = dirname($filename)) && !file_exists($filepath. DIRECTORY_SEPARATOR . $subpath)) {
            mkdir($filepath. DIRECTORY_SEPARATOR . $subpath, 0777, true);
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
        // Go to "Theme selector" page for Default device
        $this->getSession()->visit($this->locate_path('/theme/index.php?device=default'));

        // Find table cell containing theme name and click button "Use theme" in this cell
        $rownode = $this->find('xpath', "//td[.//h3='" . str_replace("'", "\'", $themename) . "']");
        list($selector, $locator) = $this->transform_selector("button", "Use theme");
        $elementnode = $this->find($selector, $locator, false, $rownode);
        try {
            $elementnode->click();
        } catch (Exception $e) {
            // we could not click button because it is invisible. Submit form using Javascript
            $formxpath = $this->find('css', 'form', false, $rownode)->getXpath();
            $jscode = 'document.evaluate("'.str_replace("'", "\'", $formxpath).'" ,document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null ).singleNodeValue.submit();';
            $this->getSession()->executeScript($jscode);
        }

        // Return to homepage
        $this->getSession()->visit($this->locate_path('/'));

        self::$lasttheme = $themename;
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
     * On /my/ page clicks on all activity types to expand them (to be used for screenshot)
     *
     * @Given /^I expand all course overviews$/
     */
    public function i_expand_all_course_overviews() {
        if ($links = $this->getSession()->getPage()->findAll('css', '.collapsibleregion .collapsibleregioncaption')) {
            foreach ($links as $link) {
                $link->click();
                $this->getSession()->wait(2 * 1000, false);
            }
        }
        return true;
    }

    /**
     * Installs the language pack, use 2-letter code here (may be several values comma-separated). Must be admin
     *
     * @When /^I install languages "(.*)"$/
     */
    public function i_install_languages($lang) {
        $langlist = array_diff(preg_split('/\s*,\s*/', trim($lang), 0, PREG_SPLIT_NO_EMPTY), array('en'));
        if (empty($langlist)) {
            return;
        }
        $this->getSession()->visit($this->locate_path('/admin/tool/langimport/index.php'));
        $selectnode = $this->find_field("List of available languages");
        foreach (preg_split('/\s*,\s*/', trim($lang), 0, PREG_SPLIT_NO_EMPTY) as $l) {
            $this->getSession()->getDriver()->selectOption($selectnode->getXpath(), $l, true);
        }
        $button = $this->find_button("Install selected language pack");
        $button->press();
        $this->getSession()->wait(5 * 1000, false);
        $this->getSession()->visit($this->locate_path('/'));
    }

    /**
     * Sets the current language, use 2-letter code here.
     *
     * Please note that the most of commands will not work in another language,
     * this directive is used primarily for saving a screenshots and going back to English.
     *
     * @When /^I change language to (\w\w)$/
     */
    public function i_change_language_to($lang) {
        if ($this->find('css', 'html')->getAttribute('lang') === $lang) {
            return;
        }
        if (!($langbar = $this->getSession()->getPage()->find('css', 'select.langmenu'))) {
            // page does not have a language selector
            $this->fast_change_language($lang);
            return;
        }
        $this->getSession()->getDriver()->selectOption($langbar->getXpath(), $lang, false);
        if ($this->running_javascript()) {
           $langbar->click();
        }
        sleep(2);
        $this->getSession()->wait(self::TIMEOUT, '(document.readyState === "complete")');
    }

    /**
     * Changes the current language
     *
     * Generally It's better to use "When I change language to XX" but sometimes the select.langmenu is not displayed
     *
     * @param string $lang
     */
    private function fast_change_language($lang) {
        if ($this->find('css', 'html')->getAttribute('lang') === $lang) {
            return;
        }
        $href = $this->getSession()->getDriver()->getCurrentUrl();
        $url = new moodle_url($href);
        $url->param('lang', $lang);
        $this->getSession()->getDriver()->visit($url->out(false));
    }

    /**
     * Saves screenshot in several languages with specified filename, resets language to English afterwards
     *
     * @Then /^I save screenshots in languages "(.*?)" as (.*)$/
     * @param string $filename
     */
    public function i_save_screenshots_in_languages_as($langs, $filename) {
        $langs = preg_split("/\s*,\s*/", $langs, 0, PREG_SPLIT_NO_EMPTY);
        foreach ($langs as $lang) {
            $this->fast_change_language($lang);
            $this->save_a_screenshot_as($filename);
        }
        $this->fast_change_language('en');
    }
}
