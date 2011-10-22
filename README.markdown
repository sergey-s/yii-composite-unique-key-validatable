To validate composite unique keys attach the ECompositeUniqueKeyValidateable behavior, declare unique keys and declare short validation method in your model class.

Reasons to use behavior and validation method instead of writing validation class

 * we can't attach a handler for [CActiveRecord::onAfterFind()](http://www.yiiframework.com/doc/api/1.1/CActiveRecord#onAfterFind-detail "CActiveRecord::onAfterFind") with only validator (we need this for storing of the model's old attributes for proper validation when updating an existing record)

 * [CValidator](http://www.yiiframework.com/doc/api/1.1/CValidator#validate-detail "CValidator") doesn't imply validation of several attributes

##Requirements 
Tested on Yii 1.1 and php 5.3

##Usage

Attach the ECompositeUniqueKeyValidatable behavior and declare unique keys

```php
<?php

public function behaviors() {
    return array(
        'ECompositeUniqueKeyValidatable' => array(
            'class' => 'ECompositeUniqueKeyValidatable',
            'uniqueKeys' => array(
                'attributes' => 'login, applicationId',
                'errorMessage' => 'Your login is already taken'
            )
        ),
    );
}
```

declare simple validation method in the model class

```php
<?php

/**
 * Validates composite unique keys
 *
 * Validates composite unique keys declared in the
 * ECompositeUniqueKeyValidatable bahavior
 */
public function compositeUniqueKeysValidator() {
    $this->validateCompositeUniqueKeys();
}
```

declare the validation rule

```php
<?php

public function rules() {
    return array(
        // the first parameter doesn't matter, I use '*' (pretty ugly
        // definition, but I don't know a better way)
        array('*', 'compositeUniqueKeysValidator'),
    );
}
```

### Description of the options of unique keys

 * **attributes** - unique key

 * **errorMessage** - error message

 * **errorAttributes** (_optional_) - attributes of the model which will contain the error message

 * **skipOnErrorIn** (_optional_) - if one of this attributes contains errors then validation will be skipped

### Some examples

declaring of two composite unique keys

```php
<?php

public function behaviors() {
    return array(
        'ECompositeUniqueKeyValidatable' => array(
            'class' => 'ECompositeUniqueKeyValidatable',
            'uniqueKeys' => array(
                array(
                    'attributes' => 'email, applicationId',
                    'errorAttributes' => 'email, email_confirmation',
                    'errorMessage' => 'This email is already registered',
                    'skipOnErrorIn' => 'email, applicationId'
                ),
                array(
                    'attributes' => 'login, applicationId',
                    'errorAttributes' => 'login',
                    'errorMessage' => 'Your login is already taken',
                    'skipOnErrorIn' => 'login, applicationId'
                ),
            )
        ),
    // ...
```
