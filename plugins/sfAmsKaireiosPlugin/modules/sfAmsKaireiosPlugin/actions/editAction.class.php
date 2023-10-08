<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Information Object - editDc.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class sfAmsKaireiosPluginEditAction extends InformationObjectEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'accessConditions',
        'extentAndMedium',
        'number',
        'keywords',
        'identifier',
        'language',
        'locationOfOriginals',
        'placeAccessPoints',
        'repository',
        'scopeAndContent',
        'subjectAccessPoints',
        'title',
        'type',
        'displayStandard',
        'displayStandardUpdateDescendants',
    ];

    protected function earlyExecute()
    {
        parent::earlyExecute();

        $this->grkba = new sfAmsKaireiosPlugin($this->resource);

        $title = $this->context->i18n->__('Add new resource');
        if (isset($this->getRoute()->resource)) {
            if (1 > strlen($title = $this->resource->__toString())) {
                $title = $this->context->i18n->__('New Entry');
            }

            $title = "Edit {$title}";
        }

        $this->response->setTitle("{$title} - {$this->response->getTitle()}");

        $this->dcDatesComponent = new sfAmsKaireiosPluginDcDatesComponent($this->context, 'sfAmsKaireiosPlugin', 'dcDates');
        $this->dcDatesComponent->resource = $this->resource;
        $this->dcDatesComponent->execute($this->request);

        $this->dcNamesComponent = new sfAmsKaireiosPluginDcNamesComponent($this->context, 'sfAmsKaireiosPlugin', 'dcNames');
        $this->dcNamesComponent->resource = $this->resource;
        $this->dcNamesComponent->execute($this->request);
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'type':
                $criteria = new Criteria();
                $this->resource->addObjectTermRelationsRelatedByObjectIdCriteria($criteria);
                QubitObjectTermRelation::addJoinTermCriteria($criteria);
                $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::DC_TYPE_ID);

                $value = [];
                foreach ($this->relations = QubitObjectTermRelation::get($criteria) as $item) {
                    $value[] = $this->context->routing->generate(null, [$item->term, 'module' => 'term']);
                }

                $this->form->setDefault('type', $value);
                $this->form->setValidator('type', new sfValidatorPass());

                $choices = [];
                foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::DC_TYPE_ID) as $item) {
                    $choices[$this->context->routing->generate(null, [$item, 'module' => 'term'])] = $item;
                }

                $this->form->setWidget('type', new sfWidgetFormSelect(['choices' => $choices, 'multiple' => true]));

                break;
            case 'keywords':
                $criteria = new Criteria;
                $criteria->add(QubitObjectTermRelation::OBJECT_ID, $this->resource->id);
                $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);
                $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::GRKBA_AMS_KEYWORD_ID);
        
                $value = $choices = [];
                foreach ($this[$name] = QubitObjectTermRelation::get($criteria) as $item) {
                    $choices[$value[] = $this->context->routing->generate(null, [$item->term, 'module' => 'term'])] = $item->term;
                }
        
                $this->form->setDefault('keywords', $value);
                $this->form->setValidator('keywords', new sfValidatorPass);        
                $this->form->setWidget('keywords', new sfWidgetFormSelect(array('choices' => $choices, 'multiple' => true)));
        
                break;
            case 'number':
                $this->form->setDefault('number', $this->grkba->getProperty($name));
                $this->form->setValidator('number', new sfValidatorString());
                $this->form->setWidget('number', new sfWidgetFormTextarea());
            default:
                return parent::addField($name);
        }
    }

    protected function processField($field)
    {
        switch ($field->getName()) {
            case 'type':
                $value = $filtered = [];
                foreach ($this->form->getValue('type') as $item) {
                    $params = $this->context->routing->parse(Qubit::pathInfo($item));
                    $resource = $params['_sf_route']->resource;
                    $value[$resource->id] = $filtered[$resource->id] = $resource;
                }

                foreach ($this->relations as $item) {
                    if (isset($value[$item->term->id])) {
                        unset($filtered[$item->term->id]);
                    } else {
                        $item->delete();
                    }
                }

                foreach ($filtered as $item) {
                    $relation = new QubitObjectTermRelation();
                    $relation->term = $item;

                    $this->resource->objectTermRelationsRelatedByobjectId[] = $relation;
                }

                break;
            case 'keywords':
                $value = $filtered = [];
                foreach ($this->form->getValue($field->getName()) as $item) {
                    $params = $this->context->routing->parse(Qubit::pathInfo($item));
                    $resource = $params['_sf_route']->resource;
                    $value[$resource->id] = $filtered[$resource->id] = $resource;
                }

                foreach ($this[$field->getName()] as $item) {
                    if (isset($value[$item->term->id])) {
                        unset($filtered[$item->term->id]);
                    } else {
                        $item->indexObjectOnDelete = false;
                        $item->delete();
                    }
                }

                foreach ($filtered as $item) {
                    $relation = new QubitObjectTermRelation();
                    $relation->term = $item;

                    $this->resource->objectTermRelationsRelatedByobjectId[] = $relation;
                }

                break;            
            
            case 'number':
                $this->grkba->setProperty($field->getName(), $this->form->getValue($field->getName()));
                break;
            default:
                return parent::processField($field);
        }
    }

    protected function processForm()
    {
        $this->resource->sourceStandard = 'Dublin Core Simple version 1.1';

        $this->dcDatesComponent->processForm();

        $this->dcNamesComponent->processForm();

        return parent::processForm();
    }
}