<?php

namespace simialbi\yii2\firebasemailer;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\SendReport;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
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
     * @var array The request options to set to all request.
     * Following options are supported:
     *  - timeout: int, the maximum number of seconds to allow request to be executed.
     *  - proxy: string, URI specifying address of proxy server. (e.g. tcp://proxy.example.com:5100).
     */
    public array $requestOptions = [];

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
     * Check if a token exists in the firebase
     *
     * @param string $token The token to check
     *
     * @return bool
     */
    public function tokenExists(string $token): bool
    {
        try {
            $this->getMessaging()->getAppInstance($token);
        } catch (NotFound $e) {
            return false;
        } catch (FirebaseException $e) {
            Yii::error($e->getMessage(), __METHOD__);
        }

        return true;
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

        // only return failed if there are failures that are not due to unknown tokens
        $failures = $report->filter(static function (SendReport $report): bool {
            return $report->isFailure() && !$report->messageWasSentToUnknownToken();
        });

        return !count($failures->getItems());
    }

    /**
     * Get the messaging service
     *
     * @return Messaging
     */
    protected function getMessaging(): Messaging
    {
        $factory = new Factory();

        $requestOptions = ArrayHelper::filter($this->requestOptions, ['timeout', 'proxy']);
        if (!empty($requestOptions)) {
            $options = HttpClientOptions::default();
            if (isset($requestOptions['timeout'])) {
                $options = $options->withTimeout($requestOptions['timeout']);
            }
            if (isset($requestOptions['proxy'])) {
                $options = $options->withProxy($requestOptions['proxy']);
            }
            $factory = $factory->withHttpClientOptions($options);
        }

        return $factory->withServiceAccount($this->firebaseCredentials)->createMessaging();
    }
}
