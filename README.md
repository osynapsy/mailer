# Osynapsy Mailer

Osynapsy Mailer è un package PHP per l'invio di email tramite diversi client, incluso SMTP, con supporto per coda, messaggi transazionali con placeholder e allegati.

## Installazione

Utilizzando Composer:

```bash
composer require osynapsy/mailer
```

## Utilizzo base

### Creare un messaggio

```php
use Osynapsy\Mailer\Email\Message;

$msg = new Message('mittente@example.com', 'destinatario@example.com', 'Oggetto della mail');
$msg->setHtmlBody('<b>Ciao!</b>');
$msg->addAttachment('/percorso/al/file.pdf');
```

### Inviare la mail tramite SMTP

```php
use Osynapsy\Mailer\Client\Smtp\SmtpClient;
use Osynapsy\Mailer\Mailer;

$client = new SmtpClient(
    'smtp.example.com',
    587,
    'username',
    'password',
    'tls'
);

$mailer = new Mailer($client);
$mailer->send($msg);

$errors = $mailer->getErrors();
if (!empty($errors)) {
    print_r($errors);
}
```

## Messaggi Transazionali

Per supportare placeholder e invii personalizzati:

```php
use Osynapsy\Mailer\Email\TransitionalMessage;

$msg = new TransitionalMessage('mittente@example.com', 'destinatario@example.com', 'Benvenuto');
$msg->setHtmlBody('Ciao {nome}, benvenuto!');
$msg->setPlaceholder('nome', 'Peter');

$mailer->send($msg);
```

## Queue

È possibile accodare più messaggi e inviarli tutti insieme:

```php
$mailer->queue($msg1)
       ->queue($msg2)
       ->sendQueue();
```

## Connessioni alternative

Il client `Mailer` accetta qualsiasi oggetto che implementi `ClientInterface`, permettendo di cambiare facilmente il metodo di invio, ad esempio tramite un'API REST:

```php
use Osynapsy\Mailer\Client\ClientInterface;
use Osynapsy\Mailer\Mailer;

class RestConnection implements ClientInterface {
    public function sendMessage(Message $msg) {
        // invio via API REST
    }
}

$client = new RestConnection();
$mailer = new Mailer($client);
```

## Gestione errori

Gli errori vengono raccolti dal `Mailer` e possono essere letti tramite:

```php
$errors = $mailer->getErrors();
```

## License

MIT License

