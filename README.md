tool_behatui introduces commands for changing the current theme and saving screenshots.
The main objective is to be able to see how particular screen looks in all/several themes.

In order to use this plugin you need to install Behat and run Selenium.
More about Acceptance testing: http://docs.moodle.org/dev/Acceptance_testing

Testing theme for changes in Course listings in 2.5
---------------------------------------------------

See http://docs.moodle.org/dev/Courses_lists_upgrade_to_2.5 for changes in HTML/CSS in
course listings.

1. Install this plugin to **ROOTDIR/admin/tool/behatui/**
2. Set up environment for running Behat tests: http://docs.moodle.org/dev/Acceptance_testing#Quick_start (you only need items 1-8).
3. Add **$CFG->behat_screenshots_dir = '/path/to/dir';** to your config.php with the path to writable directory
   where screenshots will be stored (separate subfolder will be created for each test run)
4. Copy the file **[tests/behat/courses_list.feature.example](tests/behat/courses_list.feature.example)**
   to **tests/behat/courses_list.feature**.
   This test generates plenty of categories and courses, changes the default settings so
   the pagination is displayed more often, runs through all affected screens and saves
   screenshots.
5. In this file modify the "Examples" section in the very bottom to specify
   the list of languages and themes that you want to test.
   Note that there is a commented example with all core themes.
6. Run test from moodle root dir (ROOTDIR):

        php admin/tool/behat/cli/util.php --enable
        vendor/bin/behat --config ROOTDIR/behatdata/behat/behat.yml --tags @core_course_list

Creating new .feature files
---------------------------

This plugin may also be used for creating other screenshots.

Repeat steps 1-3 from previous section to install and configure.

Now you can create new .feature files using commands defined in [tests/behat/behat_ui.php](tests/behat/behat_ui.php).
Example of scenario:

    Given I log in as "admin"
    When I am on homepage
    And I change browser size to 1024x768px
    Then I save a screenshot
    When I change frontpage display to Combo list
    And I change guest frontpage display to List of categories,Course search box
    And I install languages "he,fr"
    And Repeat in themes "Afterburner,Anomaly,Arialist,Binarius,Boxxie,Brick,Clean,Formal white,FormFactor,Fusion,Leatherbound,Magazine,Nimble,Nonzero,Overlay,Serenity,Sky High,Splash,Standard (legacy),Standard":
      """
      When I am on homepage
      Then I save a screenshot as homepage_admin_{themename}
      And I log out
      And I save screenshots in languages "en,he,fr" as homepage_guest_{lang}_{themename}
      And I log in as "admin"
      """
    And I change language to fr
    And I save a screenshot as another_fr_screenshot
    And I change language to en
    And I change browser size to 600x768px
    And I change theme to MyMobile
    Then I save a screenshot as homepage_admin_{themename}
    And I log out
    And I save a screenshot as homepage_guest_{themename}

Please note:
You must be logged in as admin in order to change theme, repeat in themes and
change frontpage display. You don't have to be admin to save a screenshot,
change browser size and/or change language.

You can only use single-line commands inside "Repeat in themes".
