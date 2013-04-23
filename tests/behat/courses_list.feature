@core_course_list
Feature: View course lists
  In order to find out about moodle courses
  As a moodle user
  I need to be able to see course lists

  Background:
    Given the following "categories" exists:
      | name | category | idnumber |
      | Cat 1 (many subcategories, no courses) | 0 | CAT1 |
      | Cat 2 (no subcategories, many courses) | 0 | CAT2 |
      | Cat 3 with very long name that to test how it is wrapped into several lines. Cat 3 with very long name that to test how it is wrapped into several lines. Cat 3 with very long name that to test how it is wrapped into several lines. | 0 | CAT3 |
      | Cat 4 | CAT1 | CAT4 |
      | Cat 5 | CAT1 | CAT5 |
      | Cat 6 | CAT1 | CAT6 |
      | Cat 7 | CAT1 | CAT7 |
      | Cat 8 | CAT1 | CAT8 |
      | Cat 9 | CAT1 | CAT9 |
      | Cat 10 | CAT1 | CAT10 |
      | Cat 11 | CAT1 | CAT11 |
      | Cat 12 | CAT1 | CAT12 |
      | Cat 13 | CAT5 | CAT13 |
      | Cat 14 | CAT5 | CAT14 |
      | Cat 15 (many subcategories, many courses) | CAT5 | CAT15 |
      | Cat 16 | CAT15 | CAT16 |
      | Cat 17 | CAT15 | CAT17 |
      | Cat 24 | CAT15 | CAT24 |
      | Cat 25 | CAT15 | CAT25 |
      | Cat 26 | CAT15 | CAT26 |
      | Cat 27 | CAT15 | CAT27 |
      | Cat 28 | CAT15 | CAT28 |
      | Cat 29 | CAT15 | CAT29 |
      | Cat 18 | CAT1 | CAT18 |
      | Cat 19 | CAT1 | CAT19 |
      | Cat 20 | CAT1 | CAT20 |
      | Cat 21 | CAT1 | CAT21 |
      | Cat 22 | CAT1 | CAT22 |
      | Cat 23 | CAT1 | CAT23 |
    And the following "categories" exists:
      | name | category | idnumber | visible |
      | Cat 40 (hidden) | CAT13 | CAT40 | 0 |
      | Cat 41 (hidden) | CAT40 | CAT41 | 0 |
    And the following "courses" exists:
      | fullname | shortname | category | summary |
      | Cat course | COURSE1 | 0 | The domestic cat is a small, usually furry, domesticated, and carnivorous mammal. It is often called the housecat when kept as an indoor pet, or simply the cat when there is no need to distinguish it from other felids and felines. Cats are valued by humans for companionship and their ability to hunt vermin and household pests. |
      | Dog course | COURSE2 | 0 | The domestic dog is a subspecies of the gray wolf (Canis lupus), a member of the Canidae family of the mammalian order Carnivora. The term "domestic dog" is generally used for both domesticated and feral varieties. The dog has been the first animal to be domesticated and has been the most widely kept working, hunting, and pet animal in human history. The word "dog" may also mean the male of a canine species, as opposed to the word "bitch" for the female of the species. |
      | Dog hidden course | COURSE2x | 0 | The domestic dog is a subspecies of the gray wolf (Canis lupus), a member of the Canidae family of the mammalian order Carnivora. The term "domestic dog" is generally used for both domesticated and feral varieties. The dog has been the first animal to be domesticated and has been the most widely kept working, hunting, and pet animal in human history. The word "dog" may also mean the male of a canine species, as opposed to the word "bitch" for the female of the species. |
      | Mouse course | COURSE3 | 0 | A mouse (plural: mice) is a small mammal belonging to the order of rodents, characteristically having a pointed snout, small rounded ears, and a long naked or almost hairless tail. The best known mouse species is the common house mouse (Mus musculus). It is also a popular pet. In some places, certain kinds of field mice are also common. This rodent is eaten by large birds such as hawks and eagles. They are known to invade homes for food and occasionally shelter. |
      | Course 4 | COURSE4 | CAT2 | Course summary |
      | Course 5 (no summary) | COURSE5 | CAT2 ||
      | Course 7 | COURSE7 | CAT2 | Course summary |
      | Course 8 | COURSE8 | CAT2 | Course summary |
      | Course 9 | COURSE9 | CAT2 | Course summary |
      | Course 10 | COURSE10 | CAT2 | Course summary |
      | Course 11 | COURSE11 | CAT2 | Course summary |
      | Course 12 | COURSE12 | CAT2 | Course summary |
      | Course 13 | COURSE13 | CAT2 | Course summary |
      | Course 14 | COURSE14 | CAT15 | Course summary |
      | Course 15 | COURSE15 | CAT15 | Course summary |
      | Course 16 | COURSE16 | CAT15 | Course summary |
      | Course 17 | COURSE17 | CAT15 | Course summary |
      | Course 18 | COURSE18 | CAT15 | Course summary |
      | Course 19 | COURSE19 | CAT15 | Course summary |
      | Course 20 | COURSE20 | CAT15 | Course summary |
      | Course 6 with very long name that to test how it is wrapped into several lines. Course 6 with very long name that to test how it is wrapped into several lines. Course 6 with very long name that to test how it is wrapped into several lines | COURSE6 | CAT2 | Course summary |
    And the following "users" exists:
      | username | firstname | lastname | email | auth | confirmed |
      | student1 | Sam1 | Student1 | student1@test.com | manual | 1 |
      | teacher1 | Terry1 | Teacher1 | teacher1@test.com | manual | 1 |
      | teacher2 | Terry2 | Teacher2 | teacher2@test.com | manual | 1 |
    And the following "course enrolments" exists:
      | user | course | role |
      | teacher1 | COURSE1 | editingteacher |
      | teacher1 | COURSE2 | editingteacher |
      | teacher2 | COURSE2 | editingteacher |
      | student1 | COURSE1 | student |
      | student1 | COURSE2 | student |
    And I log in as "admin"
    And I set the following administration settings values:
      | Course overview files limit | 5 |
      | Course overview files extensions | .jpg,.gif,.png,.pdf |
    # Allow guest access to "Cat course":
    When I am on homepage
    And I follow "Cat course"
    And I follow "Edit settings"
    And I upload "admin/tool/behatui/tests/fixtures/cat.jpg" file to "Course overview files" filepicker
    And I fill the moodle form with:
      | Allow guest access | Yes |
    And I press "Save changes"
    # Enable self-enrolment for "Mouse course":
    When I am on homepage
    And I follow "Mouse course"
    And I follow "Edit settings"
    And I upload "admin/tool/behatui/tests/fixtures/mouse.jpg" file to "Course overview files" filepicker
    And I press "Save changes"
    And I add "Self enrolment" enrolment method with:
      | Custom instance name | Test student enrolment |
    And I am on homepage
    # Enable self-enrolment and guest access for "Dog course":
    When I am on homepage
    And I follow "Dog course"
    And I follow "Edit settings"
    And I upload "admin/tool/behatui/tests/fixtures/dog.jpg" file to "Course overview files" filepicker
    And I upload "admin/tool/behatui/tests/fixtures/dog_course_overview.pdf" file to "Course overview files" filepicker
    And I fill the moodle form with:
      | Allow guest access | Yes |
    And I press "Save changes"
    And I add "Self enrolment" enrolment method with:
      | Custom instance name | Test student enrolment |
    And I am on homepage
    # Hide and enable self-enrolment and guest access for "Dog course (hidden)":
    When I am on homepage
    And I follow "Dog hidden course"
    And I follow "Edit settings"
    And I upload "admin/tool/behatui/tests/fixtures/dog.jpg" file to "Course overview files" filepicker
    And I upload "admin/tool/behatui/tests/fixtures/dog_course_overview.pdf" file to "Course overview files" filepicker
    And I fill the moodle form with:
      | Allow guest access | Yes |
      | Availability | 0 |
    And I press "Save changes"
    And I add "Self enrolment" enrolment method with:
      | Custom instance name | Test student enrolment |
    And I am on homepage
    # End
    And I log out
    # ------------ Create modules inside "Cat course" -----------------
    And I log in as "teacher1"
    And I follow "Cat course"
    And I turn editing mode on
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name | Test assignment name |
      | Description | Submit your online text |
      | assignsubmission_onlinetext_enabled | 1 |
      | assignsubmission_file_enabled | 0 |
    And I add a "Chat" to section "1" and I fill the form with:
      | Name of this chat room | Test chat name |
      | Description | The new chat |
      | Repeat sessions | At the same time every week |
    And I log out

  @javascript
  Scenario: Make screenshots of course listings
    Given I log in as "admin"
    And I change browser size to 1200x768
    And I set the following administration settings values:
      | Courses per page | 5 |
      | Courses with summaries limit | 4 |
      | Auto-login guests | 1 |
    And I am on homepage
    And I follow "Edit settings"
    And I fill in "id_s__frontpagecourselimit" with "8"
    And I fill in "id_s__maxcategorydepth" with "10"
    And I press "Save changes"
    And I change guest frontpage display to List of courses
    # And Repeat in themes "Afterburner,Anomaly,Arialist,Binarius,Boxxie,Brick,Formal white,FormFactor,Fusion,Leatherbound,Magazine,Nimble,Nonzero,Overlay,Serenity,Simple,Sky High,Splash,Standard (legacy),Standard":
    And Repeat in themes "Standard":
      """
      And I change frontpage display to Enrolled courses
      And I log out
      Then I save a screenshot as 01_frontpageguest_{themename}
      When I follow "Mouse course"  
      Then I save a screenshot as 14_courseenrol_{themename}
      When I log in as "student1"
      And I am on homepage
      Then I save a screenshot as 02_frontpageenroled_{themename}
      And I follow "My courses"
      And I expand all course overviews
      Then I save a screenshot as 15_mycourses_{themename}
      And I log out
      When I log in as "admin"
      And I change frontpage display to Combo list
      And I am on homepage
      Then I save a screenshot as 03_frontpagecombo_{themename}
      When I change frontpage display to List of categories,Course search box
      And I am on homepage
      Then I save a screenshot as 04_frontpagecategories_{themename}
      When I follow "Courses"
      Then I save a screenshot as 05_coursespage_{themename}
      When I follow "Miscellaneous"
      Then I save a screenshot as 06_smallcategory_{themename}
      When I follow "Courses"
      And I follow "Cat 1 (many subcategories, no courses)"
      Then I save a screenshot as 07_categoriespaging_{themename}
      When I follow "Courses"
      And I follow "Cat 2 (no subcategories, many courses)"
      Then I save a screenshot as 08_coursespaging_{themename}
      When I follow "Courses"
      When I fill in "Search courses:" with "course"
      And I press "Go"
      Then I save a screenshot as 09_coursesearch_{themename}
      When I am on homepage
      And I follow "Cat 15 (many subcategories, many courses)"
      Then I save a screenshot as 10_bigcategory_{themename}
      And I follow "View more"
      Then I save a screenshot as 11_bigcategorycategories_{themename}
      And I follow "View all courses"
      Then I save a screenshot as 12_bigcategorycourses_{themename}
      """
