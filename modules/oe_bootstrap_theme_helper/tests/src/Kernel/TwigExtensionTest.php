<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Kernel;

use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\Tests\oe_bootstrap_theme\Kernel\AbstractKernelTestBase;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Markup;

/**
 * Test those Twig extensions that require Drupal to be bootstrapped.
 */
class TwigExtensionTest extends AbstractKernelTestBase {

  /**
   * Test bcl_link function.
   */
  public function testBclLinkFunction(): void {
    $items = $this->bclLinkDataProvider();

    foreach ($items as $item) {
      [$expected, $data] = $item;
      $elements = [
        '#type' => 'inline_template',
        '#template' => '{{ bcl_link(label, path, attributes) }}',
        '#context' => [
          'label' => $data['label'],
          'path' => $data['path'],
          'attributes' => new Attribute($data['attributes'] ?? []),
        ],
      ];

      $output = $this->renderRoot($elements);
      $crawler = new Crawler($output);
      $link = $crawler->filter('a');

      $this->assertEquals($expected['href'], $link->attr('href'));
      $this->assertEquals($expected['text'], $link->html());

      if (!empty($expected['attributes'])) {
        foreach ($expected['attributes'] as $name => $value) {
          $this->assertEquals($value, $link->attr($name));
        }
      }
    }
  }

  /**
   * Data provider for bclLinkFunction.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function bclLinkDataProvider(): array {
    return [
      'string_path_to_front' => [
        [
          'href' => '/',
          'text' => 'My link',
        ],
        [
          'path' => '/',
          'label' => 'My link',
        ],
      ],
      'string_path_front_with_hash' => [
        [
          'href' => '/#kitty',
          'text' => 'Miau',
        ],
        [
          'path' => '/#kitty',
          'label' => 'Miau',
        ],
      ],
      'string_path_current_url_with_hash' => [
        [
          'href' => '#kitty',
          'text' => 'Miau',
        ],
        [
          'path' => '#kitty',
          'label' => 'Miau',
        ],
      ],
      'just_hash' => [
        [
          'href' => '#',
          'text' => 'hash',
        ],
        [
          'path' => '#',
          'label' => 'hash',
        ],
      ],
      'string_path_current_url_with_parameters' => [
        [
          'href' => '?foo=baz',
          'text' => 'My link',
        ],
        [
          'path' => '?foo=baz',
          'label' => 'My link',
        ],
      ],
      'string_path_internal' => [
        [
          'href' => '/some-path',
          'text' => 'Some link',
        ],
        [
          'path' => '/some-path',
          'label' => 'Some link',
        ],
      ],
      'label_with_safe_markup' => [
        [
          'href' => '/',
          'text' => 'This is <em>markup</em>!',
        ],
        [
          'path' => '/',
          // Emulate markup as sent by twig.
          'label' => new Markup('This is <em>markup</em>!', NULL),
        ],
      ],
      'label_with_unsafe_markup' => [
        [
          'href' => '/',
          // Xss filtered markup.
          'text' => 'This is unsafe <em>markup</em>!',
        ],
        [
          'path' => '/',
          'label' => 'This is <script type="text/javascript">unsafe</script> <em>markup</em>!',
        ],
      ],
      'url_object' => [
        [
          'href' => '/node/add',
          'text' => 'Url object link',
        ],
        [
          'path' => Url::fromUserInput('/node/add'),
          'label' => 'Url object link',
        ],
      ],
      'url_object_absolute' => [
        [
          'href' => 'http://localhost/here',
          'text' => 'Absolute url link',
        ],
        [
          'path' => Url::fromUri('base:here', ['absolute' => TRUE]),
          'label' => 'Absolute url link',
        ],
      ],
      'link_with_custom_attribute' => [
        [
          'href' => '/',
          'text' => 'Custom attrib link',
        ],
        [
          'path' => '/',
          'label' => 'Custom attrib link',
          'attributes' => [
            'data-foo' => 'baz',
            'id' => 'id-bar',
          ],
        ],
      ],
      'external_link' => [
        [
          'href' => 'https://www.example.com',
          'text' => 'External link',
        ],
        [
          'path' => 'https://www.example.com',
          'label' => 'External link',
        ],
      ],
    ];
  }

}
