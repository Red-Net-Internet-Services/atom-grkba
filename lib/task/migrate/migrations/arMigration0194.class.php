<?php

/*
 * Activate AMS plugin
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0194
{
  const
    VERSION = 194, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Enable sfAmsKaireiosPlugin
    if (null !== $setting = QubitSetting::getByName('plugins'))
    {
      $settings = unserialize($setting->getValue(array('sourceCulture' => true)));
      $settings[] = 'sfAmsKaireiosPlugin';

      $setting->setValue(serialize($settings), array('sourceCulture' => true));
      $setting->save();
    }

    // Add the "grkba" template to its taxonomy
    $term = new QubitTerm;
    $term->parentId = QubitTerm::ROOT_ID;
    $term->taxonomyId = QubitTaxonomy::INFORMATION_OBJECT_TEMPLATE_ID;
    $term->code = 'grkba';
    $term->name = 'Custom Dublin Core Model for Kaireios Archives';
    $term->culture = 'en';
    $term->save();


    QubitMigrate::bumpTaxonomy(QubitTaxonomy::GRKBA_AMS_KEYWORD_ID, $configuration);
    $taxonomy = new QubitTaxonomy;
    $taxonomy->id = QubitTaxonomy::GRKBA_AMS_KEYWORD_ID;
    $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
    $taxonomy->sourceCulture = 'en';
    $taxonomy->setName('Keywords', array('culture' => 'en'));
    $taxonomy->save();

    QubitMigrate::bumpTerm(QubitTerm::GRKBA_AMS_KEYWORD_NOTE_ID, $configuration);
    $term = new QubitTerm;
    $term->id = QubitTerm::GRKBA_AMS_KEYWORD_NOTE_ID;
    $term->parentId = QubitTerm::ROOT_ID;
    $term->taxonomyId = QubitTaxonomy::NOTE_TYPE_ID;
    $term->sourceCulture = 'en';
    $term->setName('Keywords note', array('culture' => 'en'));
    $term->save();

    return true;
  }
}