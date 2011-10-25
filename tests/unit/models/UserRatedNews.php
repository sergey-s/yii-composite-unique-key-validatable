<?php

/**
 * This is the model class for table "user_rated_news".
 *
 * The followings are the available columns in table 'user_rated_news':
 * @property integer $userId
 * @property integer $newsId
 * @property integer $rating
 */
class UserRatedNews extends CActiveRecord {
    public function behaviors() {
        return array(
            'ECompositeUniqueKeyValidatable' => array(
                'class' => 'ECompositeUniqueKeyValidatable',
                'uniqueKeys' => array(
                    'attributes' => 'userId, newsId',
                    'errorMessage' => 'User cannot rate news twice!',
                    'errorAttributes' => 'userId, newsId'
                )
            ),
        );
    }
    
    /**
     * Returns the static model of the specified AR class.
     * @return UserRatedNews the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user_rated_news';
    }
    
    public function primaryKey() {
        return array('userId', 'newsId');
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('userId, newsId, rating', 'required'),
            array('userId, newsId, rating', 'numerical', 'integerOnly' => true),
            array('*', 'compositeUniqueKeysValidator'),
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