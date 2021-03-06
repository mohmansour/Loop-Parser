<?php

/*Initializing Global Variables Starts*/
  $lineCounter=0;
  $FormatCounter=0;
  $parse="";
  $loop=false;
  $nestedloop=false;
  $parentloop=false;
  $formattedLineNumber=0;
  $format="";
  $values=[];
  $iterations=[];
  $formatIndex=0;
/*Initializing Global Variables Ends*/

/*For Line & Iterations Starts*/
  $values =$_POST['iterations'];
  $formatted = fopen("code-formatted.c", "r+") or die("Unable to open file!");
  while (!feof($formatted))
   {
    $line1=trim(fgets($formatted));
    $FormatCounter++;
    if(strpos($line1,"for")!==false)
      {$iterations[$FormatCounter]=(int)$values[$formatIndex];$formatIndex++;}
   }
  fclose($formatted);
/*For Line & Iterations Ends*/

/*Classes Start*/
 /*For Loop Class Starts*/
  class ForLoop
  {
  protected $StartlineNumber;
  protected $EndlineNumber;
  protected $braceCounter;
  protected $forBlock;
  public $children=[];
  protected $for;
  protected $init;
  protected $step;
  protected $extracted;
  protected $condition;
  protected $maxI;

  public function __construct()
   {
    $StartlineNumber=0;
    $EndLineNumber=0;
    $braceCounter=0;
    $forBlock="";
    $for="";
    $init="";
    $step="";
    $maxI=0;
    $extracted="";
    $condition="";
   }

    public function setStartLineNumber ($lineCounter)
      {
        $this->StartlineNumber= $lineCounter;
      }
    public function setFor ($line)
     {
          $this->for=trim(substr($line, strpos($line,"for"),strpos($line,")")+1));
     }
    public function setEndLineNumber ($lineCounter)
        {
          $this->EndlineNumber= $lineCounter;
        }
    public function setBraceCounter ()
        {
          $this->braceCounter++;
        }
    public function ResetBraceCounter ()
        {
          $this->braceCounter--;
        }
    public function setMaxI ()
     {
      $this->condition=trim(substr($this->for,strpos($this->for,";")+1,strrpos($this->for,";")));
      $this->condition=substr_replace($this->condition," ",strrpos($this->condition,";"), strrpos($this->condition,";"));
      if(strpos($this->condition,"=")!==false)
        {
          $this->extracted=trim(substr($this->condition,strrpos($this->condition,"=")+1,strrpos($this->condition,";")-strrpos($this->condition,"=")-1));
        }
      elseif(strpos($this->condition,"<")!==false)
        {
          $this->extracted=trim(substr($this->condition,strpos($this->condition,"<")+1,strrpos($this->condition,";")-strpos($this->condition,"<")-1));
        }
      elseif(strpos($this->condition,">")!==false)
        {
          $this->extracted=trim(substr($this->condition,strpos($this->condition,">")+1,strrpos($this->condition,";")-strpos($this->condition,">")-1));
        }
        $this->maxI=(int)$this->extracted;
        }
    public function setForBlock ($line)
        {
          $this->forBlock=$this->forBlock.$line."\n";
        }
    public function setInit ($line)
        {
        $this->init=trim(substr($this->for,strpos($this->for,"(")+1,strpos($this->for,";")));
        $this->init=trim(substr_replace($this->init," ",strpos($this->init,";")+1,strlen($this->init)));
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
    public function getStartLineNumber ()
        {
          return $this ->StartlineNumber;
        }
    public function getFor ()
        {
          return $this->for;
        }
    public function getEndLineNumber ()
        {
          return $this->EndlineNumber;
        }
    public function getBraceCounter ()
        {
          return $this->braceCounter;
        }
    public function getMaxI ()
        {
          return $this ->maxI;
        }
    public function getForBlock ()
        {
          return $this->forBlock;
        }
    public function getInit ()
        {
          return $this->init;
        }
    public function getStep ()
        {
          return $this->step;
        }
    }
  /*For Loop Class ends*/
/*Classes End*/

/*Checking For Loop Starts*/
  $index=0;
  $nestedIndex=0;
  $ForArray = [];
  $file = fopen("code-formatted.c", "r+") or die("Unable to open file!");
  function check(&$ForArray,&$line,&$parse,&$loop,&$parentloop,&$nestedloop,&$lineCounter,&$index,&$nestedIndex)
      {
        if (strpos($line ,"for")===0 && ($loop==false || $parentloop==true))
          {
             echo "parent <br>";//mark1
             if($parentloop==true){$index=$nestedIndex;}
              $ForArray[$index]= new ForLoop();
              $loop=true;
              $line1=trim(substr_replace($line," ",strpos($line,"for"), strlen($line)));
              $parse=$parse.$line1."\n";
              $ForArray[$index]->setStartLineNumber($lineCounter);
              $ForArray[$index]->setFor ($line);
              $ForArray[$index]->setMaxI ();
              $ForArray[$index]->setInit ($line);
              $ForArray[$index]->setStep ($ForArray[$index]->getFor($line));
              $line=trim(substr_replace($line," ",0, strpos($line,")")+1));
              if($line!="")
                { $loop=false;$ForArray[$index]->setForBlock ($line);}
              $parse=$parse."//->>".$ForArray[$index]->getStartLineNumber();
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
                check($ForArray[$index]->children,$line,$ForArray[$index]->setForBlock,$loop,$parentloop,$nestedloop,$lineCounter,$index,$nestedIndex);
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
              $parentloop=false;
              $nestedloop=false;
              $line=substr_replace($line," ",strrpos($line,"}"));
              $ForArray[$index]->setEndLineNumber($lineCounter);
             }
            $ForArray[$index]->setForBlock ($line);
            if($loop==false){$index++;}
           }
        else { $parse=$parse.$line."\n";}
        echo "<pre>$parse<hr><pre/>";
        echo $loop."loop \n".$nestedloop."nestedloop \n".$parentloop."parentloop\n";
      }
  while (!feof($file))
   {
      $line=trim(fgets($file));
      $lineCounter++;
      check ($ForArray,$line , $parse , $loop,$parentloop,$nestedloop,$lineCounter,$index,$nestedIndex);
   }
  fclose($file);
