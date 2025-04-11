<?php

/**
 * Created by PhpStorm.
 * User: rudyten
 * Date: 9/29/2016
 * This is created in the profile.pgp and referenced inside the $butObj(ButtonBaseComplex Class)
 *      It collects the URL every time a button is created, And a running total
 *      of how many URL's is has found
 *
 * Alias : DebugHelpRt
 *
 * Class collects all the URL's that
 */

declare(strict_types=1);

namespace App\Helpers;

//use JetBrains\PhpStorm\NoReturn;

//use DebugHelpRt;

class DebugRt
{
    public const DEBUG_HIDE_MSG = false;
    //old file name was class Class_Rt_Debug.php
    public static bool $display = true;
    public static bool $runTrackLoc  = true;

    public static string $debugLine = 'off';
    public static string $runningPad = '';
    public static string $tag = '';
    //public static string
    public static string $testVar = 'debug test';
    public static string $testY = '';
    public static string $fileName = "";

    private static int $cnt = 0;
    private static int $stepCnt = 0;
    private static string $locInfo;


    private static string $traceLine = '';

    public string $ident  = __Method__;

    public function __construct()
    {
        //$this->pageKey = $pageKey;
    }



    /**
     * Display configuration debug information
     *
     * usage:
     * ```
     * Debug::showConfig(
     *    $config,
     *    __DIR__ . '/../../logs',
     *    'Logger Configuration'
     * );
     * ```
     *
     * @param array $config Configuration array
     * @param string $directory Default directory path
     * @param string $title Optional title for the debug box
     * @return void
     */
    public static function showConfig(array $config, string $directory, string $title = 'Logger Configuration'): void
    {
        echo '<div style="background:#afa;padding:5px;margin:5px;border:1px solid #0a0;">';
        echo "<h4>$title</h4>";
        echo 'Config array contents: <pre>' . print_r($config, true) . '</pre><br>';
        echo 'Directory from config: ' . ($config['directory'] ?? 'NOT SET') . '<br>';
        echo 'Default directory: ' . $directory . '<br>';
        echo 'Final directory used: ' . ($config['directory'] ?? $directory) . '<br>';
        echo '</div>';
    }

    /**
     * Display general debug information
     *
     * usage:
     * ```
     * Debug::pp([
     *     'Container environment' => ($c->get('environment') ?? 'NOT FOUND IN CONTAINER')
     * ], 'Container environment', '#dd0');
     * ```
     * ============================
     * ```
     * Debug::pp([
     *     'Environmentaaaa' => $environment,
     *     'Debug mode from config' => $config['debug_mode'] ? 'TRUE' : 'FALSE',
     *     'Debug mode actually set' => $logger->isDebugMode() ? 'TRUE' : 'FALSE'
     * ], 'Logger Status', '#ffa');
     * ```
     *
     * @param mixed $data Data to display
     * @param string $title Optional title
     * @param string $bgColor Optional background color
     * @return void
     */
    public static function pp($data, string $title = 'Debug', string $bgColor = '#ffc'): void
    {
        echo '<div style="background:' . $bgColor . ';padding:5px;margin:5px;border:1px solid #aa0;">';
        echo "<h4>$title</h4>";

        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                echo "$key $value <br />";
            }
        } else {
            echo htmlspecialchars((string)$data);
        }

        echo '</div>';
    }


    public static function initialize(string $var): void
    {
        self::$cnt      = 0;
        self::$fileName = $var;
    }

    public static function printRr(?array $arr, ?string $val = null, ?string $lbl = null): void
    {
        echo "<hr />";
        if (isset($val)) {
            echo "$lbl : " . $val;
        }
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
    }

    public static function printHtmlspecialchars(string $var, ?string $lbl = null): void
    {
        echo "<hr />";
        if (isset($lbl)) {
            echo "\Helper :--: $lbl : " . $var;
        }
        echo "<pre>";
        echo htmlspecialchars($var);
        echo "</pre>";
    }

    public static function ruleArrDisplay(array $rulesArr): void
    {
        echo "<hr />";
        if (isset($rulesArr['type'])) {
            echo "--TYPE : ";//.$rulesArr['type'];
        }
        echo "<pre>rulesArr : ";
        print_r($rulesArr);
        echo "</pre>";
    }

    public static function exit(string $strg = ""): void
    {
        print "<br />Forced an exit.... : " . $strg;
    }
    public static function printStep(
        string $strg = "",
        string $stepTag = "gen",
        string $label = "",
        bool $exit = false,
        string $foo1 = "foo1",
        string $foo2 = "foo2",
        object|array|bool|null $arr = [],
        ?array $strgArr = null
    ): void {
        if (!DEBUG_SYSTEM) {
            return;
        }

        $tagsToSkip = DEBUG_TAGS;
        if (in_array($stepTag, $tagsToSkip)) {
            //print "<br />skipped : $strg";
            //print "<br />skipped1 foo1: $foo1";
            //print "<br />skipped2 foo2: $foo2";
            return;
        }
        ++self::$stepCnt;
        //+self:$stepCnt;
        $class0      = "";
        $function0   = "";
        $i = 0;
        //var_dump(debug_backtrace());
        $file0       = debug_backtrace()[$i]['file'];
        //$file0 = subStr($file0,23);


        $line0       = debug_backtrace()[$i]['line'];
        if (isset(debug_backtrace()[$i + 1]['class'])) {
            $class0      = debug_backtrace()[$i + 1]['class'];
        }
        if (isset(debug_backtrace()[$i + 1]['function'])) {
            $function0   = debug_backtrace()[$i + 1]['function'];
        }

        //print "<br />preMsg $file0 - $class0 - $line0 - ($function0) msg";

        $x = self::$stepCnt;
        if (DEBUG_PRINT_STEP) {
            $tagLine1 = "<span style='color:red'>$stepTag - Step: $x</span>:";
            $tagLine2 = "$file0 - $function0 -$line0 ";
            echo "<br />$tagLine1 - $tagLine2 ";
            if (isset($strgArr)) {
                foreach ($strgArr as $key => $value) {
                        echo "<br />$value";
                }
                //echo "<br />sssss1";
            }
            if ($strg != "") {
                if ($label != "") {
                    echo "<br />
                    <span style='font-weight:bolder;
                    font-size:large; color:red;'>$label</span>: " . $strg;
                } else {
                    echo "<br />$strg";
                }
                //echo "<br />$tagLine2 ";
            }
            if (is_array($arr) && !empty($arr)) {
                print "<pre>";
                print_r($arr);
                print "</pre>";
            }
            if (is_object($arr) && !empty($arr)) {
                print "<pre>";
                print_r($arr);
                print "</pre>";
            }
            if (is_null($arr)) {
                print ": NULL";
            }
            print "<hr style='color:red'/>";
        }
        if ($exit) {
            print "<br />BOOM";
            exit();
        }
    }

    /**
     * Print Line with X characters long.
     * "========================================"
     * @param string $char
     * @param int $cnt
     * @return void
     */
    public static function printLineSep(string $char = "=", int $cnt = 40): void
    {
        if (!DEBUG_SYSTEM) {
            return;
        }

        $xxx = str_repeat($char, $cnt);
        echo "<br />$xxx";
    }


