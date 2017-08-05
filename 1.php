<!DOCTYPE html>
<html>
<head>
    <title>C code</title>
</head>
<body>
<?php

/*Initializing Global Variables Starts*/
  $lineCounter=0;
  $parse="";
  $loop=false;
  $formattedLineNumber=0;
  $format="";
/*Initializing Global Variables Ends*/

/*Formatting Code Starts*/
  function checkBrace (&$line)
    {
      if(strpos($line, "{")!==false && strpos($line, "{")!==0) {$line=substr_replace($line,"\n"."{ ",strpos($line,"{"), 1);}

      if(strrpos($line, "}")===(strlen($line)-1)) {$line=substr_replace($line,"\n "."}",strpos($line,"}"), 1);}
    }
  $source = fopen("file-for-loop-1.c", "r+") or die("Unable to open file!");
  $formatted = fopen("file-for-loop-1-formatted.c", "w+") or die("Unable to open file!");

  while (!feof($source))
   {
      $line=trim(fgets($source));
      checkBrace ($line);
      fwrite($formatted,"\n".$line);
   }
  fclose($source);

/*Formatting Code Ended*/

/*For Loop Class Starts*/
  class ForLoop
  {
   protected $StartlineNumber;
   protected $EndLineNumber;
   protected $braceCounter;
   protected $forBlock;
   //protected $children=[];
   protected $for;
   protected $init;
   protected $step;
   protected $finalForBlock;

    public function __construct()
      {
        $StartlineNumber=0;
        $EndLineNumber=0;
        $braceCounter=0;
        $forBlock="";
        $finalForBlock="";
        $for="";
        $init="";
        $step="";
      }

    public function getStartLineNumber ()
      {
        return $this ->StartlineNumber;
      }

    public function setStartLineNumber ($lineCounter)
      {
        $this->StartlineNumber= $lineCounter;
      }

    public function getEndLineNumber ()
      {
        return $this->EndlineNumber;
      }

    public function setEndLineNumber ($lineCounter)
      {
        $this->EndlineNumber= $lineCounter;
      }

    public function getBraceCounter ()
      {
        return $this->braceCounter;
      }

    public function setBraceCounter ()
      {
        $this->braceCounter++;
      }

    public function ResetBraceCounter ()
      {
        $this->braceCounter--;
      }

    public function getForBlock ()
      {
        return $this->forBlock;
      }
    public function setForBlock ($line)
      {
        $this->forBlock=$this->forBlock.$line."\n";
      }

    public function getFor ()
      {
        return $this->for;
      }

    public function setFor ($line)
      {
        $this->for=trim(substr($line, 0,strrpos($line,")")+1));
      }

    public function getInit ()
      {
        return $this->init;
      }

    public function setInit ($line)
      {
        $this->init=trim(substr($this->for,strpos($this->for,"(")+1,strpos($this->for,";")-3));
      }

    public function getStep ()
      {
        return $this->step;
      }

    public function setStep ($for)
      {
        $this->step=trim(substr($this->for, strrpos($this->for,";")+1 ,strrpos($this->for,")")));
        $this->step=substr_replace($this->step,";",strrpos($this->step,")"), strrpos($this->step,")"));
      }

  /*
    public function getNestedFor($StartlineNumber)
      {
        return $this->children[$StartlineNumber];
      }

    public function setNestedFor($StartlineNumber)
      {
        $NestedFor= new ForLoop();
        $this->children[$StartlineNumber] = $NestedFor;
      }
    */
  }
/*For Loop Class ends*/
 $singleLoop= new ForLoop();

/*Counting Formatted Code Lines Starts*/
  while (!feof($formatted))
    {
      $Format=trim(fgets($formatted));
      $formattedLineNumber++;
    }
  fclose($formatted);
/*Counting Formatted Code Lines Ends*/

/*Checking For Loop Starts*/
  $file = fopen("file-for-loop-1-formatted.c", "r+") or die("Unable to open file!");
  function check (&$line , &$parse , &$loop ,&$lineCounter,$ForLoop )
      {

        if (strpos($line ,"for")===0)
          {
              $loop=true;
              $ForLoop->setStartLineNumber($lineCounter);
              $ForLoop->setFor ($line);
              $ForLoop->setInit ($line);
              $ForLoop->setStep ($ForLoop->getFor($line));
              $line=trim(substr_replace($line," ",0, strrpos($line,")")+1));
              $ForLoop->setForBlock ($line);
              $parse=$parse."//forloop->SwitchCase";

          }

        else if ($loop === true)
           {
            if(strpos ($line ,"{") !== false)
             {
                  $ForLoop->setBraceCounter ();
             }
            if ($lineCounter==($ForLoop->getStartlineNumber()+1))
             {
                $line=trim (substr_replace($line," ", strpos($line,"{"),1));

             }
            if(strpos ($line,"}") !== false)
             {
                $ForLoop->ResetBraceCounter();
             }

              if($ForLoop->getBraceCounter()==0)
              {
                  $loop=false;
                  $line=substr_replace($line," ",strrpos($line,"}"));
              }
              $ForLoop->setForBlock ($line);
           }
        else { $parse=$parse.$line."\n";}

      }

  echo "<pre>";



  while (!feof($file))
   {
      $line=trim(fgets($file));
      $lineCounter++;
      check ($line , $parse , $loop ,$lineCounter,$singleLoop);
   }
  fclose($file);
/*Checking For Loops Ends*/

/*Replace ForLoop with SwitchCase*/
  $output = fopen("output.c", "w+") or die("Unable to open file!");
  function CheckBlock ($forLoop,&$output,$token)
    {
      $iterations=3; //required from User
      if (strpos($token ,"//forloop->SwitchCase")===0)
        {
          $token=$forLoop->getInit()." \n switch(1) \n { \n case 1:";
          fwrite($output, $token);
          for($i=0;$i<($iterations);$i++)
            {
              if($i===0){fwrite($output,"\n"."//Loop Starts \n");}
              $token="{".$forLoop->getForBlock().$forLoop->getStep()."\n"."}"."\n";
              fwrite($output,$token);
              if($i===($iterations-1)){fwrite($output,"//Loop Ends"."\n");}
            }
          $token="break; \n } \n";
          fwrite($output, $token);
        }
      else
        {
          fwrite($output,$token."\n");
        }
    }
  $token = strtok($parse, "\n");
  while ($token !==false)
  {
    CheckBlock ($singleLoop,$output,$token);
    $token = strtok("\n");
  }

  fclose($output);
/*ForLoop Replaced successfully*/
//echo $parse."<br/> <br/> <br/> <br/> <hr>";
//echo $singleLoop->getForBlock()."<br/> <br/> <br/><hr>";
//echo $singleLoop->getInit()."<br/> <br/> <br/><hr>";
//echo $singleLoop->getStep()."<br/> <br/> <br/> <hr>";
//echo $singleLoop->getFor()."<br/> <br/> <br/> <hr>";
echo "</pre>";

?>
</body>
</html>
