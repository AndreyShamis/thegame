<?php

namespace App\Twig;

use ReflectionClass;
use ReflectionException;
use Twig\Extension\AbstractExtension;
use Symfony\Component\Config\Definition\Exception\Exception;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new \Twig_SimpleFilter('ExecutionTimeInHours', array($this, 'ExecutionTimeInHours')),
            new \Twig_SimpleFilter('ExecutionTimeGeneric', array($this, 'ExecutionTimeGeneric')),
            new \Twig_SimpleFilter('getPercentage', array($this, 'getPercentage')),
            new \Twig_SimpleFilter('cast_to_array', array($this, 'cast_to_array')),
            new \Twig_SimpleFilter('pre_print_r', array($this, 'pre_print_r'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('time_ago', function ($time) { return $this->ExecutionTimeInHours(time() - $time);}),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('shortString', [$this, 'shortString']),
            new TwigFunction('getPercentage', [$this, 'getPercentage']),
            new TwigFunction('inarray', array($this, 'inArray'))
        ];
    }


    /**
     * Receive input, if string len > len, cut the string to the len and return with postfix
     * @param string $input
     * @param int $len
     * @param string $postFix
     * @return string
     */
    public function shortString(string $input, int $len = 20, string $postFix = '...'): string
    {
        if (\strlen($input) > $len) {
            return substr($input, 0, $len) . $postFix;
        }
        return $input;
    }

    /**
     * @param mixed $variable
     * @param array $arr
     *
     * @return bool
     */
    public function inArray($variable, $arr): bool
    {
        return \in_array($variable, $arr, true);
    }

    /**
     * @param $obj
     * @return string
     */
    public function pre_print_r($obj): string
    {
        return '<pre>' . print_r($obj, true) . '</pre>';
    }

    /**
     * @param $stdClassObject
     * @return array
     * @throws ReflectionException
     */
    public function cast_to_array($stdClassObject): array
    {
        $array = array();
        try {
            $reflectionClass = new ReflectionClass(\get_class($stdClassObject));

            foreach ($reflectionClass->getProperties() as $property) {
                $property->setAccessible(true);
                $array[$property->getName()] = $property->getValue($stdClassObject);
                $property->setAccessible(false);
            }
        } catch (Exception  $ex) {
        }
        return $array;
    }

//
//    public function __construct(Markdown $parser = null)
//    {
//        $this->parser = $parser;
//    }

    /**
     * @param $valueOf
     * @param $valueFrom
     * @param int $precision
     * @return float|int
     */
    public function getPercentage($valueOf, $valueFrom, $precision = 2)
    {
        $ret = 0;
        try {
            if ($valueFrom>0) {
                $ret = ($valueOf*100)/$valueFrom;
            }
            $ret = round($ret,$precision);
        }
        catch (Exception $ex) {
        }
        return $ret;
    }

    /**
     * @param $time
     * @return string
     */
    public function ExecutionTimeInHours($time): string
    {
        $seconds  =   $time%60;
        $minutes  =   ($time/60)%60;
        $hours    =   number_format (floor($time/60/60));
        $min_print = sprintf('%02d', $minutes);
        if ($min_print === '00') {
            return ($hours . 'h');
        }
        return ($hours . 'h ' . sprintf('%02d', $minutes) . 'm');
    }

    /**
     * @param $time
     * @return string
     */
    public function ExecutionTimeGeneric(int $time): string
    {
        $seconds  =   $time%60;
        $minutes  =   ($time/60)%60;
        $hours    =   number_format (floor($time/60/60));
        $hour_print = sprintf('%dh',$hours);
        $min_print = sprintf('%02dm',$minutes);
        $sec_print = sprintf('%02ds',$seconds);
        if ($hours > 0) {
            $ret = sprintf('%s %s %s', $hour_print, $min_print, $sec_print);
        } else {
            $ret = sprintf('%s %s', $min_print, $sec_print);
        }
        return $ret;
    }
}