//    private function debug_on_check()
//    {
//        if (DEBUG_RT === false) {
//            return false;
//        } else {
//            return true;
//        }
//    }

    public static function printTrackLoc(): void
    {
        print "<br />" . self::$testY;
    }


    public static function pad(int $cnt): string
    {
        $pad = str_repeat('_', $cnt);

        //print "<br />".subStr(__FILE__,23).": ".__LINE__." $cnt : " . $pad;
        //exit();
        return $pad;
    }


    public static function trackLoc(string $file, array $options = array()): void
    {
        print "<br />inside App\HelperRt.php ->tracker : " . self::$runTrackLoc;
        if (self::$runTrackLoc === false) {
            return;
        }
        print "<br />inside App\HelperRt.php ->tracker : " . self::$runTrackLoc;


        $defaults = array(
            'oopsType'  => "FATAL",
            'err'       => 1,
            'loc'       => 'inside',
            'act'       => null,
            'msg'       => null,
            'msgX'      => null,
            'action'    => null,
            'fields'    => null,
            'val1'      => null,
            'val2'      => null,
            'val3'      => null,
            'val4'      => null,
            'fix'       => "",  ## ver. 32
            'fixV1'     => null,  ## ver. 32
            'fixV2'     => null,  ## ver. 32
            'uniqueTag' => null,## ver. 32
            'ui'        => null,
        );
        $options = array_merge($defaults, $options);
        extract($options);
            $msgX = '';
        print "<br />loc : $loc";

        if ($loc === 'about') {
            //self::$runningPad .= self::pad(8 );
            $preMsg = self::$runningPad . 'ABOUT : ';
        } elseif ($loc === 'newobj') {
            self::$runningPad .= self::pad(8);
            $preMsg = self::$runningPad . 'NEW OBJECT : ';
        } elseif ($loc === 'action') {
            //self::$runningPad .= self::pad(8 );
            $preMsg = self::$runningPad . 'ACTION : ';
        } elseif ($loc === 'contsetup') {
            self::$runningPad .= self::pad(8);
            $preMsg = self::$runningPad . 'CONTINUE SETUP: ';
        } elseif ($loc === 'doing') {
            $preMsg = '---DOING : ';
        } else {
            $preMsg = self::$runningPad . 'INSIDE : ';
        }

        if (isset($msg)) {
            $msg = '( ' . $msg . ' )';
        } else {
            $msg = '';
        }

        $class0      = "";
        $function0   = "";

        $i = 0;
        $file0       = debug_backtrace()[$i]['file'];
        $line0       = debug_backtrace()[$i]['line'];
        if (isset(debug_backtrace()[1]['class'])) {
            $class0      = debug_backtrace()[1]['class'];
        }
        if (isset(debug_backtrace()[1]['function'])) {
            $function0   = debug_backtrace()[1]['function'];
        }
        print "<br />loc xxx: $function0";

        if ($function0 == 'include') {
            self::$testY .= "<br />$preMsg $file0 - $class0 - $line0 - ($function0)$msg";
        } elseif ($loc === 'about') {
            self::$testY .= "<br />$preMsg $class0 - $line0 - ($function0)$msg";
        } elseif ($loc === 'about2') {
            self::$testY .= "<br />$preMsg $class0 - $line0 - ($function0)$msg";
        } else {
            self::$testY .= "<br />$preMsg $class0 - $line0 - ($function0)$msg";
        }

        //exit();
        $oops = "<div style='border: #ef7c7c solid 6px; padding:10px; width:50%;margin: auto'>BOOOM FIND ME 2341234
                    <br />File = $file0
                    <br />Class     = $class0
                    <br />function     = $function0
                    <br />Line = $line0
                    </div>";
    }


    private static function getBacktraceInfo()
    {
        ## Danger Danger not sure if index 1 will work well, might depend o how deep?
        $i = 1;
        $file       = debug_backtrace()[$i]['file'];
        $line       = debug_backtrace()[$i]['line'];
        if (isset(debug_backtrace()[$i]['class'])) {
            $class      = debug_backtrace()[$i]['class'];
        }
        if (isset(debug_backtrace()[$i]['function'])) {
            $function   = debug_backtrace()[$i]['function'];
        }

        self::$traceLine = $file . ", Line: " . $line   ;
    }


    public static function boom(int|string|array $x = "o11aopsBoomBoom", int $exit = 1): void
    {

        self::getBacktraceInfo();
        if (is_array($x)) {
            self::printArrayPre($x);
            print "oopsBoomBoom";
        } else {
            print "<br /><br /><hr />" . $x;
            print "<br />oopsBoomBoom";
        }
        print "<br />oopsBoomBoom";
        print "<br />oopsBoomBoom";
        print "<br />oopsBoomBoom :" . self::$traceLine;
        print "<br />oopsBoomBoom";
        print "<br />oopsBoomBoom";
        print "<br />oopsBoomBoom";

        if ($exit === 1) {
            exit();
        }
    }

    private static function printArrayPre($arr): void
    {
        print "<pre>";
        print_r($arr);
        print "</pre>";
    }




    public static function oopsBoomBoom($ver = 33, $traceIndex = 0, array $options = array()): never
    {
        print "<br />oopsBoomBoom";
        print "<br />oopsBoomBoom";
        print "<br />oopsBoomBoom";
        print "<br />oopsBoomBoom";
        print "<br />oopsBoomBoom";
        print "<br />oopsBoomBoom";
        exit();
    }


    /**
     * # Displays a DIV with Logic Fatal-Error Information.
     *
     * - Anything with a $ver of less than 30 needs revisiting/fixing logic
     * - $msg Carried in Array, Example: "The code XXXX1 is bad and so is XXXX2".
     * - 30, 0, array('msg'=>'','val1'=>$val)
     * @param int $ver Version Number.
     * @param int $traceIndex How far back to trace.
     * @param array $options Example: array('msg'=>$msg,'val1'=>$val1, 'val2'=>$val2,'err'=>43)
     *
     *
     * @return void
     */
    public static function oopsBoom(int $ver = 33, int $traceIndex = 0, array $options = array()): void
    {
        $class0      = "";
        $function0   = "";
        $i = 0;
        $file0       = debug_backtrace()[$i]['file'];
        $line0       = debug_backtrace()[$i]['line'];
        if (isset(debug_backtrace()[$i]['class'])) {
            $class0      = debug_backtrace()[$i]['class'];
        }
        if (isset(debug_backtrace()[$i]['function'])) {
            $function0   = debug_backtrace()[$i]['function'];
        }


        $file1      = "";
        $class1     = "";
        $line1      = "";
        $function1  = "";
        if (isset(debug_backtrace()[1]['file'])) {
            $file1       = debug_backtrace()[1]['file'];
        }
        if (isset(debug_backtrace()[1]['line'])) {
            $line1 = debug_backtrace()[1]['line'];
        }
        if (isset(debug_backtrace()[1]['class'])) {
            $class1      = debug_backtrace()[1]['class'];
        }
        if (isset(debug_backtrace()[1]['function'])) {
            $function1   = debug_backtrace()[1]['function'];
        }

        if (isset(debug_backtrace()[2]['file'])) {
            $class2    = "";
            $function2 = "";
            $file2     = debug_backtrace()[2]['file'];
            $line2     = debug_backtrace()[2]['line'];
            if (isset(debug_backtrace()[2]['class'])) {
                $class2 = debug_backtrace()[2]['class'];
            }
            if (isset(debug_backtrace()[2]['function'])) {
                $function2 = debug_backtrace()[2]['function'];
            }
        }

        if (isset(debug_backtrace()[3]['file'])) {
            $class3    = "";
            $function3 = "";
            $file3     = debug_backtrace()[3]['file'];
            $line3     = debug_backtrace()[3]['line'];
            if (isset(debug_backtrace()[3]['class'])) {
                $class3 = debug_backtrace()[3]['class'];
            }
            if (isset(debug_backtrace()[3]['function'])) {
                $function3 = debug_backtrace()[3]['function'];
            }
        }

        if (isset(debug_backtrace()[4]['file'])) {
            $class4    = "";
            $function4 = "";
            $file4     = debug_backtrace()[4]['file'];
            $line4     = debug_backtrace()[4]['line'];
            if (isset(debug_backtrace()[4]['class'])) {
                $class4 = debug_backtrace()[4]['class'];
            }
            if (isset(debug_backtrace()[4]['function'])) {
                $function4 = debug_backtrace()[4]['function'];
            }
        }

        if (isset(debug_backtrace()[5]['file'])) {
            $class5    = "";
            $function5 = "";
            $file5     = debug_backtrace()[5]['file'];
            $line5     = debug_backtrace()[5]['line'];
            if (isset(debug_backtrace()[5]['class'])) {
                $class5 = debug_backtrace()[5]['class'];
            }
            if (isset(debug_backtrace()[5]['function'])) {
                $function5 = debug_backtrace()[5]['function'];
            }
        }


        if ($ver < 30) {
            $oops = "<div style='border: red solid 6px; padding:10px; width:50%;margin: auto'>BOOOM FIND ME 2341234
                    <br />File = $file0
                    <br />Class 	= $class0
                    <br />function 	= $function0
                    <br />Line = $line0
                    </div>";

            echo $oops;
            exit();
        }


        $defaults = array(
            'oopsType'  => "FATAL",
            'err'       => 1,
            'msg'       => null,
            'val1'      => null,
            'val2'      => null,
            'val3'      => null,
            'val4'      => null,
            'fix'       => "",  ## ver. 32
            'fixV1'     => null,  ## ver. 32
            'fixV2'     => null,  ## ver. 32
            'uniqueTag' => null,## ver. 32
            'ui'        => null,
        );
        $options = array_merge($defaults, $options);
        extract($options);

        if ($msg == "") {
            $msg = "Funky value";
        }

        //De::priLn('y', 'xxx', $msg);
        //De::priLn('y', 'xxx', $err);
        if ($err == 0) {
            $err = " FATAL-ERROR...";
            $msg = " JUST BOOOOOMMMMM. (Should never happen, REVISIT this error)...";
        }

        if ($err == 1) {
            $err = " FATAL-ERROR...";
        }

        if ($err == 2) {
            $err = " REVISIT...";
            $msg = " Missing OP? : XXXX1, for XXXX2/XXXX3. (REVISIT this error)...";
        }

        if ($err == 55) {
            $err = " FATAL-ERROR...";
            $msg = " Bad Index used in SQL Where Clause. Index: $val1";
        }

        if ($err == 56) {
            $err = " FATAL-ERROR...";
            $msg = " Missing Index used in SQL Where Clause. Index: $val1";
        }


        $fileA       = debug_backtrace()[$traceIndex]['file'];
        $lineA       = debug_backtrace()[$traceIndex]['line'];
        $functionA   = debug_backtrace()[$traceIndex]['function'];
        $A = subStr($fileA, 45);

        if (isset($val1)) {
            if ((str_contains($msg, "XXXX1"))) {
                $msg    = str_replace("XXXX1", '<span style="font-weight: bold; color:red;">(' . $val1 . ')</span>', $msg);
            } else {
                $msg    = $msg . ' : <span style="font-weight: bold; color:red;">(' . $val1 . ')</span>';
            }
        }

        if (isset($val2)) {
            // if ((strpos($msg,"XXXX2") !== false))
            if ((str_contains($msg, "XXXX2"))) {
                $msg    = str_replace("XXXX2", '<span style="font-weight: bold; color:red;">"' . $val2 . '"</span>', $msg);
            } else {
                $msg    = $msg . ' : <span style="font-weight: bold; color:red;">"' . $val2 . '"</span>';
            }
        }

        if (isset($val3)) {
            if ((str_contains($msg, "XXXX3"))) {
                $msg    = str_replace("XXXX3", '<span style="font-weight: bold; color:red;">' . $val3 . '</span>', $msg);
            } else {
                $msg = $msg . ' : <span style="font-weight: bold; color:red;">' . $val3 . '</span>';
            }
        }

        if (isset($val4)) {
        // if ((strpos($msg,"XXXX4") !== false))
            if ((str_contains($msg, "XXXX4"))) {
                $msg = str_replace("XXXX4", '<span style="font-weight: bold; color:red;">' . $val4 . '</span>', $msg);
            } else {
                $msg = $msg . ' : <span style="font-weight: bold; color:red;">' . $val4 . '</span>';
            }
        }

        ## ver. 32
        if (isset($fixV1)) {
            if ((str_contains($fix, "FFFF1"))) {
                $fix = str_replace("FFFF1", '<span style="font-weight: bold; color:green;">(' . $fixV1 . ')</span>', $fix);
            } else {
                $fix = $fix . ' : <span style="font-weight: bold; color:red;">(' . $fixV1 . ')</span>';
            }
        }

        ## ver. 32
        if (isset($fixV2)) {
            if ((str_contains($fix, "FFFF2"))) {
                $fix = str_replace("FFFF2", '<span style="font-weight: bold; color:green;">"' . $fixV2 . '"</span>', $fix);
            } else {
                $fix = $fix . ' : <span style="font-weight: bold; color:red;">"' . $fixV2 . '"</span>';
            }
        }
        //if ($oopsType=== 'FATAL')
        //  $xx = 'BOOOOOOOOOOOOMMMMM - Debug Opps FATAL ERROR';
        //else
        //  $xx = 'BOOOOOOOOOOOOMMMMM - Debug Opps WARNING';

        $oops       = " <div style='text-align:center; font-weight: bold; color:red; border-bottom: red 2px solid;'>$oopsType - $oopsType - BOOOOOOOOOMMM - Debug Opps $oopsType ERROR</div>";
        $oops      .= " $err <br />";
        $oops      .= " $msg <br />";
        ## ver. 32
        if (isset($fix)) {
            $oops      .= " <span style='font-weight: bold; color:darkgreen;'>FIX:</span>$fix <br />";
        }
        $oops      .= " <br /> Version: $ver - Trace: $traceIndex - UniqueTag: <span style='font-weight: bold; color:black;'>$ui $uniqueTag</span> <br />";
        $oops      .= " <br /> --------------- File0 : " . "<span style='font-weight: bold; color:red;'>$file0</span>";
        $oops      .= " <br /> --------------- Line0 : " . "<span style='font-weight: bold; color:red;'>$line0</span>";

        $oops  .= " <br /> --------------- Function0 : " . "<span style='font-weight: bold; color:red;'>$function0</span>";
        $oops  .= " <br /> --------------- Class0 : " . "<span style='font-weight: bold; color:red;'>$class0</span>";

        if (isset(debug_backtrace()[1]['file'])) {
            $oops .= " <hr />";
            $oops .= " <br /> --------------- File1 : " . "<span style='font-weight: bold; color:red;'>$file1</span>";
            $oops .= " <br /> --------------- Line1 : " . "<span style='font-weight: bold; color:red;'>$line1</span>";
            $oops .= " <br /> --------------- Function1 : " . "<span style='font-weight: bold; color:red;'>$function1</span>";
            $oops .= " <br /> --------------- Class1 : " . "<span style='font-weight: bold; color:red;'>$class1</span>";
        }

        if (isset(debug_backtrace()[2]['file'])) {
            $oops .= " <hr />";
            $oops .= " <br /> --------------- File2 : " . "<span style='font-weight: bold; color:red;'>$file2</span>";
            $oops .= " <br /> --------------- Line2 : " . "<span style='font-weight: bold; color:red;'>$line2</span>";
            $oops .= " <br /> --------------- Function2 : " . "<span style='font-weight: bold; color:red;'>$function2</span>";
            $oops .= " <br /> --------------- Class2 : " . "<span style='font-weight: bold; color:red;'>$class2</span>";
        }

        if (isset(debug_backtrace()[3]['file'])) {
            $oops .= " <hr />";
            $oops .= " <br /> --------------- File3 : " . "<span style='font-weight: bold; color:red;'>$file3</span>";
            $oops .= " <br /> --------------- Line3 : " . "<span style='font-weight: bold; color:red;'>$line3</span>";
            $oops .= " <br /> --------------- Function3 : " . "<span style='font-weight: bold; color:red;'>$function3</span>";
            $oops .= " <br /> --------------- Class3 : " . "<span style='font-weight: bold; color:red;'>$class3</span>";
        }

        if (isset(debug_backtrace()[4]['file'])) {
            $oops .= " <hr />";
            $oops .= " <br /> --------------- File4 : " . "<span style='font-weight: bold; color:red;'>$file4</span>";
            $oops .= " <br /> --------------- Line4 : " . "<span style='font-weight: bold; color:red;'>$line4</span>";
            $oops .= " <br /> --------------- Function4 : " . "<span style='font-weight: bold; color:red;'>$function4</span>";
            $oops .= " <br /> --------------- Class4 : " . "<span style='font-weight: bold; color:red;'>$class4</span>";
        }

        if (isset(debug_backtrace()[5]['file'])) {
            $oops .= " <hr />";
            $oops .= " <br /> --------------- File5 : " . "<span style='font-weight: bold; color:red;'>$file5</span>";
            $oops .= " <br /> --------------- Line5 : " . "<span style='font-weight: bold; color:red;'>$line5</span>";
            $oops .= " <br /> --------------- Function5 : " . "<span style='font-weight: bold; color:red;'>$function5</span>";
            $oops .= " <br /> --------------- Class5 : " . "<span style='font-weight: bold; color:red;'>$class5</span>";
        }

        $oops = "<div style='border: red solid 6px; padding:10px; width:50%;margin: auto'>$oops</div>";

        echo $oops;
        if ($oopsType === 'FATAL') {
            exit();
        }
    }

    /**
     * # Displays a DIV with Logic Fatal-Error Information.
     *
     * - Anything with a $ver of less than 30 needs revisiting/fixing logic
     * - $msg Carried in Array, Example: "The code XXXX1 is bad and so is XXXX2".
     * - 30, 0, array('msg'=>'','val1'=>$val)
     * @param int $ver Version Number.
     * @param int $traceIndex How far back to trace.
     * @param array $options Example: array('msg'=>$msg,'val1'=>$val1, 'val2'=>$val2,'err'=>43)
     *
     *
     * @return void
     */
    public static function invalidOopsBoom(int $ver = 100, int $traceIndex = 0, array $options = array()): void
    {
        /* Ver. 32
                $msg    = 'XXXX2 for XXXX1, was not found or was not initialized.';
                $val1   = $this->ttObj->page."/".$this->ttObj->op;
                $val2   = 'sapPageMenuBtnSet';
                $fix    = 'Go to FFFF1 and set FFFF2 property value.';
                $fixV1  = 'Class_Page_Sap_Xxxxxx().initializePage';
                $fixV2  = 'sapPageMenuBtnSet';
                DebugHelpRt::oopsBoom(32, 0, array('uniqueTag'=>"crapiking",'msg'=>$msg,'fix'=>$fix,'val1'=>$val1,'val2'=>$val2,'fix'=>$fix,'fixV1'=>$fixV1,'fixV2'=>$fixV2,));
        */


        //if (!ADMIN) {
        //    ob_clean();
        //    echo 'oops';
        //    exit();
        //}
        $class0      = "";
        $function0   = "";
        $i = 0;
        $file0       = debug_backtrace()[$i]['file'];
        $line0       = debug_backtrace()[$i]['line'];
        if (isset(debug_backtrace()[$i]['class'])) {
            $class0      = debug_backtrace()[$i]['class'];
        }
        if (isset(debug_backtrace()[$i]['function'])) {
            $function0   = debug_backtrace()[$i]['function'];
        }


        $file1      = "";
        $class1     = "";
        $line1      = "";
        $function1  = "";
        if (isset(debug_backtrace()[1]['file'])) {
            $file1       = debug_backtrace()[1]['file'];
        }
        if (isset(debug_backtrace()[1]['line'])) {
            $line1 = debug_backtrace()[1]['line'];
        }
        if (isset(debug_backtrace()[1]['class'])) {
            $class1      = debug_backtrace()[1]['class'];
        }
        if (isset(debug_backtrace()[1]['function'])) {
            $function1   = debug_backtrace()[1]['function'];
        }

        if (isset(debug_backtrace()[2]['file'])) {
            $class2    = "";
            $function2 = "";
            $file2     = debug_backtrace()[2]['file'];
            $line2     = debug_backtrace()[2]['line'];
            if (isset(debug_backtrace()[2]['class'])) {
                $class2 = debug_backtrace()[2]['class'];
            }
            if (isset(debug_backtrace()[2]['function'])) {
                $function2 = debug_backtrace()[2]['function'];
            }
        }

        if (isset(debug_backtrace()[3]['file'])) {
            $class3    = "";
            $function3 = "";
            $file3     = debug_backtrace()[3]['file'];
            $line3     = debug_backtrace()[3]['line'];
            if (isset(debug_backtrace()[3]['class'])) {
                $class3 = debug_backtrace()[3]['class'];
            }
            if (isset(debug_backtrace()[3]['function'])) {
                $function3 = debug_backtrace()[3]['function'];
            }
        }

        if (isset(debug_backtrace()[4]['file'])) {
            $class4    = "";
            $function4 = "";
            $file4     = debug_backtrace()[4]['file'];
            $line4     = debug_backtrace()[4]['line'];
            if (isset(debug_backtrace()[4]['class'])) {
                $class4 = debug_backtrace()[4]['class'];
            }
            if (isset(debug_backtrace()[4]['function'])) {
                $function4 = debug_backtrace()[4]['function'];
            }
        }

        if (isset(debug_backtrace()[5]['file'])) {
            $class5    = "";
            $function5 = "";
            $file5     = debug_backtrace()[5]['file'];
            $line5     = debug_backtrace()[5]['line'];
            if (isset(debug_backtrace()[5]['class'])) {
                $class5 = debug_backtrace()[5]['class'];
            }
            if (isset(debug_backtrace()[5]['function'])) {
                $function5 = debug_backtrace()[5]['function'];
            }
        }


        $defaults = array(
            'oopsType'  => "FATAL",
            'err'       => 1,
            'msg'       => null,
            'val1'      => null,
            'val2'      => null,
            'val3'      => null,
            'val4'      => null,
            'fix'       => "",  ## ver. 32
            'fixV1'     => null,  ## ver. 32
            'fixV2'     => null,  ## ver. 32
            'uniqueTag' => null,## ver. 32
            'ui'        => null,
        );
        $options = array_merge($defaults, $options);
        extract($options);

        //print "<br />" . $xxx;
        //exit();
        if ($msg == "") {
            $msg = "Funky value";
        }

        //De::priLn('y', 'xxx', $msg);
        //De::priLn('y', 'xxx', $err);
        if ($err == 0) {
            $err = " 0FATAL-ERROR...";
            $msg = " JUST BOOOOOMMMMM. (Should never happen, REVISIT this error)...";
        }

        if ($err == 1) {
            $err = " 1FATAL-ERROR...";
        }

        if ($err == 2) {
            $err = " 2REVISIT...";
            $msg = " Missing OP? : XXXX1, for XXXX2/XXXX3. (REVISIT this error)...";
        }

        if ($err == 55) {
            $err = " 55FATAL-ERROR...";
            $msg = " Bad Index used in SQL Where Clause. Index: $val1";
        }

        if ($err == 56) {
            $err = " 56FATAL-ERROR...";
            $msg = " Missing Index used in SQL Where Clause. Index: $val1";
        }


        $fileA       = debug_backtrace()[$traceIndex]['file'];
        $lineA       = debug_backtrace()[$traceIndex]['line'];
        $functionA   = debug_backtrace()[$traceIndex]['function'];
        $A = subStr($fileA, 45);

        if (isset($val1)) {
            if ((str_contains($msg, "XXXX1"))) {
                $msg    = str_replace("XXXX1", '<span style="font-weight: bold; color:red;">(' . $val1 . ')</span>', $msg);
            } else {
                $msg    = $msg . ' : <span style="font-weight: bold; color:red;">(' . $val1 . ')</span>';
            }
        }

        if (isset($val2)) {
            if ((str_contains($msg, "XXXX2"))) {
                $msg    = str_replace("XXXX2", '<span style="font-weight: bold; color:red;">"' . $val2 . '"</span>', $msg);
            } else {
                $msg    = $msg . ' : <span style="font-weight: bold; color:red;">"' . $val2 . '"</span>';
            }
        }

        if (isset($val3)) {
            if ((str_contains($msg, "XXXX3"))) {
                $msg    = str_replace("XXXX3", '<span style="font-weight: bold; color:red;">' . $val3 . '</span>', $msg);
            } else {
                $msg = $msg . ' : <span style="font-weight: bold; color:red;">' . $val3 . '</span>';
            }
        }

        if (isset($val4)) {
            if ((str_contains($msg, "XXXX4"))) {
                $msg    = str_replace("XXXX4", '<span style="font-weight: bold; color:red;">' . $val4 . '</span>', $msg);
            } else {
                $msg = $msg . ' : <span style="font-weight: bold; color:red;">' . $val4 . '</span>';
            }
        }

        ## ver. 32
        if (isset($fixV1)) {
            if ((str_contains($fix, "FFFF1"))) {
                $fix = str_replace("FFFF1", '<span style="font-weight: bold; color:green;">(' . $fixV1 . ')</span>', $fix);
            } else {
                $fix = $fix . ' : <span style="font-weight: bold; color:red;">(' . $fixV1 . ')</span>';
            }
        }

        ## ver. 32
        if (isset($fixV2)) {
            if ((str_contains($fix, "FFFF2"))) {
                $fix = str_replace("FFFF2", '<span style="font-weight: bold; color:green;">"' . $fixV2 . '"</span>', $fix);
            } else {
                $fix = $fix . ' : <span style="font-weight: bold; color:red;">"' . $fixV2 . '"</span>';
            }
        }
        //if ($oopsType=== 'FATAL')
        //    $xx = 'BOOOOOOOOOOOOMMMMM - Debug Opps FATAL ERROR';
        //else
        //    $xx = 'BOOOOOOOOOOOOMMMMM - Debug Opps WARNING';

        $oops       = " <div style='text-align:center; font-weight: bold; color:red; border-bottom: red 2px solid;'>
                        $oopsType ERROR - Invalid Programmer Error
                        </div>";
        $oops      .= " $err <br />";
        $oops      .= " $msg <br />";

        ## ver. 32
        if (isset($fix)) {
            $oops      .= " <span style='font-weight: bold; color:darkgreen;'>FIX:</span>$fix <br />";
        }

        $oops      .= " <br /> Version: $ver - Trace: $traceIndex - UniqueTag: <span style='font-weight: bold; color:black;'>$ui $uniqueTag</span> <br />";
        $oops      .= " <br /> --------------- File0 : " . "<span style='font-weight: bold; color:red;'>$file0</span>";
        $oops      .= " <br /> --------------- Line0 : " . "<span style='font-weight: bold; color:red;'>$line0</span>";

        //if (isset(debug_backtrace()[1]['function']))
        $oops  .= " <br /> --------------- Function0 : " . "<span style='font-weight: bold; color:red;'>$function0</span>";
        //if (isset(debug_backtrace()[1]['class']))
        $oops  .= " <br /> --------------- Class0 : " . "<span style='font-weight: bold; color:red;'>$class0</span>";

        if (isset(debug_backtrace()[1]['file'])) {
            $oops .= " <hr />";
            $oops .= " <br /> --------------- File1 : " . "<span style='font-weight: bold; color:red;'>$file1</span>";
            $oops .= " <br /> --------------- Line1 : " . "<span style='font-weight: bold; color:red;'>$line1</span>";
            $oops .= " <br /> --------------- Function1 : " . "<span style='font-weight: bold; color:red;'>$function1</span>";
            $oops .= " <br /> --------------- Class1 : " . "<span style='font-weight: bold; color:red;'>$class1</span>";
        }

        if (isset(debug_backtrace()[2]['file'])) {
            $oops .= " <hr />";
            $oops .= " <br /> --------------- File2 : " . "<span style='font-weight: bold; color:red;'>$file2</span>";
            $oops .= " <br /> --------------- Line2 : " . "<span style='font-weight: bold; color:red;'>$line2</span>";
            $oops .= " <br /> --------------- Function2 : " . "<span style='font-weight: bold; color:red;'>$function2</span>";
            $oops .= " <br /> --------------- Class2 : " . "<span style='font-weight: bold; color:red;'>$class2</span>";
        }

        if (isset(debug_backtrace()[3]['file'])) {
            $oops .= " <hr />";
            $oops .= " <br /> --------------- File3 : " . "<span style='font-weight: bold; color:red;'>$file3</span>";
            $oops .= " <br /> --------------- Line3 : " . "<span style='font-weight: bold; color:red;'>$line3</span>";
            $oops .= " <br /> --------------- Function3 : " . "<span style='font-weight: bold; color:red;'>$function3</span>";
            $oops .= " <br /> --------------- Class3 : " . "<span style='font-weight: bold; color:red;'>$class3</span>";
        }

        if (isset(debug_backtrace()[4]['file'])) {
            $oops .= " <hr />";
            $oops .= " <br /> --------------- File4 : " . "<span style='font-weight: bold; color:red;'>$file4</span>";
            $oops .= " <br /> --------------- Line4 : " . "<span style='font-weight: bold; color:red;'>$line4</span>";
            $oops .= " <br /> --------------- Function4 : " . "<span style='font-weight: bold; color:red;'>$function4</span>";
            $oops .= " <br /> --------------- Class4 : " . "<span style='font-weight: bold; color:red;'>$class4</span>";
        }

        if (isset(debug_backtrace()[5]['file'])) {
            $oops .= " <hr />";
            $oops .= " <br /> --------------- File5 : " . "<span style='font-weight: bold; color:red;'>$file5</span>";
            $oops .= " <br /> --------------- Line5 : " . "<span style='font-weight: bold; color:red;'>$line5</span>";
            $oops .= " <br /> --------------- Function5 : " . "<span style='font-weight: bold; color:red;'>$function5</span>";
            $oops .= " <br /> --------------- Class5 : " . "<span style='font-weight: bold; color:red;'>$class5</span>";
        }

        $oops = "<div style='border: red solid 6px; padding:10px; width:50%;margin: auto'>$oops</div>";

        echo $oops;
        if ($oopsType === 'FATAL') {
            exit();
        }
    }

    /**
     * # Used to retrieve File Name and Line Number for the script that called the DDL.
     * ---
     * *Usage Example:*
     * * <code>Class_Debug_HelperRt::oopsSqlBoom(1);</code>
     *
     * - Always One
     * @param int $traceIndex
     * @return string Html code containing the File name and Line number of the script that called the DDL
     */
    public static function oopsSqlBoom(int $traceIndex): string
    {
        $oops       = " File : " . basename(debug_backtrace()[$traceIndex]['file']);
        $oops      .= ' Line : ' . debug_backtrace()[$traceIndex]['line'];
        $oops      .= "<span style='font-weight: bold; background-color: lime;'>$oops</span>";
        return $oops;
    }

    public static function oopsBoomxxX($traceIndex = 0, $msg = "XXX"): void
    {
        if (!ADMIN) {
            ob_clean();
            echo 'oops';
            exit();
        }
        $class0      = "";
        $function0   = "";
        $file0       = debug_backtrace()[0]['file'];
        $line0       = debug_backtrace()[0]['line'];
        if (isset(debug_backtrace()[0]['class'])) {
            $class0      = debug_backtrace()[0]['class'];
        }
        if (isset(debug_backtrace()[0]['function'])) {
            $function0   = debug_backtrace()[0]['function'];
        }


        $class1      = "";
        $function1   = "";
        $file1       = debug_backtrace()[1]['file'];
        $line1       = debug_backtrace()[1]['line'];
        if (isset(debug_backtrace()[1]['class'])) {
            $class1      = debug_backtrace()[1]['class'];
        }
        if (isset(debug_backtrace()[1]['function'])) {
            $function1   = debug_backtrace()[1]['function'];
        }

        $class2      = "";
        $function2   = "";
        $file2       = debug_backtrace()[2]['file'];
        $line2       = debug_backtrace()[2]['line'];
        if (isset(debug_backtrace()[2]['class'])) {
            $class2      = debug_backtrace()[2]['class'];
        }
        if (isset(debug_backtrace()[2]['function'])) {
            $function2   = debug_backtrace()[2]['function'];
        }

        if (isset(debug_backtrace()[3]['file'])) {
            $class3    = "";
            $function3 = "";
            $file3     = debug_backtrace()[3]['file'];
            $line3     = debug_backtrace()[3]['line'];
            if (isset(debug_backtrace()[3]['class'])) {
                $class3 = debug_backtrace()[3]['class'];
            }
            if (isset(debug_backtrace()[3]['function'])) {
                $function3 = debug_backtrace()[3]['function'];
            }
        }

        if (isset(debug_backtrace()[4]['file'])) {
            $class4    = "";
            $function4 = "";
            $file4     = debug_backtrace()[4]['file'];
            $line4     = debug_backtrace()[4]['line'];
            if (isset(debug_backtrace()[4]['class'])) {
                $class4 = debug_backtrace()[4]['class'];
            }
            if (isset(debug_backtrace()[4]['function'])) {
                $function4 = debug_backtrace()[4]['function'];
            }
        }

        if (isset(debug_backtrace()[5]['file'])) {
            $class5    = "";
            $function5 = "";
            $file5     = debug_backtrace()[5]['file'];
            $line5     = debug_backtrace()[5]['line'];
            if (isset(debug_backtrace()[5]['class'])) {
                $class5 = debug_backtrace()[5]['class'];
            }
            if (isset(debug_backtrace()[5]['function'])) {
                $function5 = debug_backtrace()[5]['function'];
            }
        }


        $msg = '<span style="font-weight: bold; color:red;">' . $msg . '</span>';


        $oops       = " <div style='text-align:center; font-weight: bold; color:red; border-bottom: red 2px solid;'>BOOOOOOOOOOOOMMMMM - Debug Opps Fatal Error</div>";
        $oops      .= " $msg <br />";
        $oops      .= " trace: $traceIndex <br />";
        $oops      .= " <br /> --------------- File0 : " . "<span style='font-weight: bold; color:red;'>$file0</span>";
        $oops      .= " <br /> --------------- Line0 : " . "<span style='font-weight: bold; color:red;'>$line0</span>";

        //if (isset(debug_backtrace()[1]['function']))
        $oops  .= " <br /> --------------- Function0 : " . "<span style='font-weight: bold; color:red;'>$function0</span>";
        //if (isset(debug_backtrace()[1]['class']))
        $oops  .= " <br /> --------------- Class0 : " . "<span style='font-weight: bold; color:red;'>$class0</span>";

        $oops .= " <hr />";
        $oops .= " <br /> --------------- File1 : " . "<span style='font-weight: bold; color:red;'>$file1</span>";
        $oops .= " <br /> --------------- Line1 : " . "<span style='font-weight: bold; color:red;'>$line1</span>";
        $oops .= " <br /> --------------- Function1 : " . "<span style='font-weight: bold; color:red;'>$function1</span>";
        $oops .= " <br /> --------------- Class1 : " . "<span style='font-weight: bold; color:red;'>$class1</span>";

        $oops  .= " <hr />";
        $oops       .= " <br /> --------------- File2 : " . "<span style='font-weight: bold; color:red;'>$file2</span>";
        $oops       .= " <br /> --------------- Line2 : " . "<span style='font-weight: bold; color:red;'>$line2</span>";
        $oops       .= " <br /> --------------- Function2 : " . "<span style='font-weight: bold; color:red;'>$function2</span>";
        $oops       .= " <br /> --------------- Class2 : " . "<span style='font-weight: bold; color:red;'>$class2</span>";


        $oops = "<div style='border: red solid 6px; padding:10px; width:50%;margin: auto'>$oops</div>";


        echo $oops;
        //return;
    }


    public static function debugBlowUp(int $traceIndex, string $msg, int $errNum): void
    {
        //$traceIndex = 5;
        if (ADMIN) {
            $error      = "BOOM Error: $errNum  |  $msg";
            $oops       = " File : " . basename(debug_backtrace()[$traceIndex]['file']);
            $oops      .= ' Line : ' . debug_backtrace()[$traceIndex]['line'];
            $oops       = "$error | <span style='font-weight: bold; background-color: lime;'>$oops</span>";
        } else {
            $error      = "BOOM Error: $errNum";
            $oops       = $error;
        }

        echo $oops;

        //return;
    }


    /**
     * # lazyBackTrack
     */
    public static function lazyBackTrack(string $type = "", string $var = ""): void
    {
        //print "<br />".subStr(__FILE__,23).": ".__LINE__." var : " . DebugHelpRt::$tag;
        //DebugHelpRt::$tag="boo";

        if (DebugHelpRt::$tag == "boo") {
            $i          = 0;
            $file       = basename(debug_backtrace()[$i]['file']);
            $line       = basename(debug_backtrace()[$i]['line']);
            $function   = basename(debug_backtrace()[$i]['function']);
            $class      = basename(debug_backtrace()[$i]['class']);

            if ($type == "btnObj") {
                $type = " Inside ButtonBaseComplex class";
            }

            //if (isset($val4))
            $val4 = "";
            $msg  = "";
            $msg  = str_replace("XXXX4", '<span style="font-weight: bold; color:red;">' . $val4 . '</span>', $msg);

            $oops = " <div style='text-align:center; font-weight: bold; color:red; border-bottom: red 2px solid;'>Debug INFO for $type</div>";

            $oops .= " $msg BREAK LOCATION";
            $oops .= " <br /> --------------- File : " . "<span style='font-weight: bold; color:red;'>" . $file . "</span>";
            $oops .= " <br /> --------------- Line : " . "<span style='font-weight: bold; color:red;'>" . $line . "</span>";
            //if (isset($function))
                $oops .= " <br /> --------------- Function : " . "<span style='font-weight: bold; color:red;'>" . $function . "</span>";
            //if (isset($class))
                $oops .= " <br /> --------------- Class : " . "<span style='font-weight: bold; color:red;'>" . $class . "</span>";

            ###############################################################################################################
            ###############################################################################################################
            ###############################################################################################################
            ###############################################################################################################
            $i = 2;
            $file     = basename(debug_backtrace()[$i]['file']);
            $line     = basename(debug_backtrace()[$i]['line']);
            $function = basename(debug_backtrace()[$i]['function']);

            if (isset(debug_backtrace()[$i]['class'])) {
                $class    = basename(debug_backtrace()[$i]['class']);
            }
            $oops     .= " <br /><br /><br /> FOR :";
            $oops     .= " <br /> --------------- File : " . "<span style='font-weight: bold; color:red;'>" . $file . "</span>";
            $oops     .= " <br /> --------------- Line : " . "<span style='font-weight: bold; color:red;'>" . $line . "</span>";
            if (isset(debug_backtrace()[$i]['function'])) {
                $oops .= " <br /> --------------- Function : " . "<span style='font-weight: bold; color:red;'>" . $function . "</span>";
            }
            if (isset(debug_backtrace()[$i]['class'])) {
                $oops .= " <br /> --------------- Class : " . "<span style='font-weight: bold; color:red;'>" . $class . "</span>";
            }

            $oops .= " <br /> --------------- Class : " . "<span style='font-weight: bold; color:red;'>" . $var . "</span>";

            $oops = "<div style='border: red solid 6px; padding:10px; width:50%;margin: auto'>$oops</div>";
            echo $oops;
            DebugHelpRt::$tag = "";
        }
    }


    //public static function printLineInfo($exitSw='n', $textDisp='xxx', $value=null, $printIt="e", array $options=array()){
    public static function priLn2(string $exitSw = 'n', string $label = 'xxx', ?string $value = null, string $printIt = "e", array $options = array()): void
    {
        if (self::$debugLine === 'off') {
            return;
        }

        $first = substr($label, 0, 1);
        if ($first === '$') {
            $label = 'xxx ' . substr($label, 1);
            //De::priLn('y', 'xxx', $label);
        }
        ## comment this out to print everything
        //$printIt = "everything";

        ## chanter it here to make it match
        $printItKey = "moo2";

        if (DEBUG_HIDE_MSG) {     #hide/unhide code = ind101
            return;
            //print "<br />".subStr(__FILE__,23).": ".__LINE__." BOOOMMMMM";
            //exit();
        }

        $defaults = array(
            'textx'    => 'xxx : ',
            'pgNmx'    => 'fofo',
        );
        $options = array_merge($defaults, $options);
        extract($options);


        if (ADMIN) {
            //print "hello";
            $trace = debug_backtrace();
            $i = 0;
            $file = $trace[$i]["file"];
            $line = $trace[$i]["line"];

            print  "<div style='border:6px solid pink;  background: lime; font-weight:bold; color:red;'>"
            . subStr($file, 23) . " : "
            . $line . "<span style='position:absolute; left:550px;'> $label  :: $value</span></div>";

            if ($exitSw == 'y') {
                exit();
            }
        }
        //return;

        #print "<br />".__FUNCTION__." | ".subStr(__FILE__,23).": ".__LINE__." xxx : ". $inVfile;
        #exit();
        #print "<br />".subStr(__FILE__,23).": ".__LINE__." xxx : ". $xxx;
        #exit();
    }

    /**
     * # Prints a Line Number for Debugging
     *
     *  - n = display message but does not Exit()
     *  - y = display message and Exit()
     *  - h = hide message AND does not Exit()
     * @param string $exitSw
     * @param string $label Label to display
     * @param string|null $value Value to display
     * @param string $printIt ffff to "e"
     * @param array $options ...
     */
    public static function priLn(string $exitSw = 'n', string $label = 'xxx', ?string $value = null, string $printIt = "e", array $options = array()): void
    {
        $first = substr($label, 0, 1);
        if ($first === '$') {
            $label = 'xxx ' . substr($label, 1);
            //De::priLn('y', 'xxx', $label);
        }
        ## comment this out to print everything
        //$printIt = "everything";

        ## chanter it here to make it match
        $printItKey = "moo2";

        $defaults = array(
            'textx'    => 'xxx : ',
            'pgNmx'    => 'fofo',
        );
        $options = array_merge($defaults, $options);
        extract($options);

        //print "<br />".subStr(__FILE__,23).": ".__LINE__." xxx : " . $foo;

        if (ADMIN) {
            //print "hello";
            $trace = debug_backtrace();
            $i = 0;
            $file = $trace[$i]["file"];
            $line = $trace[$i]["line"];

            print  "<span style='font-weight:bold; color:red;'><br />LLINE : " . subStr($file, 24) . " : " . $line . " $label  : $value</span>";//($x)
            if ($exitSw == 'y') {
                exit();
            }
        }
    }

    public static function x(string $exitSw = 'n', string $label = 'xxx', $value = null, string $printIt = "e", array $options = array()): string
    {
        ## comment this out to print everything
        //$printIt = "everything";

        ## chanter it here to make it match
        $printItKey = "moo2";

        if (DEBUG_HIDE_MSG) {     #hide/unhide code = ind101
            return "";
            //print "<br />".subStr(__FILE__,23).": ".__LINE__." BOOOMMMMM";
            //exit();
        }


        $defaults = array(
            'textx'    => 'xxx : ',
            'pgNmx'    => 'fofo',
        );
        $options = array_merge($defaults, $options);
        extract($options);

        //print "<br />".subStr(__FILE__,23).": ".__LINE__." xxx : " . $foo;
        //exit();


        if (ADMIN) {
            //print "hello";
            $trace = debug_backtrace();
            $i = 0;
            $file = $trace[$i]["file"];
            $line = $trace[$i]["line"];

            //print  "<span style='font-weight:bold; color:red;'><br /> ".subStr($trace[0]["file"],23)." : ".$trace[0]["line"] . " $textDisp : $value</span>";
            //print  "<span style='font-weight:bold; color:red;'><br /> ".subStr($trace[2]["file"],23)." : ".$trace[2]["line"] . " $textDisp : $value</span>";
            return  "<span style='font-weight:bold; color:red;'>" . subStr($file, 23) . " : " . $line . " $label : $value</span>";
            //if ($exitSw == 'y')
            //    exit();
        }
        return "";

        #print "<br />".__FUNCTION__." | ".subStr(__FILE__,23).": ".__LINE__." xxx : ". $inVfile;
        #exit();
        #print "<br />".subStr(__FILE__,23).": ".__LINE__." xxx : ". $xxx;
        #exit();
    }

    public static function printMulti(string $var): void
    {
        print "<pre>";
        print $var;
        print "</pre>";
    }

    public static function pre(array|object $arr, int $exit = 1, ?string $value = null): void
    {
        print "<hr />$value";
        print "<pre>";
        print_r($arr);
        echo '</pre>';
        if ($exit === 1) {
            exit();
        }
    }

    /**
     * Outputs a JSON-encoded response for debugging purposes and optionally exits the script.
     *
     * @param string|int $exit Determines whether to exit the script after outputting the response.
     *                         Use "1" or any truthy value to exit, or "0" or falsy value to continue execution.
     * @param string $label An optional label to include in the output for context or identification.
     * @param mixed $arr The value to be outputted. Can be any data type.
     * @param bool $trace Whether to include a stack trace in the output. Defaults to true.
     *
     * @return void
     */
    public static function j(
        string|int $exit = "1",
        string $label = "",
        mixed $arr = "--",
        bool $trace = true
    ): void {
        $rr = '';
        if ($trace) {
            self::getBacktraceInfo();
            $rr = "&nbsp -- Trace : " . self::$traceLine;
        }
        $label = $label ? "$label : " : '';


        if (is_bool($arr)) {
            $b = $arr ? '0 (false)' : '1 (true)';
            print "<br />$label $b" . $rr;
        } elseif (is_null($arr)) {
            print "<br />$label NULL" . $rr;
        } elseif (is_string($arr)) {
            print "<br />$label $arr" . $rr;
        } elseif (is_numeric($arr)) {
            print "<br />$label $arr" . $rr;
        } else {
            print "<hr />$label" . $rr;
            print "<pre>";
            print_r($arr);
            echo '</pre><hr />';
        }
        if ($exit === 1) {
             exit();
        }
    }

    public static function p(bool|null|string|int|array|object $arr = "--", int $exit = 1, ?string $label = ""): void
    {
        self::getBacktraceInfo();

        if (is_bool($arr)) {
            print "<br />$label -- $arr -- FRACKING BOOL --Trace--" . self::$traceLine;
        } elseif (is_null($arr)) {
            print "<br />$label NULL --Trace--" . self::$traceLine;
        } elseif (is_string($arr)) {
            print "<br />$label $arr &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp&nbsp&nbsp --Trace--" . self::$traceLine;
        } elseif (is_numeric($arr)) {
            print "<br />$label $arr &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp&nbsp&nbsp --Trace--" . self::$traceLine;
        } else {
            print "<hr />$label --Trace--" . self::$traceLine;
            print "<pre>";
            print_r($arr);
            echo '</pre><hr />';
        }
        if ($exit === 1) {
             exit();
        }
    }

    /**
     * @param string $exitSw
     * @param null|string $value
     * @param array  $options
     */
    public static function printLineWarning(string $exitSw = '', ?string $value = '', array $options = array()): void
    {
        if (DEBUG_HIDE_MSG) {     #hide/unhide code = ind101
            return;
            //print "<br />".subStr(__FILE__,23).": ".__LINE__." BOOOMMMMM";
            //exit();
        }

        //print_r($options);
        //print "<br />".subStr(__FILE__,23).": ".__LINE__." xxx : " . $options['foo'];
        //exit();
        $defaults = array(
            'code'      => 'z',
        );
        $options = array_merge($defaults, $options);
        extract($options);


        //print "<br />".subStr(__FILE__,23).": ".__LINE__." xxx : " . $value;
        //print "<br />".subStr(__FILE__,23).": ".__LINE__." xxx : " . $code;
        //exit();
        $msg = "";
        $msgSpan = "<span style='font-weight: bolder; background:yellow; padding:0 15px; color:black;'>($value)</span>";
        //$msgSpan="";
        if ($code == "BtnUpgradeNeeded") {
            $msg = "Outdate Btn Version of $msgSpan, Please revisit the source code, and upgrade the button code";
        }

        if (ADMIN) {
            //print "hello";
            $trace = debug_backtrace();
            $i = 1;
            $file = $trace[$i]["file"];
            $line = $trace[$i]["line"];

            $lineLoc = $trace[0]["file"] . $trace[0]["line"];
            $lineLoc =  subStr($lineLoc, 45) . "||||}}"; ## this is the physical location line of "DebugHelpRt::printLineWarning(" statement.

            print "<span style='display:block; margin-bottom:2px; padding:0; border:2px solid black; color:red;'>$lineLoc " . subStr($file, 23) . " : " . $line . " $msg</span>";
            if ($exitSw == 'x') {
                exit();
            }
        }

        #print "<br />".__FUNCTION__." | ".subStr(__FILE__,23).": ".__LINE__." xxx : ". $inVfile;
        #exit();
        #print "<br />".subStr(__FILE__,23).": ".__LINE__." xxx : ". $xxx;
        #exit();
    }


    public static function setFileName(string $var): void
    {
        self::$fileName = $var;
        //print "<br />".__FUNCTION__." | ".subStr(__FILE__,23).": ".__LINE__." setCollFileName : " . $this->collFileName;
        //exit();
    }

    public static function setDebugLocInfo(string $var): void
    {
        $var1 = basename($var, "0");
        $var2 = substr($var, 7, 16);
        self::$locInfo = $var2 . "--" . $var1;
    }

    public static function getDebugLocInfo(): string
    {
        return self::$locInfo;
    }


    public function getFileName(): string
    {
        return self::$fileName;
    }


    public function getCnt(): int
    {
        return self::$cnt;
    }

    public static function bumpUpCnt(): void
    {
        self::$cnt = self::$cnt + 1;
    }

    public function getTest(): string
    {
        return self::$testVar;
    }
}
# 1526
