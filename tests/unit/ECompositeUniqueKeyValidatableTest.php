<?php

Yii::setPathOfAlias('self', dirname(dirname(__DIR__)));
Yii::import('self.ECompositeUniqueKeyValidatable');
Yii::import('self.tests.unit.*');
Yii::import('self.tests.unit.models.*');
Yii::import('self.tests.unit.models.*');

/**
 * Unit tests for ECompositeUniqueKeyValidatable 
 *
 * To run this test you need AbstractSqliteTestCase class (github:
 * @link https://github.com/sergey-s/yii-abstract-sqlite-test-case )
 * 
 * @link http://www.yiiframework.com/extension/composite-unique-key-validatable/
 * @link https://github.com/sergey-s/yii-composite-unique-key-validatable
 * @author Sergey Sytsevich <laa513@gmail.com>
 */
class ECompositeUniqueKeyValidatableTest extends AbstractSqliteTestCase {

    public $fixtures = array(
        'user' => 'User',
        'news' => 'News',
        'user_rated_news' => 'UserRatedNews'
    );

    /**
     * Creates needed tables 
     */
    protected static function _setUpDatabase() {
        $command = Yii::app()->getDb()->createCommand();
        $command->createTable('user', array(
            'id' => 'INT(3) PRIMARY KEY NOT NULL',
            'applicationId' => 'INT(3) NOT NULL',
            'login' => 'VARCHAR(30)',
            'email' => 'VARCHAR(30)'
        ));
        $command->createTable('news', array(
            'id' => 'INT(3) PRIMARY KEY NOT NULL',
            'categoryId' => 'INT(3) DEFAULT NULL',
            'title' => 'TEXT',
            'text' => 'TEXT',
        ));
        $command->createTable('user_rated_news', array(
            'userId' => 'INT(3) NOT NULL',
            'newsId' => 'INT(3) NOT NULL',
            'rating' => 'INT(1) NOT NULL'
        ));
    }
    
    /**
     * Test two unique keys (both are INT+VARCHAR) in one model
     */
    public function testSaveAndUpdateOfModelWithTwoUniqueKeys() {
        // this information are valid
        $user = new User();
        $user->setAttributes(array(
            'id' => 4,
            'applicationId' => 2,
            'login' => 'test2',
            'email' => 'test2@gmail.com'
        ));
        $this->assertTrue($user->validate());
        
        // but such a user is already registered in application#1
        $user->applicationId = 1;
        $this->assertFalse($user->validate());
        $this->assertEquals($user->getError('login'), 'Your login is already taken');
        $this->assertEquals($user->getError('email'), 'This email is already registered');
        
        // fix login
        $user->login = 'Free_login';
        $this->assertFalse($user->validate());
        $this->assertNull($user->getError('login'));
        $this->assertEquals($user->getError('email'), 'This email is already registered');
        
        // fix email
        $user->email = 'Free.email@gmail.com';
        $this->assertTrue($user->validate());
        $this->assertTrue($user->save());
        
        // test update
        $user = User::model()->findByPk(4);
        $this->assertTrue($user instanceof User);
        $this->assertTrue($user->validate());
        $this->assertTrue($user->save());
    }
    
    /**
     * Test one INT+INT unique key 
     */
    public function testSaveAndUpdateOfModelWithOneUniqueKey() {
        // news#1 has already been rated by user#1
        $rating = new UserRatedNews();
        $rating->setAttributes(array(
            'userId' => 1,
            'newsId' => 1,
            'rating' => 3
        ));
        $this->assertFalse($rating->validate());
        $this->assertEquals($rating->getError('userId'), 'User cannot rate news twice!');
        $this->assertEquals($rating->getError('newsId'), 'User cannot rate news twice!');
        
        // fix news
        $rating->newsId = 3;
        $this->assertTrue($rating->validate());
        $this->assertTrue($rating->save());
        
        // test update
        $rating = UserRatedNews::model()->findByPk(array(
            'userId' => 1,
            'newsId' => 3
        ));
        $this->assertTrue($rating instanceof UserRatedNews);
        $rating->rating = 4;
        $this->assertTrue($rating->validate());
        $this->assertTrue($rating->save());
    }
    
    /**
     * Test VARCHAR+INT(possible NULL) unique key
     */
    public function testUniqueKeyWithPossibleNullAttributeValue() {
        // there is already news with the 'Perpetual Motion Found!' which is not
        // attached to any category
        $news = new News();
        $news->setAttributes(array(
            'id' => 5,
            'categoryId' => null,
            'title' => 'Perpetual Motion Found!',
            'text' => 'wow!'
        ));
        $this->assertFalse($news->validate());
        $this->assertEquals($news->getError('title'), $news->getError('categoryId'));
        
        // news with this title also exists in category #1
        $news->categoryId = 1;
        $this->assertFalse($news->validate());
        $this->assertEquals($news->getError('title'), $news->getError('categoryId'));
        
        // but not in category #2
        $news->categoryId = 2;
        $this->assertTrue($news->validate());
        $this->assertTrue($news->save());
        
        // test update
        $news = News::model()->findByPk(5);
        $this->assertTrue($news instanceof News);
        $news->text = 'updated text';
        $this->assertTrue($news->validate());
        $this->assertTrue($news->save());
    }
    
    /**
     * Tests for 'skipOnErrorIn' feature
     */
    public function testSaveAndUpdateWithSkipOnErrorInFeature() {
        $user = new User();
        $user->setAttributes(array(
            'id' => 42,
            'login' => 'test1', // this login is already taken in appliction#1
            'applicationId' => 'wrong_application_id',
            'email' => 'wrong_email_format'
        ));
        $this->assertFalse($user->validate());
        
        $this->assertNotNull($user->getError('email'));
        $this->assertNotEquals($user->getError('email'), 'This email is already registered');
        $this->assertNotNull($user->getError('applicationId'));
        $this->assertNull($user->getError('login'));
        
        $user->applicationId = 1;
        $user->email = 'test1@gmail.com';
        $this->assertFalse($user->validate());
        
        $this->assertEquals($user->getError('email'), 'This email is already registered');
        $this->assertEquals($user->getError('login'), 'Your login is already taken');
        
        $user->applicationId = 3;
        $this->assertTrue($user->validate());
        $this->assertTrue($user->save());
        
        // test update
        $user = User::model()->findByPk(42);
        $this->assertTrue($user instanceof User);
        $this->assertTrue($user->validate());
        $this->assertTrue($user->save());
    }
}

