<?php

namespace simialbi\yii2\firebasemailer;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\MulticastSendReport;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;

class Mailer extends BaseMailer
{
    /**
     * {@inheritdoc}
     */
    public $messageClass = Message::class;

    /**
     * @var string|array The firebase serviceAccountCredentials.json contents
     */
    public string|array $firebaseCredentials;

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (!$this->firebaseCredentials) {
            throw new InvalidConfigException('Firebase credentials must be set');
        }

        parent::init();
    }

    /**
     * Check if a token is subscribed to a topic.
     *
     * @param string $token The token to check
     * @param string $topic The topic to check the token against
     *
     * @return bool
     *
     * @throws MessagingException
     */
    public function isTokenSubscribedToTopic(string $token, string $topic): bool
    {
        return $this->getMessaging()->getAppInstance($token)->isSubscribedToTopic($topic);
    }

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException|MessagingException|FirebaseException
     */
    protected function sendMessage($message): bool
    {
        if (!$message instanceof Message) {
            throw new InvalidConfigException('Message must be an instance of "' . Message::class . '"');
        }

        /** @var MulticastSendReport $report */
        $report = $this->getMessaging()->sendAll($message->toCloudMessages());

        return !$report->hasFailures();
    }

    /**
     * Get the messaging service
     *
     * @return Messaging
     */
    protected function getMessaging(): Messaging
    {
        $factory = new Factory();

        return $factory->withServiceAccount($this->firebaseCredentials)->createMessaging();
    }
}
