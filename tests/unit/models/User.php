<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $id
 * @property integer $applicationId
 * @property string $login
 * @property string $email
 */
class User extends CActiveRecord {
    public function behaviors() {
        return array(
            'ECompositeUniqueKeyValidatable' => array(
                'class' => 'ECompositeUniqueKeyValidatable',
                'uniqueKeys' => array(
                    array(
                        'attributes' => 'email, applicationId',
                        'errorMessage' => 'This email is already registered',
                        'errorAttributes' => 'email',
                        'skipOnErrorIn' => 'email, applicationId'
                    ),
                    array(
                        'attributes' => 'login, applicationId',
                        'errorMessage' => 'Your login is already taken',
                        'errorAttributes' => 'login'
                    ),
                )
            ),
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * @return User the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('id, applicationId', 'required'),
            array('id, applicationId', 'numerical', 'integerOnly' => true),
            array('email', 'email'),
            array('*', 'compositeUniqueKeysValidator'),
            array('login, email', 'length', 'max' => 45)
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