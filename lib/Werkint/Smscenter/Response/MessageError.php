<?php
namespace Werkint\Smscenter\Response;

/**
 * MessageError.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class MessageError extends MessageStatus
{
    protected $error;

    /**
     * @param int    $id
     * @param string $status
     * @param string $error
     */
    public function __construct(
        $id,
        $status,
        $error
    ) {
        parent::__construct($id, $status);
        $this->error = $error;
    }

    /**
     * {@inheritdoc}
     */
    public function isError()
    {
        return true;
    }

    protected $errorMessages = [
        0   => 'Нет ошибки',
        1   => 'Абонент не существует',
        6   => 'Абонент не в сети',
        11  => 'Нет услуги SMS',
        13  => 'Абонент заблокирован',
        21  => 'Нет поддержки SMS',
        245 => 'Статус не получен',
        246 => 'Ограничение по времени',
        247 => 'Превышен лимит сообщений',
        248 => 'Нет маршрута',
        249 => 'Неверный формат номера',
        250 => 'Номер запрещен настройками',
        251 => 'Превышен лимит на один номер',
        252 => 'Номер запрещен',
        253 => 'Запрещено спам-фильтром',
        254 => 'Запрещенный sender id',
        255 => 'Отклонено оператором',
    ];

    public function getErrorMessage()
    {
        return $this->errorMessages[$this->error];
    }

    protected $errorInfos = [
        0   => 'Абонент существует и доступен',
        1   => 'Указанный номер телефона не существует',
        6   => 'Телефон абонента отключен или находится вне зоны действия сети',
        11  => 'Означает, что абонент не может принять SMS-сообщение. Например, услуга не подключена, или абонент находится в роуминге, где не активирован прием сообщений, или у оператора абонента не налажен обмен SMS с текущим роуминговым оператором. Также это может быть городской номер без приема сообщений',
        13  => 'Возникает, например, если на счету абонента нулевой или отрицательный баланс, и он находится в роуминге, или заблокирован оператором за продолжительную неуплату либо добровольно самим абонентом. Также данная ошибка может возвращаться при повреждении SIM-карты либо неправильном вводе PIN и PUK-кодов SIM-карты',
        21  => 'Аппарат абонента не поддерживает прием SMS-сообщений',
        245 => 'В течение суток статус доставки не был получен от оператора, в этом случае нельзя точно сказать, было сообщение доставлено или нет',
        246 => 'Если в личном кабинете в пункте "Настройки" во вкладке "Лимиты и ограничения" установлено "Время отправки" и галочка "запретить отправку в другое время", то при попытке отправки SMS-сообщений в период времени, отличный от указанного в поле "Время отправки", отправка сообщений будет запрещаться с указанием данной ошибки',
        247 => 'Превышен общий суточный лимит сообщений, указанный Клиентом в личном кабинете в пункте "Настройки"',
        248 => 'Означает, что на данный номер отправка сообщений недоступна в нашем сервисе. Например, ввели несуществующий мобильный код, либо для указанного номера и текста нет рабочего SMS-шлюза',
        249 => 'Возникает, когда мобильный код указанного номера и соответствующая этому коду длина номера неверны',
        250 => 'Номер попал под ограничения, установленные Клиентом для мобильных номеров в личном кабинете в пункте "Настройки"',
        251 => 'Превышен суточный лимит сообщений на один номер. Лимит устанавливается Клиентом в личном кабинете в пункте "Настройки". Также такая ошибка возможна при отправке более 50 сообщений одному абоненту, которые были отправлены с перерывом между сообщениями менее 30 секунд',
        252 => 'Возникает, например, при попытке указания Клиентом одного из наших федеральных номеров в качестве получателя SMS-сообщения',
        253 => 'Данная ошибка возникает, например, если текст сообщения содержит нецензурные выражения и оскорбления, призывы отправить sms и некоторые другие запрещенные тексты',
        254 => 'Запрещено указывать в качестве отправителя короткие платные номера, номер получателя сообщений, а также названия операторов, чужих интернет-ресурсов, компаний и государственных организаций.
Также данная ошибка возникает при попытке отправки от незарегистрированного имени отправителя',
        255 => 'Оператор отклонил сообщение без указания точного кода ошибки.
Такое бывает, например, когда номер не принадлежит ни одному мобильному оператору, т.е. с несуществующим кодом, либо по какой-то другой причине оператор не может доставить сообщение',
    ];

    public function getErrorInfo()
    {
        return $this->errorInfos[$this->error];
    }

    // -- Getters ---------------------------------------

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

}