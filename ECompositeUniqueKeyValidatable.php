<?php

/**
 * ECompositeUniqueKeyValidatable behavior attaches methods to validate
 * uniqueness of composite keys
 *
 * @link http://www.yiiframework.com/extension/composite-unique-key-validatable/
 * @link https://github.com/sergey-s/yii-composite-unique-key-validatable
 * @author Sergey Sytsevich <laa513@gmail.com>
 */
class ECompositeUniqueKeyValidatable extends CActiveRecordBehavior {
    
    /**
     * @var mixed composite unique keys of a model
     *
     * In normalized form this is an array with the next structure
     * array(
     *     // array representing a composite unique key
     *     array(
     *         // attributes of a composite key
     *         'attributes' => array('email', 'applicationId'),
     *         
     *         // error message
     *         'errorMessage' => 'This email is already registered',
     *         
     *         // (optional) list of attributes of the model which will be populated with an error message
     *         'errorAttributes' => array('email', 'email_confirmation'),
     *         
     *         // (optional) if one of this attributes contains errors then validation will be skipped
     *         'skipOnErrorIn' => array('email', 'applicationId')
     *     ),
     *     
     *     // other keys...
     * )
     */
    public $uniqueKeys;

    /**
     * @var bool Whether the $uniqueKeys property is already normalized
     */
    private $_normalized = false;

    /**
     * Saves old values of unique keys
     *
     * We need old values to validate existing model on update scenario
     *
     * @param CEvent $event
     */
    public function afterFind($event) {
        $this->_normalizeKeysData();

        foreach ($this->uniqueKeys as &$uk) {
            $uk['oldValue'] = $this->_getUkValue($uk['attributes']);
        }
    }

    /**
     * Validates composite unique keys
     */
    public function validateCompositeUniqueKeys() {
        $this->_normalizeKeysData();
        
        $object = $this->getOwner();

        foreach ($this->uniqueKeys as $uk) {
            // check whether validation of the current key should be skipped
            foreach ($uk['skipOnErrorIn'] as $skipAttr) {
                if ($object->getError($skipAttr)) {
                    continue 2;
                }
            }
            
            $criteria = new CDbCriteria();
            foreach ($uk['attributes'] as $attr) {
                if ($object->$attr === null) {
                    $criteria->addCondition("`$attr`" . ' is null');
                } else {
                    $criteria->compare("`$attr`", $object->$attr);
                }
            }

            /*
             * if the model is a new record or if it's an old record with modified unique key value
             * then the composite key should be unique ($criteriaLimit = 0)
             *
             * if we are updating an existing record without changes of unique key attributes
             * then we should allow one existing record satisfying the criteria
             */
            $ukIsChanged = !$object->isNewRecord
                           && ($uk['oldValue'] != $this->_getUkValue($uk['attributes']));
            $criteriaLimit = ($object->isNewRecord || $ukIsChanged) ? 0 : 1;

            if (CActiveRecord::model(get_class($object))->count($criteria) > $criteriaLimit) {
                foreach ($uk['errorAttributes'] as $attr) {
                    $object->addError($attr, $uk['errorMessage']);
                }
            }
        }
    }

    /**
     * [column => value] map representing composite key
     *
     * @return array
     */
    public function _getUkValue($attributes) {
        $ukValue = array();
        foreach ($attributes as $attr) {
            $ukValue[$attr] = $this->getOwner()->$attr;
        }

        return $ukValue;
    }


    /**
     * Normalize unique keys
     */
    private function _normalizeKeysData() {
        if ($this->_normalized) {
            return;
        }

        if (!is_array($this->uniqueKeys)) {
            throw new CException('Wrong unique keys format in the '
                                 . get_class($this->getOwner()) . ' model');
        }

        // when only one unique key is declared
        if (!is_array(current($this->uniqueKeys))) {
            $this->uniqueKeys = array($this->uniqueKeys);
        }

        // convert comma separated lists to arrays
        foreach ($this->uniqueKeys as &$uk) {
            isset($uk['attributes']) or $uk['attributes'] = array();
            is_array($uk['attributes']) or $this->_stringListToArray($uk['attributes']);
            
            // *nonexistent attribute* means that an error message will not be attached to a certain attribute
            isset($uk['errorAttributes']) or $uk['errorAttributes'] = array('*nonexistent attribute*');
            is_array($uk['errorAttributes']) or $this->_stringListToArray($uk['errorAttributes']);

            isset($uk['skipOnErrorIn']) or $uk['skipOnErrorIn'] = array();
            is_array($uk['skipOnErrorIn']) or $this->_stringListToArray($uk['skipOnErrorIn']);
        }

        $this->_normalized = true;
    }


    /**
     * Convert comma separated list to array
     *
     * @param string $list
     */
    private function _stringListToArray(&$list) {
        $list = explode(',', $list);
        foreach ($list as &$item) {
            $item = trim($item);
        }
    }

}