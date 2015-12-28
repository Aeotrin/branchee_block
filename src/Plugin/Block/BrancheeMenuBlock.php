<?php
/**
 * Provides a 'Branchee Menu' Block
 *
 * @Block(
 *   id = "branchee_menu_block",
 *   admin_label = @Translation("Branchee Menu Block"),
 * )
 */

namespace Drupal\branchee\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Menu\MenuTreeParameters;

class BrancheeMenuBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    if (!empty($config['menu'])) {
      $menu = $config['menu'];

      $parameters = new MenuTreeParameters();
      $parameters->onlyEnabledLinks();

      $menu_tree = \Drupal::menuTree();
      $tree = $menu_tree->load($menu, $parameters);

      $manipulators = array(
        array('callable' => 'menu.default_tree_manipulators:checkAccess'),
        array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
      );

      $tree = $menu_tree->transform($tree, $manipulators);
      $tree = $menu_tree->build($tree);

      $theme = $config['theme'] == 'other' ? $config['theme_other'] : $config['theme'];
      return [
        '#theme' => 'branchee_menu_block',
        '#branchee_theme' => $theme,
        '#menu' => $tree,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();
    $menus = entity_load_multiple('menu');
    $menu_options = [];
    foreach ($menus as $key => $menu) {
      $menu_options[$menu->get('id')] = $menu->get('label');
    }

    $form['branchee_menu_block_menu'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a Menu'),
      '#description' => $this->t('Select a Menu to render with Branchee'),
      '#default_value' => isset($config['menu']) ? $config['menu'] : 'main',
      '#options' => $menu_options,
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
