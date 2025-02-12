<?php

namespace simialbi\yii2\firebasemailer;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Messages;
use Kreait\Firebase\Messaging\Notification;
use yii\helpers\FileHelper;
use yii\mail\BaseMessage;

class Message extends BaseMessage
{
    /**
     * @var string Message charset
     */
    private string $_charset = 'utf-8';

    /**
     * @var array The recipients
     * To send to a topic define like this:
     * ```php
     * [
     *     'topic' => 'my_topic'
     * ]
     * ```
     */
    private array $_recipients = [];

    /**
     * @var Notification The notification instance
     */
    private Notification $_notification;

    /**
     * {@inheritDoc}
     */
    public function __construct($config = [])
    {
        $this->_notification = Notification::create();

        parent::__construct($config);
    }

    /**
     * {@inheritDoc}
     */
    public function getCharset(): string
    {
        return $this->_charset;
    }

    /**
     * {@inheritDoc}
     */
    public function setCharset($charset): self
    {
        $this->_charset = $charset;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFrom(): null
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setFrom($from): self
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTo(): array
    {
        return $this->_recipients;
    }

    /**
     * @param string|array $to receiver fcm tokens. You may pass an array of tokens if multiple recipients should
     * receive this message. You may also specify a topic using format: `['topic' => '<myTopic>']`.
     * {@inheritDoc}
     */
    public function setTo($to): self
    {
        $this->_recipients = (array)$to;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getReplyTo(): null
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setReplyTo($replyTo): self
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCc(): null
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setCc($cc): self
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getBcc(): null
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setBcc($bcc): self
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubject(): ?string
    {
        return $this->_notification->title();
    }

    /**
     * {@inheritDoc}
     */
    public function setSubject($subject): self
    {
        $this->_notification = $this->_notification->withTitle($subject);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setTextBody($text): self
    {
        $this->_notification = $this->_notification->withBody($text);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setHtmlBody($html): self
    {
        return $this->setTextBody(strip_tags($html));
    }

    /**
     * {@inheritDoc}
     */
    public function attach($fileName, array $options = []): self
    {
        if (file_exists($fileName)) {
            $contentType = (isset($options['contentType'])) ? $options['contentType'] : FileHelper::getMimeType($fileName);
            if (str_starts_with($contentType, 'image/')) {
                $this->_notification = $this->_notification->withImageUrl('data:' . $contentType . ';base64,' . base64_encode(file_get_contents($fileName)));
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function attachContent($content, array $options = []): self
    {
        if (isset($options['contentType']) && str_starts_with($options['contentType'], 'image/')) {
            $this->_notification = $this->_notification->withImageUrl('data:' . $options['contentType'] . ';base64,' . base64_encode($content));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function embed($fileName, array $options = []): self
    {
        return $this->attach($fileName, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function embedContent($content, array $options = []): self
    {
        return $this->attachContent($content, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function toString(): string
    {
        $data = $this->_notification->jsonSerialize();

        $string = '';
        if (!empty($data['title'])) {
            $string .= $data['title'] . "\n\n";
        }
        if (!empty($data['body'])) {
            $string .= $data['body'];
        }

        return $string;
    }

    /**
     * Get the Messages object from this configuration.
     *
     * @return Messages
     */
    public function toCloudMessages(): Messages
    {
        $messages = new Messages();
        if (isset($this->_recipients['topic'])) {
            $messages[] = CloudMessage::new()->withNotification($this->_notification)->toTopic($this->_recipients['topic']);
        } else {
            foreach ($this->_recipients as $recipient) {
                $messages[] = CloudMessage::new()->withNotification($this->_notification)->toToken($recipient);
            }
        }

        return $messages;
    }
}
