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
 * sfAmsKaireiosPlugin - AMS Kaireios Metadata Standard Plugin Class.
 *
 * @author     [Your Name]
 */
class sfAmsKaireiosPlugin implements ArrayAccess
{
    protected $resource;
    protected $property;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function __toString()
    {
        $string = '';

        // Add title if set
        if (0 < strlen($title = $this->resource->__toString())) {
            $string .= $title;
        }

        // Add publication status
        $publicationStatus = $this->resource->getPublicationStatus();
        if (isset($publicationStatus) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $publicationStatus->statusId) {
            $string .= (!empty($string)) ? ' ' : '';
            $string .= "({$publicationStatus->status->__toString()})";
        }

        return $string;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'creators':
                $creators = null;

                foreach ($this->resource->ancestors->andSelf()->orderBy('rgt') as $ancestor) {
                    if (QubitInformationObject::ROOT_ID == $ancestor->id) {
                        break;
                    }

                    if (0 < count($creators = $ancestor->getCreators())) {
                        break;
                    }
                }

                return $creators;

            case '_event':
                // Because simple Dublin Core cannot qualify the <date/> or <coverage/>
                // elements, we only return a limited set of events: just those that
                // are related to creation/origination
                $event = [];
                foreach ($this->resource->eventsRelatedByobjectId as $item) {
                    switch ($item->typeId) {
                        case QubitTerm::CREATION_ID:
                        case QubitTerm::CONTRIBUTION_ID:
                        case QubitTerm::PUBLICATION_ID:
                        case QubitTerm::COLLECTION_ID:
                        case QubitTerm::ACCUMULATION_ID:
                            $event[] = $item;

                            break;
                    }
                }

                return $event;

            case 'coverage':
                $coverage = [];

                foreach ($this->resource->eventsRelatedByobjectId as $item) {
                    if (null !== $place = $item->getPlace()) {
                        $coverage[] = $place;
                    }
                }

                foreach ($this->resource->getPlaceAccessPoints() as $item) {
                    $coverage[] = $item->term;
                }

                return $coverage;

            case 'date':
                $list = [];
                foreach ($this->_event as $item) {
                    if (0 < strlen($date = $item->getDate(['cultureFallback' => true]))) {
                        $list[] = $date;
                    }
                }

                return $list;

            case 'format':
                return $this->resource->getCleanExtentAndMedium(['cultureFallback' => true]);

            case 'identifier':
                return $this->resource->referenceCode;

            case 'sourceCulture':
                return $this->resource->sourceCulture;

            case 'keywords':
                $keywords = [];

                foreach ($this->resource->getTermRelations(QubitTaxonomy::GRKBA_AMS_KEYWORD_ID) as $item) {
                    $keywords[] = $item->term;
                }

                return $keywords;
            case 'licenseNumber':
            case 'number':
                return $this->$grkba->number;

            case 'subject':
                $subject = [];
                foreach ($this->resource->getSubjectAccessPoints() as $item) {
                    $subject[] = $item->term;
                }

                // Add name access points
                $criteria = new Criteria();
                $criteria = $this->resource->addrelationsRelatedBysubjectIdCriteria($criteria);
                $criteria->add(QubitRelation::TYPE_ID, QubitTerm::NAME_ACCESS_POINT_ID);

                foreach (QubitRelation::get($criteria) as $item) {
                    $subject[] = $item->object;
                }

                return $subject;

            case 'type':
                $type = [];

                foreach ($this->resource->getTermRelations(QubitTaxonomy::DC_TYPE_ID) as $item) {
                    $type[] = $item->term;
                }

                return $type;

        }
    }

    public function getProperty($name, $options = [])
    {
        switch ($name) {
            case 'number':
                return $this->property($name)->__get('value', $options);
        }
    }

    public function setProperty($name, $value)
    {
        switch ($name) {
            case 'number':
                $this->property($name)->value = $value;

                return $this;
            }
    }
    protected function property($name)
    {
        if (!isset($this->property[$name])) {
            $criteria = new Criteria();
            $this->resource->addPropertysCriteria($criteria);
            $criteria->add(QubitProperty::NAME, $name);

            if (1 == count($query = QubitProperty::get($criteria))) {
                $this->property[$name] = $query[0];
            } else {
                $this->property[$name] = new QubitProperty();
                $this->property[$name]->name = $name;

                $this->resource->propertys[] = $this->property[$name];
            }
        }

        return $this->property[$name];
    }

    public function offsetExists($offset)
    {
        $args = func_get_args();

        return call_user_func_array([$this, '__isset'], $args);
    }

    public function offsetGet($offset)
    {
        $args = func_get_args();

        return call_user_func_array([$this, '__get'], $args);
    }

    public function offsetSet($offset, $value)
    {
        $args = func_get_args();

        return call_user_func_array([$this, '__set'], $args);
    }

    public function offsetUnset($offset)
    {
        $args = func_get_args();

        return call_user_func_array([$this, '__unset'], $args);
    }

    public static function eventTypes()
    {
        return [
            QubitTerm::getById(QubitTerm::CONTRIBUTION_ID),
            QubitTerm::getById(QubitTerm::CREATION_ID),
            QubitTerm::getById(QubitTerm::PUBLICATION_ID),
        ];
    }
}
