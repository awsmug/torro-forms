Feature: Going to my Site
  I am visiting my WordPress site

  Scenario: Visiting
    Given I am on my Site
    When I am logged in as an admin
    Then I am on the Dashboard
    Then I go to the menu Settings
    Then I see a title named "General Settings"