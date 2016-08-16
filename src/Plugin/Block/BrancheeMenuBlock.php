<?php
/**
 * @file
 * Contains Drupal\branchee_block\Plugin\Block\BrancheeMenuBlock.
 */

namespace Drupal\branchee_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;


/**
 * Provides a 'Branchee Menu' Block
 *
 * @Block(
 *   id = "branchee_menu_block",
 *   admin_label = @Translation("Branchee Menu Block"),
 * )
 */
class BrancheeMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The menu link tree service.
   *
   * @var MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MenuLinkTreeInterface $menu_link_tree) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuLinkTree = $menu_link_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu.link_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    // If no menu was selected, don't try to render one.
    if (!empty($config['menu'])) {
      $menu = $config['menu'];

      // Build a default set of menuTreeParameters
      $parameters = new MenuTreeParameters();
      $parameters->onlyEnabledLinks();

      // Load the menu tree using the parameters defined
      $menu_tree = \Drupal::menuTree();
      $tree = $menu_tree->load($menu, $parameters);

      $manipulators = [
        [ 'callable' => 'menu.default_tree_manipulators:checkAccess' ],
        [ 'callable' => 'menu.default_tree_manipulators:generateIndexAndSort' ],
      ];

      // Build the menu tree taking into account access and sorting
      $tree = $menu_tree->transform($tree, $manipulators);
      $tree = $menu_tree->build($tree);

      // Add menu level classes to the tree
      branchee_block_add_menu_class($tree, 1);

      $theme = $config['theme'] == 'other' ? $config['theme_other'] : $config['theme'];

      // Construct the Branchee_block render array
      $form = [
        '#theme' => 'branchee_menu_block',
        '#branchee_theme' => $theme,
        '#menu' => $tree,
        '#attached' => [
          'library' =>  [
            'branchee_block/branchee-menu'
          ],
        ],
        '#cache' => [
          'contexts' => [
            'url.path', //vary on url path
          ],
        ],
      ];

      return $form;
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $entity_manager = \Drupal::entityTypeManager();
    $menus = $entity_manager->getStorage('menu')->loadMultiple();

    $menu_options = [];
    foreach ($menus as $menu) {
      $menu_options[$menu->get('id')] = $menu->get('label');
    }

    $form['branchee_menu_block_menu'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a Menu'),
      '#description' => $this->t('Select a Menu to render with Branchee'),
      '#default_value' => isset($config['menu']) ? $config['menu'] : 'main',
      '#options' => $menu_options,
      '#required' => TRUE,
    ];

    $theme_options = [
      'base' => 'Base',
      'minimal' => 'Minimal',
      'rainbow' => 'Rainbow',
      'dark-rainbow' => 'Dark Rainbow',
      'deep-blue' => 'Deep Blue',
      'other' => 'Other',
    ];

    $form['branchee_menu_block_theme'] = [
      '#type' => 'radios',
      '#title' => $this->t('Select a Theme'),
      '#description' => $this->t('Select a Theme for the branchee menu, or a custom class to apply to it.'),
      '#default_value' => isset($config['theme']) ? $config['theme'] : 'base',
      '#options' => $theme_options,
      '#required' => TRUE,
    ];

    $form['branchee_menu_block_theme_other'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Other'),
      '#description' => $this->t('Type in a custom theme name to use.'),
      '#default_value' => isset($config['theme_other']) ? $config['theme_other'] : '',
      '#states' => array(
        'visible' => array(
          ':input[name="settings[branchee_menu_block_theme]"]' => array('value' => 'other'),
        ),
      ),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('menu', $form_state->getValue('branchee_menu_block_menu'));
    $this->setConfigurationValue('theme', $form_state->getValue('branchee_menu_block_theme'));
    $this->setConfigurationValue('theme_other', $form_state->getValue('branchee_menu_block_theme_other'));
  }
}
