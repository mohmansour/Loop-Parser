<!DOCTYPE html>
<html>
<head>
    <title>C code</title>
</head>
<body>
<?php
$target_dir = "loop-parser/";
$target_file = $target_dir . basename($_FILES["code"]["name"]);

/*Initializing Global Variables Starts*/
  $lineCounter=0;
  $parse="";
  $loop=false;
 $nestedloop=false;
  $parentloop=false;
  $formattedLineNumber=0;
  $format="";
/*Initializing Global Variables Ends*/

/*Classes Start*/
  /*For Loop Class Starts*/
    class ForLoop
    {
     protected $StartlineNumber;
     protected $EndLineNumber;
     protected $braceCounter;
     protected $forBlock;
     public $children=[];
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
          $this->for=trim(substr($line, strpos($line,"for"),strpos($line,")")+1));
        }
      public function getInit ()
        {
          return $this->init;
        }
      public function setInit ($line)
        {
        $this->init=trim(substr($this->for,strpos($this->for,"(")+1,strpos($this->for,";")));
        $this->init=trim(substr_replace($this->init," ",strpos($this->init,";")+1,strlen($this->init)));
        }
      public function getStep ()
        {
          return $this->step;
        }
      public function setStep ($for)
        {
          $this->step=trim(substr($this->for, strrpos($this->for,";")+1 ,strpos($this->for,")")));
          $this->step=substr_replace($this->step,";",strrpos($this->step,")"), strrpos($this->step,")"));
        }
      public function setNestedFor($StartlineNumber)
        {
          $NestedFor= new ForLoop();
          $this->children[$StartlineNumber] = $NestedFor;
        }
    }
  /*For Loop Class ends*/
/*Classes End*/

/*Formatting Code Starts*/
  function checkBrace (&$line)
    {
      if(strpos($line, "for")!==false && strpos($line, "for")!==0) {$line=substr_replace($line,"\n"."for ",strpos($line,"for"), 1);}

      if(strpos($line, "{")!==false && strpos($line, "{")!==0) {$line=substr_replace($line,"\n"."{ ",strpos($line,"{"), 1);}

      if(strrpos($line, "}")===(strlen($line)-1)) {$line=substr_replace($line,"\n "."}",strpos($line,"}"), 1);}
    }
  $source = fopen("code.c", "r+") or die("Unable to open file!");
  $formatted = fopen("code-formatted.c", "w+") or die("Unable to open file!");

  while (!feof($source))
   {
      $line=trim(fgets($source));
      checkBrace ($line);
      fwrite($formatted,"\n".$line);
   }
  fclose($source);
/*Formatting Code Ended*/

/*Checking For Loop Starts*/
  $index=0;
  $nestedIndex=0;
  $ForArray = [];
  $file = fopen("code-formatted.c", "r+") or die("Unable to open file!");
  function check(&$ForArray,&$line,&$parse,&$loop,&$parentloop,&$nestedloop,&$lineCounter,&$index,&$nestedIndex,$forComment)
      {
        if (strpos($line ,"for")===0 && ($loop==false || $parentloop==true))
          {
             echo "parent <hr>";
             if($parentloop==true){$index=$nestedIndex;}
              $ForArray[$index]= new ForLoop();
              $loop=true;
              $line1=trim(substr_replace($line," ",strpos($line,"for"), strlen($line)));
              $parse=$parse.$line1."\n";
              $ForArray[$index]->setStartLineNumber($lineCounter);
              $ForArray[$index]->setFor ($line);
              $ForArray[$index]->setInit ($line);
              $ForArray[$index]->setStep ($ForArray[$index]->getFor($line));
              $line=trim(substr_replace($line," ",0, strpos($line,")")+1));
              if($line!="")
                { $loop=false;$ForArray[$index]->setForBlock ($line);}
              $parse=$parse.$forComment."-->SwitchCase...".$index;
              echo "<pre>$parse<hr><pre/>";

          }
        else if ($loop == true && $line!="")
           {
            if (strpos($line ,"for")===0 )
              {
                echo "nested <br>";
                $nestedloop=true;
                $parentloop=true;
              }
              if($nestedloop==true )
               {
                echo "check<hr>";
                check($ForArray[$index]->children,$line,$ForArray[$index]->setForBlock,$loop,$parentloop,$nestedloop,$lineCounter,$index,$nestedIndex,"//NestedForloop");
               }
            if(strpos($line ,"{")!==0 && $ForArray[$index]->getBraceCounter()==0)
             {
              $ForArray[$index]->setForBlock ($line);
             }
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
      check ($ForArray,$line , $parse , $loop,$parentloop,$nestedloop,$lineCounter,$index,$nestedIndex,"//forloop");
   }
  fclose($file);
  var_dump($parse);
/*Checking For Loops Ends*/

/*Replace ForLoop with SwitchCase*/
  $output = fopen("output.c", "w+") or die("Unable to open file!");
  function CheckBlock (&$ForArray,&$output,$token)
    {
        $iterations=array(2,2); //required from User

        if (strpos($token ,"//forloop-->SwitchCase...")===0)
         {
          for($ind=0;$ind<count($ForArray);$ind++)
           {
            if((strpos($token ,"//forloop-->SwitchCase...".$ind)===0))
             {
              $token=$ForArray[$ind]->getInit()." \n switch(1) \n { \n case 1:";
               fwrite($output, $token);
              for($i=0;$i<($iterations[$ind]);$i++)
               {
                if($i===0){fwrite($output,"\n"."//Loop-".$ind."- Starts\n");}
                $token="{ \n".$ForArray[$ind]->getForBlock().$ForArray[$ind]->getStep()."\n"."}"."\n";
                for($nestedInd=0;$nestedInd<count($ForArray[$ind]->children);$nestedInd++)
                  {
                    if((strpos($token ,"//NestedForloop-->SwitchCase...".$nestedInd)==0))
                     {
                      $token=$ForArray[$ind]->children[$nestedInd]->getInit()." \n switch(1) \n { \n case 1:";
                      fwrite($output, $token);
                      for($j=0;$j<($iterations[$nestedInd]);$j++)
                        {
                          if($j===0){fwrite($output,"\n"."//nestedLoop-".$nestedInd."- Starts\n");}
                          $token="{ \n".$ForArray[$ind]->children[$nestedInd]->getForBlock().$ForArray[$ind]->children[$nestedInd]->getStep()."\n"."}"."\n";
                          fwrite($output,$token);
                          if($j===($iterations[$nestedInd]-1))
                            {
                             fwrite($output,"//nestedLoop-".$nestedInd."- Ends\n");
                             $token="break; \n } \n".$ForArray[$ind]->getStep()."\n";
                             fwrite($output, $token);
                             $token="";
                            }
                        }
                      }
                  }
                fwrite($output, $token);
                if($i===($iterations[$ind]-1))
                  {fwrite($output,"//Loop-".$ind."- Ends\n");$token="break; \n } \n";fwrite($output, $token);}
               }
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
?>
</body>
</html>
