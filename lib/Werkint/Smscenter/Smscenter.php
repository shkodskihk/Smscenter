<?php
namespace Werkint\Smscenter;

use Werkint\Smscenter\Exception\ServerException;
use Werkint\Smscenter\Response\IncomingMessage;
use Werkint\Smscenter\Response\MessageError;
use Werkint\Smscenter\Response\MessageInfo;
use Werkint\Smscenter\Response\PhoneOperator;

/**
 * Smscenter.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class Smscenter implements
    SmscenterInterface
{
    // API url
    const BASE_URL = 'https://smsc.ru/sys/';
    // Messages default charset
    const CHARSET = 'utf-8';

    protected $login;
    protected $password;
    protected $sender;

    /**
     * @param string $login
     * @param string $password
     * @param string $sender
     */
    public function __construct(
        $login,
        $password,
        $sender
    ) {
        $this->login = $login;
        $this->password = $password;
        $this->sender = $sender;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessages(
        array $phones,
        $format = 0,
        $message = null,
        \DateTime $time = null
    ) {
        $params = [
            'phones'  => join(';', $phones),
            'mes'     => (string)$message,
            'sender'  => $this->sender,
            'charset' => static::CHARSET,
        ];

        if ($time) {
            $time = $time->format('DDMMYYhhmm, h1-h2, 0ts, +m');
            $params['time'] = $time;
        }

        $format = explode('=', $this->formats[$format]);
        $params[$format[0]] = $format[1];

        $ret = $this->query('send', $params);

        return (int)$ret->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getIncomingMessages(
        $hours = 24
    ) {
        $hours = min($hours, 70);
        $list = $this->query('get', [
            'get_answers' => '1',
            'hour'        => $hours,
        ]);

        $ret = [];
        foreach ($list as $row) {
            $ret[] = new IncomingMessage(
                $row->id,
                $this->getDate($row->sent),
                $this->getDate($row->received),
                $row->message,
                $row->phone,
                $row->to_phone
            );
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance()
    {
        $ret = $this->query('balance');

        return (float)$ret->balance;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(
        $phone,
        $messageId,
        $moreInfo = false
    ) {
        $ret = $this->query('status', [
            'phone' => $phone,
            'id'    => $messageId,
            'all'   => $moreInfo ? '2' : '0',
        ]);
        if ($ret->status > 1) {
            return new MessageError(
                $messageId,
                $ret->status,
                $ret->err
            );
        } else {
            return new MessageInfo(
                $messageId,
                $ret->status,
                $this->getDate($ret->last_date)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPhoneOperator(
        $phone
    ) {
        $ret = $this->query('info', [
            'get_operator' => '1',
            'phone'        => $phone,
        ]);

        return new PhoneOperator(
            $ret->operator,
            $ret->region
        );
    }

    /**
     * Internal method for querying
     *
     * @param string $command
     * @param array  $params
     * @return mixed
     * @throws \Exception
     */
    protected function query(
        $command,
        array $params = []
    ) {
        $params = array_merge($params, [
            'fmt'   => '3',
            'login' => $this->login,
            'psw'   => md5($this->password),
        ]);
        $url = static::BASE_URL . $command . '.php';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($ch);

        if (!$ret) {
            throw new ServerException('Empty server response');
        }
        $ret = json_decode($ret);
        if (!empty($ret->error_code) && !empty($ret->error)) {
            throw new ServerException('Error ' . $ret->error_code . ': ' . $ret->error);
        }

        return $ret;
    }

    // Incoming date format
    const DATE_FORMAT = 'd.m.Y H:i:s';
    // Incoming timezone
    const DATE_TIMEZONE = 'Europe/Moscow';

    /**
     * Internal method for populating date with TZ
     *
     * @param string $date
     * @return \DateTime
     */
    protected function getDate($date)
    {
        $date = \DateTime::createFromFormat(
            static::DATE_FORMAT,
            $date,
            new \DateTimeZone(static::DATE_TIMEZONE)
        );
        return $date;
    }

    // -- Formats ---------------------------------------

    protected $formats = [
        'bin=0', 'flash=1', 'push=1', 'hlr=1', 'bin=1', 'bin=2', 'ping=1'
    ];

    // Usual message
    const FORMAT_NORMAL = 0;
    // Flash (popup message)
    const FORMAT_FLASH = 1;
    // WAP-PUSH message
    const FORMAT_PUSH = 2;
    // HLR-query to get phone info
    const FORMAT_HRL = 3;
    // Binary message
    const FORMAT_BIN = 4;
    // Binary message in HEX
    const FORMAT_BINHEX = 5;
    // Ping-sms
    const FORMAT_PING = 6;

}
