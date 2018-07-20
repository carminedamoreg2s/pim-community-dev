Feature: Completeness Report
  In order to inform about the completeness of the catalog
  As an administrator user
  I want to know the completeness of the catalog for all channels and locales

  @acceptance-back
  Scenario: Report the completeness for multiple channels
    Given the channel ecommerce with only complete products
    And the channel mobile with some incomplete products
    When the user asks for the completeness of the catalog
    Then the report displays that the channel ecommerce is complete
    And the report displays that the channel mobile is incomplete

  @acceptance-back
  Scenario: Report the completeness for multiple locales by channel
    Given the channel ecommerce with only complete products for the following locales: French and English
    And the channel ecommerce with some incomplete products for the following locales: Spanish
    When the user asks for the completeness of the catalog
    Then the report displays that the channel ecommerce is complete for the following locales: French and English
    And the report displays that the channel ecommerce is incomplete for the following locales: Spanish

