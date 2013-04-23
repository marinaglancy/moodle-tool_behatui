tool_behatui introduces features for changing the current theme and saving screenshots.
The main objective is to be able to see how particular screen looks in all/several themes.

Also it contains the .feature file for creating screenshots for all screens changed
during course listings refactoring in 2.5
See http://docs.moodle.org/dev/Courses_lists_upgrade_to_2.5

More about Acceptance testing: http://docs.moodle.org/dev/Acceptance_testing

First you need to define $CFG->behat_screenshots_dir in config.php
Screenshots for each test run will be stored in separate subdirectories.

Second, you can create .feature file. Example of scenario:

    Given I log in as "admin"
    When I am on homepage
    And I change browser size to 1024x768px
    Then I save a screenshot
    When I change frontpage display to Combo list
    And I change guest frontpage display to List of categories,Course search box
    And Repeat in themes "Afterburner,Anomaly,Arialist,Binarius,Boxxie,Brick,Formal white,FormFactor,Fusion,Leatherbound,Magazine,Nimble,Nonzero,Overlay,Serenity,Simple,Sky High,Splash,Standard (legacy),Standard":
      """
      When I am on homepage
      Then I save a screenshot as homepage_admin_{themename}
      And I log out
      And I save a screenshot as homepage_guest_{themename}
      And I log in as "admin"
      """
    And I change theme to MyMobile
    Then I save a screenshot as homepage_admin_{themename}
    And I log out
    And I save a screenshot as homepage_guest_{themename}

Please note:
You must be logged in as admin in order to change theme, repeat in themes and
change frontpage display. You don't have to be admin to take a screenshot.



Known bugs
----------

You need to have pretty wide browser window to change theme from Arialist to anything else.
It works if you resize the browser window in the beginning of the test.

You can not change the theme from MyMobile to anything else (therefore MyMobile test is
located in the end of example)