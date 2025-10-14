<?php

namespace Drupal\reusable_searchbar\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing;
use Drupal\Core\Url;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormBase;

/**
 * Provides the Reusable Search block.
 *
 * @Block(
 *   id = "reusable_searchbar_search",
 *   admin_label = @Translation("Reusable Searchbar"),
 *   category = @Translation("Reusable Searchbar"),
 * )
 */
class ReusableSearchbarBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Form builder service.
   *
   * @var Drupal\Core\Plugin\ContainerFactoryPluginInterface
   */
  protected $formBuilder;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   Configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The id for the plugin.
   * @param mixed $plugin_definition
   *   The definition of the plugin implementaton.
   * @param Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The "form_builder" service instance to use.
   */
  public function __construct(
      array $configuration,
      $plugin_id,
      $plugin_definition,
      FormBuilderInterface $formBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $blockConfig = $this->getConfiguration();
    $search_action = $blockConfig['search_page'];
    $search_placeholder = !empty($blockConfig['search_placeholder']) ? $blockConfig['search_placeholder'] : null;
    $search_title = !empty($blockConfig['search_title']) ? $blockConfig['search_title'] : null;
    $search_param = !empty($blockConfig['search_param']) ? $blockConfig['search_param'] : null;
    $search_custom_param = !empty($blockConfig['search_custom_param']) ? $blockConfig['search_custom_param'] : null;
    $search_custom_param_value = !empty($blockConfig['search_custom_param_value']) ? $blockConfig['search_custom_param_value'] : null;
    $search_facet = !empty($blockConfig['search_facet']) ? $blockConfig['search_facet'] : null;
    $search_facet_name = !empty($blockConfig['search_facet_name']) ? $blockConfig['search_facet_name'] : 'collection';
    $form_defaults = array();
    $form_defaults['default_action'] = null;
    $form_defaults['search_placeholder'] = null;
    $form_defaults['search_param'] = $search_param;
    $form_defaults['search_facet'] = $search_facet;
    $form_defaults['search_facet_name'] = $search_facet_name;
    if (!empty($search_custom_param) && !empty($search_custom_param_value)) {
      $form_defaults['search_custom_param'] = $search_custom_param;
      $form_defaults['search_custom_param_value'] = $search_custom_param_value;
    }
    if (!empty($search_action)) {
      $form_defaults['default_action'] = $search_action;
    }
    if (!empty($search_placeholder)) {
      $form_defaults['search_placeholder'] = $search_placeholder;
    }
    if (!empty($search_title)) {
      $form_defaults['search_title'] = $search_title;
    }
    $form = $this->formBuilder->getForm('Drupal\reusable_searchbar\Form\ReusableSearchbarForm', $form_defaults);
    return [
      '#theme' => 'reusable_searchbar_search_block',
      '#reusable_searchbar_search_form' => $form,
      '#cache' => [
        'max-age' => 3600,
      ],
      '#attached' => [
        'library' => [
          'reusable_searchbar/reusable_searchbar',
        ],
      ],
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['search_page'] = [
      '#type' => 'textfield',
      '#title' => t('Search Page Override'),
      '#default_value' =>  isset($config['search_page']) ? $config['search_page'] : null,
      '#description' => t('Which URL to search including preceeding slash. Local searches should use relative paths. For example /searchnew. This supports fully qualified URLs, but these should only be used when creating search forms for external websites.'),
    ];
    $form['search_param'] = [
      '#type' => 'textfield',
      '#title' => t('Search Parameter'),
      '#default_value' =>  isset($config['search_param']) ? $config['search_param'] : 'query',
      '#description' => t('Set this to the query parameter name. This should be whatever the search results page expects. Leave this as the default (query) for any digital searches.'),
    ];
    $form['search_facet_name'] = [
      '#type' => 'textfield',
      '#title' => t('Search Facet Name'),
      '#default_value' =>  isset($config['search_facet_name']) ? $config['search_facet_name'] : null,
      '#description' => t('Filter on which facet (if any). Find the facet name by performing a faceted query on the search page and copying the facet name from the URL. Defaults to "collection".'),
    ];
    $form['search_facet'] = [
      '#type' => 'textfield',
      '#title' => t('Search Facet'),
      '#default_value' =>  isset($config['search_facet']) ? $config['search_facet'] : null,
      '#description' => t('Filter on which facet (if any). Find this value by performing a faceted query on the search page and copying the facet value of the intended facet from the URL.'),
    ];
    $form['search_custom_param'] = [
      '#type' => 'textfield',
      '#title' => t('Custom Parameter'),
      '#default_value' => isset($config['search_custom_param']) ? $config['search_custom_param'] : null,
      '#description' => t('Define a custom parameter to pass. This will only work if the Custom Parameter Value field is also filled. Find the parameter name by performing a search and plucking the name from the url.'),
    ];
    $form['search_custom_param_value'] = [
      '#type' => 'textfield',
      '#title' => t('Custom Parameter Value'),
      '#default_value' => isset($config['search_custom_param_value']) ? $config['search_custom_param_value'] : null,
      '#description' => t('The value to pass for the Custom Parameter field above.'),
    ];
    $form['search_placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Search Placeholder'),
      '#default_value' =>  isset($config['search_placeholder']) ? $config['search_placeholder'] : null,
      '#description' => t('This text displays in the textfield before user values are entered. E.g., Search collection...'),
    ];
    $form['search_title'] = [
      '#type' => 'textfield',
      '#title' => t('Search Title'),
      '#default_value' =>  isset($config['search_title']) ? $config['search_title'] : null,
      '#description' => t('If left empty, "Search" will be used.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('search_page', $form_state->getValue('search_page'));
    $this->setConfigurationValue('search_param', $form_state->getValue('search_param'));
    $this->setConfigurationValue('search_custom_param', $form_state->getValue('search_custom_param'));
    $this->setConfigurationValue('search_facet', $form_state->getValue('search_facet'));
    $this->setConfigurationValue('search_facet_name', $form_state->getValue('search_facet_name'));
    $this->setConfigurationValue('search_custom_param_value', $form_state->getValue('search_custom_param_value'));
    $this->setConfigurationValue('search_placeholder', $form_state->getValue('search_placeholder'));
    $this->setConfigurationValue('search_title', $form_state->getValue('search_title'));
  }
}
