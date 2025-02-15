<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Functional;

use Drupal\oe_bootstrap_theme\BackwardCompatibility;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the theme settings.
 */
class ThemeSettingsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_bootstrap_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'oe_bootstrap_theme';

  /**
   * Tests the table theme settings.
   */
  public function testTableSettings(): void {
    $user = $this->drupalCreateUser([
      'access administration pages',
      'administer themes',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet('/admin/appearance/settings/oe_bootstrap_theme');
    $assert_session = $this->assertSession();

    $assert_session->checkboxChecked('Style all tables using Bootstrap.');
    $responsive_field = $assert_session->selectExists('Responsive');
    $this->assertEqualsCanonicalizing([
      'Never' => 'Never',
      'always' => 'Always',
      'sm' => 'Small',
      'md' => 'Medium',
      'lg' => 'Large',
      'xl' => 'Extra large',
      'xxl' => 'Extra extra large',
    ], $this->getOptions($responsive_field));
    $this->assertEquals('always', $responsive_field->getValue());
    // The MarkupRenderingTest runs assertions on the default config values
    // already.
    $responsive_field->setValue('');
    $assert_session->buttonExists('Save configuration')->press();
    $this->assertEquals('<table data-striping="1" class="table"><tbody><tr><td>A</td><td>B</td></tr></tbody></table>', $this->renderTestTable());

    $responsive_field->setValue('xxl');
    $assert_session->buttonExists('Save configuration')->press();
    $this->assertEquals('<div class="table-responsive-xxl"><table data-striping="1" class="table"><tbody><tr><td>A</td><td>B</td></tr></tbody></table></div>', $this->renderTestTable());

    $responsive_field->setValue('md');
    $assert_session->buttonExists('Save configuration')->press();
    $this->assertEquals('<div class="table-responsive-md"><table data-striping="1" class="table"><tbody><tr><td>A</td><td>B</td></tr></tbody></table></div>', $this->renderTestTable());

    $assert_session->fieldExists('Style all tables using Bootstrap.')->uncheck();
    $assert_session->buttonExists('Save configuration')->press();
    $this->assertEquals('<table data-striping="1"><tbody><tr><td>A</td><td>B</td></tr></tbody></table>', $this->renderTestTable());

    // Test that when the Bootstrap table setting is off, the rendering can be
    // forced via theme suggestions.
    $this->assertEquals('<table data-striping="1" class="table"><tbody><tr><td>A</td><td>B</td></tr></tbody></table>', $this->renderTestTable('table__bootstrap'));
    $this->assertEquals('<div class="table-responsive-md"><table data-striping="1" class="table"><tbody><tr><td>A</td><td>B</td></tr></tbody></table></div>', $this->renderTestTable('table__bootstrap__responsive'));
  }

  /**
   * Tests the backward compatibility theme settings.
   */
  public function testBackwardCompatibilitySettings() {
    $user = $this->drupalCreateUser([
      'access administration pages',
      'administer themes',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet('/admin/appearance/settings/oe_bootstrap_theme');
    $assert_session = $this->assertSession();
    $bc_wrapper = $assert_session->elementExists('xpath', '//details[./summary[.="Backward compatibility"]]');
    $card_image_hidden_checkbox = $assert_session->fieldExists('Card image hidden on mobile', $bc_wrapper);
    $card_use_grid_checkbox = $assert_session->fieldExists('Card to use grid classes', $bc_wrapper);

    // BC settings are disabled on new installs.
    $this->assertFalse($card_image_hidden_checkbox->isChecked());
    $this->assertFalse($card_use_grid_checkbox->isChecked());

    $this->assertFalse(BackwardCompatibility::getSetting('card_search_image_hide_on_mobile'));
    $this->assertFalse(BackwardCompatibility::getSetting('card_search_use_grid_classes'));

    $card_image_hidden_checkbox->check();
    $assert_session->buttonExists('Save configuration')->press();
    $assert_session->pageTextContains('The configuration options have been saved.');

    $this->assertTrue($card_image_hidden_checkbox->isChecked());
    $this->assertFalse($card_use_grid_checkbox->isChecked());

    drupal_static_reset('theme_get_setting');
    \Drupal::configFactory()->clearStaticCache();
    $this->assertTrue(BackwardCompatibility::getSetting('card_search_image_hide_on_mobile'));
    $this->assertFalse(BackwardCompatibility::getSetting('card_search_use_grid_classes'));

    $card_use_grid_checkbox->check();
    $assert_session->buttonExists('Save configuration')->press();
    $assert_session->pageTextContains('The configuration options have been saved.');

    $this->assertTrue($card_image_hidden_checkbox->isChecked());
    $this->assertTrue($card_use_grid_checkbox->isChecked());

    drupal_static_reset('theme_get_setting');
    \Drupal::configFactory()->clearStaticCache();
    $this->assertTrue(BackwardCompatibility::getSetting('card_search_image_hide_on_mobile'));
    $this->assertTrue(BackwardCompatibility::getSetting('card_search_use_grid_classes'));
  }

  /**
   * Renders a table for test purposes.
   *
   * @return string
   *   The table normalised markup.
   */
  protected function renderTestTable(string $theme = 'table'): string {
    $build = [
      '#theme' => $theme,
      '#rows' => [
        ['A', 'B'],
      ],
    ];

    // Theme settings config is cached statically.
    drupal_static_reset();
    \Drupal::configFactory()->clearStaticCache();

    $html = (string) $this->container->get('renderer')->renderRoot($build);
    return trim(preg_replace('/>\s+</', '><', $html));
  }

}
