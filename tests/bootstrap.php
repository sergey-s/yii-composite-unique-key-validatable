<?php

// change the following paths if necessary
$yiit=dirname(__FILE__).'/../../../../../_yii-core/yiit.php';
$config=dirname(__FILE__).'/../../../config/test.php';

require_once($yiit);
require_once(__DIR__.'/WebTestCase.php');

Yii::createWebApplication($config);
