<?php
    require_once __DIR__.'/../../vendor/autoload.php';
    
    use PhpAmqpLib\Connection\AMQPStreamConnection;
    use PhpAmqpLib\Message\AMQPMessage;
    
    class Rabbit
    {
        private $connection;
        private $channel;
        private $queue;
        
        function __construct($params = [])
        {
            // Инициируем подключение
            $this->connection = new AMQPStreamConnection(
                isset($params['host']) ? $params['host'] : 'localhost', // имя хоста, на котором запущен сервер RabbitMQ
                isset($params['port']) ? $params['port'] : 5672,        // номер порта сервиса, по умолчанию - 5672
                isset($params['user']) ? $params['user'] : 'guest',     // имя пользователя для соединения с сервером
                isset($params['pass']) ? $params['pass'] : 'guest'  // пароль
            );
            
            try {
                // Создаем канал
                $this->channel = $this->connection->channel();
            }
            catch (Exception $e) {}
        }
    
        /**
         * Функция создания очереди
         *
         * @param string $queue - имя очереди
         *
         * @return void
         * @throws Exception
         */
        public function queue($queue)
        {
            // Задаем имя биржи
            $this->queue = $queue;
            
            // Создаем биржу, т.к. нам необходимо, чтобы сообщения видели все пользователи сразу
            $this->channel->exchange_declare($this->queue, 'fanout', false, false, false);
            
            // Создаем очередь без имени
            $this->channel->queue_declare('', false, false, false, false);
        }
    
        /**
         * Функция отправки сообщения с очередь
         *
         * @param string|array $message - сообщение
         *
         * @return false|string
         * @throws Exception
         */
        public function send($message)
        {
            // Создаем эксземпляр сообщения
            $msg = new AMQPMessage(json_encode($message, JSON_UNESCAPED_UNICODE), ['delivery_mode' => 2, 'app_id' => 1]);
            
            // Отправляем его на биржу
            $this->channel->basic_publish($msg, $this->queue);
            
            return json_encode($message, JSON_UNESCAPED_UNICODE);
        }
    
        /**
         * Функция получения сообщений очереди
         *
         * @return string|boolean
         * @throws ErrorException
         * @throws Exception
         */
        public function get()
        {
            // Получаем имя очереди, которое сгенерировал RabbitMQ
            list($queue_name,,) = $this->channel->queue_declare('', false, false, true, false);
            
            // Связываем биржу с очередью
            $this->channel->queue_bind($queue_name, $this->queue);
            
            // Запускаем слушателя очереди
            // Функция 'callback' будет обрабатывать полученные сообщения
            $this->channel->basic_consume($queue_name, '', false, true, false, false, [$this, 'callback']);
            
            // Ожидаем новых сообщений
            // Т.к. мы работаем в PHP, а он создан "для того, чтобы умирать", нельзя дать ему работать бесконечно,
            // иначе вся наша работа превратится в тлен. Даем ему 5 секунд и отправляем на покой
            // Как только он закончит работу, JS снова его активирует и он вернется в работу уже новый, молодой, отдохнувший
            while (true)
            {
                $this->channel->wait(null, false, 5);
            }
            
            return json_encode(['empty']);
        }
    
        /**
         * Функция закрытия канала и соединения
         *
         * @return void
         * @throws Exception
         */
        public function close()
        {
            // Закрываем канал
            $this->channel->close();
            try {
                // Закрываем соединение
                $this->connection->close();
            } catch (Exception $e) {}
        }
    
        /**
         * Функция вывода сообщения
         *
         * @param AMQPMessage $msg
         *
         * @return void
         * @throws Exception
         */
        public function callback($msg)
        {
            // Получаем сообщение
            $message = $msg->body;
            // Закрываем всё
            $this->close();
    
            // Т.к. нам просто нужно вернуть в JS ответ, объявляем тип данных и хороним скрипт, попутно выводим сообщение
            header('Content-Type: application/json');
            die($message);
        }
    }
?>