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

      return $tree;
    }

    return array(
      '#markup' => $this->t('Hello @menu!', array (
          '@menu' => $menu,
        )
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['branchee_menu_block_menu'] = array (
      '#type' => 'select',
      '#title' => $this->t('Select a Menu'),
      '#description' => $this->t('Select a Menu to render with Branchee'),
      '#default_value' => isset($config['menu']) ? $config['menu'] : 'main',
      '#options' => array(
        'main' => t('Main navigation'),
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('menu', $form_state->getValue('branchee_menu_block_menu'));
  }
}