/*Checking For Loops Ends*/

/*Replace ForLoop with SwitchCase*/
  $output = fopen("output.c", "w+") or die("Unable to open file!");

  function CheckBlock (&$ForArray,&$output,$token,&$iterations)
   {
    if (strpos($token ,"//->>")===0)
     {
      for($ind=0;$ind<count($ForArray);$ind++)
       {
        if((strpos($token ,"//->>".$ForArray[$ind]->getStartLineNumber())===0))
         {
          $token=$ForArray[$ind]->getInit()." \n switch(1) \n { \n case 1:";
          fwrite($output, $token);
          $start=$ForArray[$ind]->getStartLineNumber();
          if($iterations[$start]==0 || ($iterations[$start]>$ForArray[$ind]->getMaxI()))
            { $iterations[$start]=$ForArray[$ind]->getMaxI();}
          for($i=0;$i<($iterations[$start]);$i++)
           {
            if($i===0){fwrite($output,"\n"."//Loop-".$ind."- Starts\n");}
            $token="{ \n".$ForArray[$ind]->getForBlock().$ForArray[$ind]->getStep()."\n"."}"."\n";
            for($nestedInd=0;$nestedInd<count($ForArray[$ind]->children);$nestedInd++)
             {
              if((strpos($token ,"//->>".$ForArray[$ind]->children[$nestedInd]->getStartLineNumber())==0))
               {
                $token=$ForArray[$ind]->children[$nestedInd]->getInit()." \n switch(1) \n { \n case 1:";
                fwrite($output, $token);
                $start1=$ForArray[$ind]->children[$nestedInd]->getStartLineNumber();
                if($iterations[$start1]==0 || ($iterations[$start1]>$ForArray[$ind]->children[$nestedInd]->getMaxI()))
                  { $iterations[$start1]=$ForArray[$ind]->children[$nestedInd]->getMaxI(); }
                for($j=0;$j<($iterations[$start1]);$j++)
                 {
                  if($j===0){fwrite($output,"\n"."//nestedLoop-".$nestedInd."- Starts\n");}
                  $token="{ \n".$ForArray[$ind]->children[$nestedInd]->getForBlock().$ForArray[$ind]->children[$nestedInd]->getStep()."\n"."}"."\n";
                  fwrite($output,$token);
                  if($j===($iterations[$ForArray[$ind]->children[$nestedInd]->getStartLineNumber()])-1)
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
            if($i===($iterations[$ForArray[$ind]->getStartLineNumber()]-1))
             {fwrite($output,"//Loop-".$ind."- Ends\n");$token="break; \n } \n";fwrite($output, $token);}
           }
         }
       }
     }
    else{fwrite($output,$token."\n");}
   }
  $token = trim(strtok($parse, "\n"));
  while ($token !==false)
  {
    CheckBlock ($ForArray,$output,$token,$iterations);
    $token = strtok("\n");
  }
  fclose($output);
/*ForLoop Replaced successfully*/
echo "<pre>For loop Blocks replaced with Switch Case Blocks \n </pre>";
?>
</body>
</html>
