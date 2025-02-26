# Firebase push notifications with yii2 framework

A lightweight PHP-client-library for using Firebase push notification services with [yii2 framework](https://www.yiiframework.com).
Reduces the complexity of network-communication between client and SMS gateway, to help save time and money for focusing
on their business logic.

[![Latest Stable Version](https://poser.pugx.org/simialbi/yii2-firebasemailer/v/stable?format=flat-square)](https://packagist.org/packages/simialbi/yii2-firebasemailer)
[![Total Downloads](https://poser.pugx.org/simialbi/yii2-firebasemailer/downloads?format=flat-square)](https://packagist.org/packages/simialbi/yii2-firebasemailer)
[![License](https://poser.pugx.org/simialbi/yii2-firebasemailer/license?format=flat-square)](https://packagist.org/packages/simialbi/yii2-firebasemailer)

## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ php composer.phar require --prefer-dist simialbi/yii2-firebasemailer
```

or add

```
"simialbi/yii2-firebasemailer": "^1.0.0"
```

to the `require` section of your `composer.json`.

## Usage

In order to use this component, you will need to:

1. [Setup component](#setup-component) your application so that the module is available.

### Setup component

```php
return [
    // [...]
    'components' => [
        'firebase' => [
            'class' => 'simialbi\yii2\firebasemailer\Mailer',
            'firebaseCredentials' => [
                'type' => 'service_account',
                'project_id' => 'my_app',
                'private_key_id' => '4d95db2814a6e057611f7883a9a243f6',
                'private_key' => '-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n',
                'client_email' => 'firebase-adminsdk-fbsvc@my_app.iam.gserviceaccount.com',
                'client_id' => '000000000000000000001',
                'auth_uri' =>  'https://accounts.google.com/o/oauth2/auth',
                'token_uri' => 'https://oauth2.googleapis.com/token',
                'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
                'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40my_app.iam.gserviceaccount.com',
                'universe_domain' => 'googleapis.com'
            ],
            // requestOptions => [
            //    'timeout' => 20,
            //    'proxy' => 'tcp://proxy.example.com:5100'
            // ]
        ]
    ]
];
```

## Example Usage

To send a message create a new `Message` instance and set at least the content and recipients.

```php
<?php
/** @var \simialbi\yii2\firebasemailer\Mailer $firebase */
$firebase = Yii::$app->get('firebase', true);
$message = $firebase->createMessage();
$message
    ->setSubject('My subject')
    ->setTextBody('My body\nGreetings')
    ->setData([
        'viewToOpen' => 'my_action',
        'actionId' => 123
    ])
    ->to(['topic' => 'my_topic'])
    ->to(['myFirstT0ken', 'my2ndT0ken']) // alternative ;

if ($message->send()) {
    echo 'success';
} else {
    echo 'failure';
}
```

## License

**yii2-firebasmailer** is released under MIT license. See bundled [LICENSE](LICENSE) for details.
