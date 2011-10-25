<?php

/**
 * This is the model class for table "news".
 *
 * The followings are the available columns in table 'news':
 * @property integer $id
 * @property integer $categoryId
 * @property string $title
 * @property string $text
 */
class News extends CActiveRecord {
    
    public function behaviors() {
        return array(
            'ECompositeUniqueKeyValidatable' => array(
                'class' => 'ECompositeUniqueKeyValidatable',
                'uniqueKeys' => array(
                    'attributes' => 'title, categoryId',
                    'errorMessage' => 'There is already news with such a title in this category!',
                    'errorAttributes' => 'title, categoryId'
                )
            ),
        );
    }
    
    /**
     * Returns the static model of the specified AR class.
     * @return News the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'news';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('id', 'required'),
            array('id', 'numerical', 'integerOnly' => true),
            array('categoryId', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('title', 'length', 'max' => 45),
            array('*', 'compositeUniqueKeysValidator'),
            array('text', 'safe')
        );
    }
    
    /**
     * Validates composite unique keys
     *
     * Validates composite unique keys declared in the
     * ECompositeUniqueKeyValidatable bahavior
     */
    public function compositeUniqueKeysValidator() {
        $this->validateCompositeUniqueKeys();
    }
}