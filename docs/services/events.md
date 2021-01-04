# Events

You can subscribe to some events sent from the Bus.

It can be used to add logging for example.

You will need to install an event library that
implements PSR-14 (Event Dispatcher).

You can find one [on Packagist][link-psr-event-dispatcher].

For example with [symfony/event-dispatcher][link-symfony-event-dispatcher]:

```php
use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Core\Event\BeforeCallEvent;
use CuyZ\WebZ\Core\Event\FailedCallEvent;
use CuyZ\WebZ\Core\Event\SuccessfulCallEvent;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Soap\SoapTransport;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();

$bus = WebServiceBus::builder()
    ->withTransport(new HttpTransport())
    ->withTransport(new SoapTransport())
    ->withEventDispatcher($dispatcher)
    ->build();

$dispatcher->addListener(
    BeforeCallEvent::class,
    function (BeforeCallEvent $event) {
        // This event is dispatched before a WebService is called
    }
);

$dispatcher->addListener(
    FailedCallEvent::class,
    function (FailedCallEvent $event) {
        // This event is dispatched when the call failed
    }
);

$dispatcher->addListener(
    SuccessfulCallEvent::class,
    function (SuccessfulCallEvent $event) {
        // This event is dispatched when the call had no error
    }
);

$place = $bus->call(new GetPlace(123));

echo $place->name();
```

[link-psr-event-dispatcher]: https://packagist.org/providers/psr/event-dispatcher-implementation
[link-symfony-event-dispatcher]: https://symfony.com/doc/current/components/event_dispatcher.html
