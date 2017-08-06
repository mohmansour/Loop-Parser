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

/*Counting Formatted Code Lines Starts*/
  while (!feof($formatted))
    {
      $Format=trim(fgets($formatted));
      $formattedLineNumber++;
    }
  fclose($formatted);
/*Counting Formatted Code Lines Ends*/


/*Checking For Loop Starts*/
  $index=0;
  $ForArray = [];
  $file = fopen("file-for-loop-1-formatted.c", "r+") or die("Unable to open file!");
  function check (&$ForArray,&$line , &$parse , &$loop ,&$lineCounter,&$index)
      {
        if (strpos($line ,"for")===0)
          {
              $ForArray[$index]= new ForLoop();
              $loop=true;
              $ForArray[$index]->setStartLineNumber($lineCounter);
              $ForArray[$index]->setFor ($line);
              $ForArray[$index]->setInit ($line);
              $ForArray[$index]->setStep ($ForArray[$index]->getFor($line));
              $line=trim(substr_replace($line," ",0, strrpos($line,")")+1));
              if($line!=""){$loop=false; $ForArray[$index]->setForBlock ($line);}
              //$ForArray[$index]->setForBlock ($line);

              $parse=$parse."//forloop->SwitchCase...".$index;
          }

        else if ($loop == true)
           {
            if($lineCounter==($ForArray[$index]->getStartLineNumber()+1) && strpos($line ,"{")!==0)
              {$ForArray[$index]->setForBlock ($line);}
            if(strpos ($line ,"{") !== false)
             {
                  $ForArray[$index]->setBraceCounter();
             }
            if ($lineCounter==(($ForArray[$index]->getStartlineNumber())+1))
             {
                $line=trim (substr_replace($line," ", strpos($line,"{"),1));
             }
            if(strpos ($line,"}") !== false)
             {
                $ForArray[$index]->ResetBraceCounter();
             }

              if($ForArray[$index]->getBraceCounter()==0)
              {
                  $loop=false;
                  $line=substr_replace($line," ",strrpos($line,"}"));
                  $ForArray[$index]->setEndLineNumber($lineCounter);
              }
              $ForArray[$index]->setForBlock ($line);
              if($loop==false){$index++;}
           }
        else { $parse=$parse.$line."\n";}
      }


  while (!feof($file))
   {
      $line=trim(fgets($file));
      $lineCounter++;
      check ($ForArray,$line , $parse , $loop ,$lineCounter,$index);
   }
  fclose($file);
/*Checking For Loops Ends*/
//die(var_dump($ForArray));
/*Replace ForLoop with SwitchCase*/
  $output = fopen("output.c", "w+") or die("Unable to open file!");
  function CheckBlock (&$ForArray,&$output,$token)
    {
        //$ind=0;
        $iterations=array(3,2); //required from User

        if (strpos($token ,"//forloop->SwitchCase...")===0)
         {
          for($ind=0;$ind<count($ForArray);$ind++)
           {
            if((strpos($token ,"//forloop->SwitchCase...".$ind)===0))
             {
              $token=$ForArray[$ind]->getInit()." \n switch(1) \n { \n case 1:";
              fwrite($output, $token);
              for($i=0;$i<($iterations[$ind]);$i++)
               {
                if($i===0){fwrite($output,"\n"."//Loop-".$ind."- Starts\n");}
                $token="{ \n".$ForArray[$ind]->getForBlock().$ForArray[$ind]->getStep()."\n"."}"."\n";
                fwrite($output,$token);
                if($i===($iterations[$ind]-1)){fwrite($output,"\n"."//Loop-".$ind."- Ends\n");}
               }
            $token="break; \n } \n";
            fwrite($output, $token);
          }
        }
       }
        else
         {
          fwrite($output,$token."\n");
         }
    }
  $token = trim(strtok($parse, "\n"));
  while ($token !==false)
  {
    CheckBlock ($ForArray,$output,$token);
    $token = strtok("\n");
  }

  fclose($output);
/*ForLoop Replaced successfully*/
echo "<pre>";
//echo $parse."<br/> <br/> <br/> <br/> <hr>";
//echo $singleLoop->getForBlock()."<br/> <br/> <br/><hr>";
//echo $singleLoop->getInit()."<br/> <br/> <br/><hr>";
//echo $singleLoop->getStep()."<br/> <br/> <br/> <hr>";
//echo $singleLoop->getFor()."<br/> <br/> <br/> <hr>";
echo "</pre>";

?>
</body>
</html>
